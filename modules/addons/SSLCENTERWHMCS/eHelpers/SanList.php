<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions;

/**
 * The SAN names of an SSL order and the SAN counts around them, used to keep the SANs on every reissue
 * (client, admin and automatic) and to tell the client when paid SAN slots are still unused.
 *
 * - current names: the synced san_details of the order, without the main domain
 * - purchased counts: single_san_count / wildcard_san_count of the GoGetSSL order (synced into configdata)
 * - free names: on regular (single-domain) SSL the CA adds www.<domain> / <domain> (from the CSR common name) itself;
 *   they are never counted or sent as SANs. Multi-domain (SAN) products have no free names: the www or non-www
 *   version is an ordinary SAN there
 * - limits: what the WHMCS product allows (included + bought), never less than the names the order already has
 */
class SanList
{
    const CONFIGDATA_SINGLE_COUNT   = 'single_san_count';
    const CONFIGDATA_WILDCARD_COUNT = 'wildcard_san_count';

    /** brands that validate every SAN with the order's DCV method (no per-SAN choice) */
    const ORDER_METHOD_BRANDS = ['digicert', 'geotrust', 'thawte', 'rapidssl'];

    private static function configdata($sslService)
    {
        if (!$sslService) {
            return [];
        }
        $c = json_decode(json_encode($sslService->configdata), true);
        return is_array($c) ? $c : [];
    }

    private static function mainDomain(array $c)
    {
        return strtolower(trim(isset($c['domain']) ? (string) $c['domain'] : ''));
    }

    /**
     * SAN details of the order keyed by name (lowercase), without the main domain.
     *
     * @return array name => san_details entry (array)
     */
    public static function getDetails($sslService)
    {
        $c    = self::configdata($sslService);
        $main = self::mainDomain($c);
        $out  = [];
        foreach ((isset($c['san_details']) && is_array($c['san_details']) ? $c['san_details'] : []) as $san) {
            $san  = is_array($san) ? $san : (array) $san;
            $name = strtolower(trim(isset($san['san_name']) ? (string) $san['san_name'] : ''));
            if ($name === '' || $name === $main || isset($out[$name])) {
                continue;
            }
            $out[$name] = $san;
        }
        return $out;
    }

    /**
     * Purchased SAN counts of the GoGetSSL order; null values when not synced yet. With $api the counts are read
     * from the order status when they are missing (and stored on the service).
     *
     * @return array ['single' => int|null, 'wildcard' => int|null]
     */
    public static function getPurchased($sslService, $api = null)
    {
        $c        = self::configdata($sslService);
        $single   = isset($c[self::CONFIGDATA_SINGLE_COUNT]) && $c[self::CONFIGDATA_SINGLE_COUNT] !== null ? (int) $c[self::CONFIGDATA_SINGLE_COUNT] : null;
        $wildcard = isset($c[self::CONFIGDATA_WILDCARD_COUNT]) && $c[self::CONFIGDATA_WILDCARD_COUNT] !== null ? (int) $c[self::CONFIGDATA_WILDCARD_COUNT] : null;

        if (($single === null || $wildcard === null) && $api !== null && $sslService && !empty($sslService->remoteid)) {
            try {
                $status = $api->getOrderStatus($sslService->remoteid);
                self::storePurchased($sslService, $status);
                $single   = isset($status['single_san_count']) ? (int) $status['single_san_count'] : 0;
                $wildcard = isset($status['wildcard_san_count']) ? (int) $status['wildcard_san_count'] : 0;
            } catch (\Exception $e) {
                // unknown: callers keep every current name
            }
        }

        return ['single' => $single, 'wildcard' => $wildcard];
    }

    /**
     * Stores the purchased SAN counts of an API order status on the service (saved when $save).
     */
    public static function storePurchased($sslService, $status, $save = true)
    {
        if (!$sslService || !is_array($status) || !array_key_exists('single_san_count', $status)) {
            return;
        }
        $sslService->setConfigdataKey(self::CONFIGDATA_SINGLE_COUNT, (int) $status['single_san_count']);
        $sslService->setConfigdataKey(self::CONFIGDATA_WILDCARD_COUNT, isset($status['wildcard_san_count']) ? (int) $status['wildcard_san_count'] : 0);
        if ($save) {
            $sslService->save();
        }
    }

