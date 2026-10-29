# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

The SSLCENTERWHMCS addon: a WHMCS module pair that integrates WHMCS with the GoGetSSL/SSLCenter reseller API to automate SSL certificate sales, provisioning, and lifecycle management. It ships as source that gets copied straight into a WHMCS installation's `modules/` directory — there is no build step, package manager, or test suite in this repo (no `composer.json`, no PHPUnit config). "Running" it means dropping it into a real (or test) WHMCS install with an active GoGetSSL API account.

## Commands

There are no build/lint/test commands — this is plain PHP consumed directly by WHMCS. To verify a change you must deploy into a WHMCS instance (copy/symlink `modules/addons/SSLCENTERWHMCS` and `modules/servers/SSLCENTERWHMCS` into that WHMCS's `modules/addons/` and `modules/servers/`), then activate the addon and enable module debug mode via its configuration (`configuration()->debug`) — this surfaces PHP exceptions and missing language strings inline instead of failing silently.

## Architecture

### Two modules, one namespace

This repo contains two separate WHMCS module types that act as one product, both under the PHP namespace `MGModule\SSLCENTERWHMCS`:

- `modules/addons/SSLCENTERWHMCS/` — the addon module: admin/client-area dashboard, API credential configuration, cron jobs, WHMCS-wide hooks.
- `modules/servers/SSLCENTERWHMCS/` — the server (provisioning) module: attached to WHMCS products as the "server type", drives the actual certificate lifecycle (create, suspend, terminate, renew, reissue, CSR generation...).

Each has its own entry-point file (`SSLCENTERWHMCS.php`) defining the global functions WHMCS calls directly (`SSLCENTERWHMCS_config`, `_activate`, `_output`, `_clientarea`, etc. for the addon; the equivalent server-module action functions for the server type).

### Custom autoloader spans both directories

`Loader.php` (present in both module roots) registers an `spl_autoload_register` callback that resolves `MGModule\SSLCENTERWHMCS\...` classes by searching **both** `modules/addons/SSLCENTERWHMCS` and `modules/servers/SSLCENTERWHMCS` for a matching file path (namespace segments → directories, PSR-0 style). This means a class can physically live under the addon directory but be loaded and used from the server module (and vice versa) — when adding a class, its location under either module root is a convention/dependency choice, not a hard boundary the autoloader enforces.

### Request routing: `AbstractMainDriver`

Both `Addon` (addon module) and `Server` (server module) extend `mgLibs\process\AbstractMainDriver`, which implements a singleton "kernel" (`Addon::I()` / `Server::I()`) plus a small routing convention:

- Requests carry `mg-page` and `mg-action` params (or defaults).
- `runControler()` resolves a controller class as `MGModule\SSLCENTERWHMCS\controllers\{addon|server}\{admin|clientarea}\{ucfirst($page)}`, instantiates it, and calls a method named `{$action}{$type}` where `$type` is one of `HTML`, `JSON`, `API`, `CRON`, `Action` (e.g. a `mg-action=list` JSON request calls `listJSON()`).
- Controllers can optionally define `isActive()` to hide/block a page.
- HTML responses go through the bundled Smarty (`mgLibs/Smarty.php`); templates live under `templates/{admin|clientarea}/pages/{lcfirst($page)}/`.

When tracing a bug reported "on page X", find it via this controller path rather than searching template names first.

### Data & external API layers

- `mgLibs/MySQL/{Query,PdoWrapper,Result}.php` — thin wrapper for direct SQL against the WHMCS database (used alongside WHMCS's own Capsule/Eloquent in hooks).
- `mgLibs/models/{Base,Orm,Repository,TableContructor}.php` — lightweight ORM/repository base classes; concrete per-feature models + repositories live in `models/{feature}/` (e.g. `models/apiConfiguration`, `models/orders`, `models/cronQueue`).
- `eModels/` and `eRepository/` — a second, parallel model/repository layer split by data source: `eRepository/sslcenter/*` and `eModels/sslcenter/*` wrap data from the external GoGetSSL/SSLCenter API (products, prices, statuses, DCV methods, web servers...), while `eRepository/whmcs/*` and `eModels/whmcs/*` wrap WHMCS's own core tables. Prefer the matching one of these two layers over raw SQL when touching product/order/status data.
- `mgLibs/SSLCenterApi.php` — the raw HTTP client for the GoGetSSL/SSLCenter reseller API.
- `eProviders/ApiProvider.php` — singleton that lazily authenticates `SSLCenterApi` using credentials pulled from `models/apiConfiguration/Repository` (stored in `tbladdonmodules` for this module). Get the authenticated client via `ApiProvider::getInstance()->getApi()` rather than instantiating `SSLCenterApi` directly.
- `eProviders/PathProvider.php` — resolves paths relative to the WHMCS root (e.g. locating WHMCS's countries data file, which differs between WHMCS 6 and 7+).

### Provisioning lifecycle (server module)

`modules/servers/SSLCENTERWHMCS/eServices/provisioning/*.php` has one class per WHMCS product/service action — `CreateAccount`, `SuspendAccount`, `UnsuspendAccount`, `TerminateAccount`, `Renew`, `GenerateCSR`, `GetCertificate`, `ClientReissueCertificate`/`AdminReissueCertificate`, `UpdateConfigData`, etc. — invoked from the server module's entry point when WHMCS fires the corresponding product action. This is the layer to touch for anything about the certificate order lifecycle itself, as distinct from the admin/client dashboard UI in `controllers/`.

### Cron jobs

`modules/addons/SSLCENTERWHMCS/cron/*.php` holds scheduled jobs (order status sync, price updates, certificate stats loading, expiry notifications, cron queue processing). They're normally triggered through the addon's own cron mechanism (`Addon::cron()`), but `cron/cron.php` is a standalone bootstrap that lets any of them run as a plain CLI script outside WHMCS's request cycle (it manually requires WHMCS's `init.php`).

### Hooks vs. routed pages

`hooks.php` in each module root registers WHMCS `add_hook()` callbacks (e.g. `ClientAreaPage`) that fire on WHMCS's own page loads — this is separate from and runs independently of the `mg-page`/`mg-action` controller routing above. When behavior seems to run "on every page" rather than only on this module's own pages, look here first.

### Versioning

Releases are tagged `vX.Y.Z` (see `git tag`) and historically also pushed to matching branches; GitHub Releases is the distribution channel for end users (per README) — this repo is the source, not the distributed package.