    /**
     * Names the CA adds to a regular (single-domain) certificate without a SAN slot: the www / non-www version of
     * the common name. Multi-domain products have none (see getFreeNamesOf()).
     */
    public static function getFreeNames($mainDomain)
    {
        $main = strtolower(trim((string) $mainDomain));
        if ($main === '') {
            return [];
        }
        if (strpos($main, '*.') === 0) {
            return [substr($main, 2), 'www.' . substr($main, 2)];
        }
        if (strpos($main, 'www.') === 0) {
            return [substr($main, 4)];
        }
        return ['www.' . $main];
    }

    /**
     * True for multi-domain (SAN) products: the API product has SANs, or the order has purchased SANs.
     */
    public static function isMultiDomain($sslService)
    {
        $c         = self::configdata($sslService);
        $productId = isset($c['product_id']) ? (int) $c['product_id'] : 0;
        if ($productId > 0) {
            try {
                $product = (new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products())->getProduct($productId);
                if ($product && (int) $product->id === $productId && isset($product->san_enabled)) {
                    return (bool) $product->san_enabled;
                }
            } catch (\Exception $e) {
                // unknown product: decided by the purchased SANs
            }
        }
        return !empty($c[self::CONFIGDATA_SINGLE_COUNT]) || !empty($c[self::CONFIGDATA_WILDCARD_COUNT]);
    }

    /**
     * Free names of an order: the www / non-www version of the common name on regular SSL, none on multi-domain.
     *
     * @param string|null $mainDomain common name (e.g. of a new CSR); default the order's domain
     */
    public static function getFreeNamesOf($sslService, $mainDomain = null)
    {
        if (self::isMultiDomain($sslService)) {
            return [];
        }
        return self::getFreeNames($mainDomain !== null ? $mainDomain : self::mainDomain(self::configdata($sslService)));
    }

    /**
     * Paid SAN names of the order: the current names without the free ones of regular SSL (the CA adds those).
     *
     * @return array ['single' => string[], 'wildcard' => string[]]
     */
    public static function getPaid($sslService, $api = null)
    {
        $purchased = self::getPurchased($sslService, $api);
        $free      = self::getFreeNamesOf($sslService);

        $single = $wildcard = [];
        foreach (array_keys(self::getDetails($sslService)) as $name) {
            if (strpos($name, '*.') === 0) {
                $wildcard[] = $name;
            } else {
                $single[] = $name;
            }
        }

        $single   = self::dropFree($single, $free, $purchased['single']);
        $wildcard = self::dropFree($wildcard, $free, $purchased['wildcard']);

        return ['single' => array_values($single), 'wildcard' => array_values($wildcard)];
    }

    private static function dropFree(array $names, array $free, $purchased)
    {
        // the CA adds the free names itself and they never use a SAN slot
        return array_filter($names, function ($name) use ($free) {
            return !in_array($name, $free, true);
        });
    }

    /**
     * SAN limits of the WHMCS service (product "Enable SANs" + included + bought).
     *
     * @return array ['single' => int, 'wildcard' => int]
     */
    public static function getProductLimits(array $params)
    {
        $options = isset($params['configoptions']) && is_array($params['configoptions']) ? $params['configoptions'] : [];

        $single = 0;
        if (isset($params[ConfigOptions::PRODUCT_ENABLE_SAN]) && $params[ConfigOptions::PRODUCT_ENABLE_SAN] === 'on') {
            $single = (int) (isset($params[ConfigOptions::PRODUCT_INCLUDED_SANS]) ? $params[ConfigOptions::PRODUCT_INCLUDED_SANS] : 0)
                + (int) (isset($options[ConfigOptions::OPTION_SANS_COUNT]) ? $options[ConfigOptions::OPTION_SANS_COUNT] : 0);
        }

        $wildcard = 0;
        if (isset($params[ConfigOptions::PRODUCT_ENABLE_SAN_WILDCARD]) && $params[ConfigOptions::PRODUCT_ENABLE_SAN_WILDCARD] === 'on') {
            $wildcard = (int) (isset($params[ConfigOptions::PRODUCT_INCLUDED_SANS_WILDCARD]) ? $params[ConfigOptions::PRODUCT_INCLUDED_SANS_WILDCARD] : 0)
                + (int) (isset($options[ConfigOptions::OPTION_SANS_WILDCARD_COUNT]) ? $options[ConfigOptions::OPTION_SANS_WILDCARD_COUNT] : 0);
        }

        return ['single' => $single, 'wildcard' => $wildcard];
    }

    /**
     * Limits for a reissue: the product limits, but never fewer than the names the order already has (a product
     * with SANs switched off must not drop the SANs of an existing order).
     *
     * @return array ['single' => int, 'wildcard' => int]
     */
    public static function getReissueLimits(array $params, $sslService, $api = null)
    {
        $limits = self::getProductLimits($params);
        $paid   = self::getPaid($sslService, $api);
        return [
            'single'   => max($limits['single'], count($paid['single'])),
            'wildcard' => max($limits['wildcard'], count($paid['wildcard'])),
        ];
    }

    /**
     * Unused SAN slots: what the product allows minus the paid names the certificate already has.
     *
     * @return array ['single' => int, 'wildcard' => int]
     */
    public static function getFreeSlots(array $params, $sslService)
    {
        $limits = self::getProductLimits($params);
        $paid   = self::getPaid($sslService);
        return [
            'single'   => max(0, $limits['single'] - count($paid['single'])),
            'wildcard' => max(0, $limits['wildcard'] - count($paid['wildcard'])),
        ];
    }

    /**
     * Paid names missing from a reissue request (they would be removed from the certificate).
     *
     * @param string[] $submitted names of the request (single + wildcard)
     * @return string[]
     */
    public static function getRemoved($sslService, array $submitted, $api = null)
    {
        $submitted = array_map(function ($n) { return strtolower(trim((string) $n)); }, $submitted);
        $paid      = self::getPaid($sslService, $api);
        return array_values(array_diff(array_merge($paid['single'], $paid['wildcard']), $submitted));
    }

    /**
     * Number of new SAN slots a reissue needs at GoGetSSL: submitted names (without the free ones) over the
     * purchased counts.
     *
     * @return array ['single' => int, 'wildcard' => int]
     */
    public static function getSlotsToBuy($sslService, $mainDomain, array $singleNames, array $wildcardNames, $purchasedSingle, $purchasedWildcard)
    {
        $free   = self::getFreeNamesOf($sslService, $mainDomain);
        $single = array_filter($singleNames, function ($n) use ($free) { return !in_array(strtolower(trim($n)), $free, true); });
        return [
            'single'   => max(0, count($single) - (int) $purchasedSingle),
            'wildcard' => max(0, count($wildcardNames) - (int) $purchasedWildcard),
        ];
    }

    /**
     * Buys SAN slots at GoGetSSL (API v1 add_ssl_san_order). Same parameters as the reissue forms always sent:
     * "count" only together with single SANs.
     */
    public static function buySlots($api, $orderId, $single, $wildcard)
    {
        $single   = max(0, (int) $single);
        $wildcard = max(0, (int) $wildcard);
        if ($single + $wildcard === 0) {
            return null;
        }
        return $api->addSslSan($orderId, $single > 0 ? $single + $wildcard : 0, $single, $wildcard);
    }

    /**
     * dns_names and approver_emails for a reissue without the client (automatic reissue): every paid name with
     * its current validation (email: its approver address; DigiCert brands: the order's DCV method).
     *
     * @return array ['dns_names' => string, 'approver_emails' => string] or [] when the order has no paid SANs
     */
    public static function buildAutomaticReissueData($sslService, $api = null)
    {
        $paid  = self::getPaid($sslService, $api);
        $names = array_merge($paid['single'], $paid['wildcard']);
        if (empty($names)) {
            return [];
        }

        $c           = self::configdata($sslService);
        $details     = self::getDetails($sslService);
        $orderMethod = strtolower(isset($c['dcv_method']) ? (string) $c['dcv_method'] : '');
        $brand       = strtolower(isset($c['product_brand']) ? (string) $c['product_brand'] : '');
        $useOrder    = in_array($brand, self::ORDER_METHOD_BRANDS, true) && $orderMethod !== '';

        $approvers = [];
        foreach ($names as $name) {
            $method = $useOrder ? $orderMethod : strtolower(isset($details[$name]['validation_method']) ? (string) $details[$name]['validation_method'] : $orderMethod);
            if ($method === '' ) {
                $method = 'dns';
            }
            if ($method === 'email') {
                $approvers[] = self::approverEmail($name, isset($details[$name]) ? $details[$name] : []);
            } else {
                $approvers[] = $method;
            }
        }

        return [
            'dns_names'       => implode(',', $names),
            'approver_emails' => implode(',', $approvers),
        ];
    }

    private static function approverEmail($name, array $detail)
    {
        $validation = isset($detail['validation']) ? (array) $detail['validation'] : [];
        $email      = isset($validation['email']) ? $validation['email'] : null;
        if (is_array($email) || is_object($email)) {
            $email = (array) $email;
            $email = isset($email['email']) ? $email['email'] : reset($email);
        }
        if (is_string($email) && strpos($email, '@') !== false) {
            return $email;
        }
        $domain = preg_replace('/^(\*\.|www\.)/', '', $name);
        return 'admin@' . $domain;
    }
}
