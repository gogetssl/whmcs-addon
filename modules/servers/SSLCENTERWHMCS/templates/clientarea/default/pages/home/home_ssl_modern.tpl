<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true || $error}
<style>
/* ---- Modern SSL Certificate View ---- */
.ssl-mod { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 900px; margin: 0 auto; color: #333; }

/* Banner */
.ssl-mod-banner { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 6px; font-weight: 600; font-size: 14px; margin-bottom: 18px; }
.ssl-mod-banner .ssl-mod-banner-icon { font-size: 18px; }
.ssl-mod-banner-active_installed { background: #e8f9ef; color: #1a7a3e; border: 1px solid #b7e8cc; }
.ssl-mod-banner-active            { background: #e8f4fd; color: #1565c0; border: 1px solid #b3d4f5; }
.ssl-mod-banner-processing        { background: #fff8e1; color: #8a6200; border: 1px solid #ffe082; }
.ssl-mod-banner-expired           { background: #fdecea; color: #b71c1c; border: 1px solid #f5c6c0; }
.ssl-mod-banner-cancelled         { background: #fdecea; color: #b71c1c; border: 1px solid #f5c6c0; }
.ssl-mod-banner-reissue           { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }
.ssl-mod-banner-awaiting_config   { background: #f5f5f5; color: #555; border: 1px solid #ddd; }

/* Header */
.ssl-mod-header { background: #1e2d3d; color: #fff; border-radius: 8px; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.ssl-mod-header-left { display: flex; align-items: center; gap: 14px; }
.ssl-mod-header-icon { font-size: 22px; opacity: .7; }
.ssl-mod-header-domain { font-size: 20px; font-weight: 700; margin: 0; }
.ssl-mod-header-service { font-size: 13px; opacity: .6; margin: 2px 0 0; }
.ssl-mod-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
.ssl-mod-badge-active_installed { background: #27ae60; color: #fff; }
.ssl-mod-badge-active            { background: #2980b9; color: #fff; }
.ssl-mod-badge-processing        { background: #f39c12; color: #fff; }
.ssl-mod-badge-expired           { background: #e74c3c; color: #fff; }
.ssl-mod-badge-cancelled         { background: #c0392b; color: #fff; }
.ssl-mod-badge-reissue           { background: #e67e22; color: #fff; }
.ssl-mod-badge-awaiting_config   { background: #95a5a6; color: #fff; }

/* Cards row */
.ssl-mod-cards { display: flex; gap: 16px; margin-bottom: 22px; }
.ssl-mod-card { flex: 1; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 22px 18px; text-align: center; }
.ssl-mod-card-title { font-size: 11px; font-weight: 700; letter-spacing: 1px; color: #888; text-transform: uppercase; margin-bottom: 14px; }
.ssl-mod-status-icon { font-size: 40px; margin-bottom: 10px; }
.ssl-mod-status-icon-active_installed,
.ssl-mod-status-icon-active { color: #27ae60; }
.ssl-mod-status-icon-processing { color: #f39c12; }
.ssl-mod-status-icon-expired,
.ssl-mod-status-icon-cancelled { color: #e74c3c; }
.ssl-mod-status-icon-reissue { color: #e67e22; }
.ssl-mod-status-icon-awaiting_config { color: #95a5a6; }
.ssl-mod-status-label { font-weight: 700; font-size: 15px; margin-bottom: 14px; }
.ssl-mod-status-label-active_installed,
.ssl-mod-status-label-active { color: #27ae60; }
.ssl-mod-status-label-processing { color: #f39c12; }
.ssl-mod-status-label-expired,
.ssl-mod-status-label-cancelled { color: #e74c3c; }
.ssl-mod-status-label-reissue { color: #e67e22; }
.ssl-mod-status-label-awaiting_config { color: #95a5a6; }
.ssl-mod-detail-label { font-size: 11px; font-weight: 700; letter-spacing: 1px; color: #aaa; text-transform: uppercase; margin-top: 12px; margin-bottom: 2px; }
.ssl-mod-detail-value { font-size: 14px; color: #333; }
.ssl-mod-days-ok   { color: #27ae60; font-size: 12px; font-weight: 600; }
.ssl-mod-days-warn { color: #f39c12; font-size: 12px; font-weight: 600; }
.ssl-mod-days-crit { color: #e74c3c; font-size: 12px; font-weight: 600; }
.ssl-mod-dl-btn    { display: block; width: 100%; margin-bottom: 8px; text-align: center; }

/* Tabs */
.ssl-mod-tabs { border-bottom: 2px solid #e0e0e0; margin-bottom: 20px; display: flex; gap: 0; }
.ssl-mod-tab { padding: 10px 22px; cursor: pointer; font-size: 14px; font-weight: 600; color: #888; border-bottom: 3px solid transparent; margin-bottom: -2px; }
.ssl-mod-tab.active { color: #2980b9; border-bottom-color: #2980b9; }

/* Info tables */
.ssl-mod-info-row { display: flex; gap: 20px; margin-bottom: 20px; }
.ssl-mod-info-box { flex: 1; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 0; overflow: hidden; }
.ssl-mod-info-box-title { font-size: 11px; font-weight: 700; letter-spacing: 1px; color: #888; text-transform: uppercase; padding: 14px 18px 10px; border-bottom: 1px solid #f0f0f0; }
.ssl-mod-info-table { width: 100%; border-collapse: collapse; }
.ssl-mod-info-table td { padding: 9px 18px; font-size: 13px; border-bottom: 1px solid #f5f5f5; vertical-align: top; }
.ssl-mod-info-table td:first-child { color: #888; width: 45%; white-space: nowrap; }
.ssl-mod-info-table td:last-child { color: #333; font-weight: 500; }
.ssl-mod-info-table tr:last-child td { border-bottom: none; }

/* Validation table */
.ssl-mod-val-wrap { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 0; overflow: hidden; margin-bottom: 20px; }
.ssl-mod-val-title { font-size: 11px; font-weight: 700; letter-spacing: 1px; color: #888; text-transform: uppercase; padding: 14px 18px 10px; border-bottom: 1px solid #f0f0f0; }
.ssl-mod-val-table { width: 100%; border-collapse: collapse; }
.ssl-mod-val-table th { font-size: 11px; font-weight: 700; letter-spacing: .8px; color: #888; text-transform: uppercase; padding: 10px 14px; border-bottom: 1px solid #e8e8e8; background: #fafafa; }
.ssl-mod-val-table td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
.ssl-mod-val-table tr:last-child td { border-bottom: none; }
.ssl-val-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.ssl-val-badge-validated  { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
.ssl-val-badge-pending    { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
.ssl-val-badge-failed     { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.ssl-val-badge-na         { background: #f5f5f5; color: #888; border: 1px solid #e0e0e0; }
.ssl-mod-val-email { font-style: italic; color: #888; font-size: 12px; padding: 4px 14px 10px; }

/* DCV info blocks (http/dns) */
.ssl-mod-dcv-block { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px 14px; margin-top: 6px; font-size: 12px; }
.ssl-mod-dcv-block code { display: block; word-break: break-all; background: #f0f0f0; padding: 4px 6px; border-radius: 3px; font-size: 11px; margin-top: 4px; }

/* Action buttons */
.ssl-mod-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }

@media (max-width: 768px) {
    .ssl-mod-cards { flex-direction: column; }
    .ssl-mod-info-row { flex-direction: column; }
}
</style>

<div class="ssl-mod">

{if $error}
    <div class="alert alert-danger">{$error}</div>
{else}

    {* ---- Top Banner ---- *}
    {assign var='stateLabel' value=''}
    {assign var='stateIcon'  value=''}
    {if $certState == 'active_installed'}
        {assign var='stateLabel' value=$MGLANG->T('sslBannerActiveInstalled')|default:'SSL Certificate Active &amp; Installed'}
        {assign var='stateIcon'  value='glyphicon-ok-circle'}
    {elseif $certState == 'active'}
        {assign var='stateLabel' value=$MGLANG->T('sslBannerActive')|default:'SSL Certificate Active'}
        {assign var='stateIcon'  value='glyphicon-ok-circle'}
    {elseif $certState == 'processing'}
        {assign var='stateLabel' value=$MGLANG->T('sslBannerProcessing')|default:'Pending Validation'}
        {assign var='stateIcon'  value='glyphicon-time'}
    {elseif $certState == 'expired'}
        {assign var='stateLabel' value=$MGLANG->T('sslBannerExpired')|default:'SSL Certificate Expired'}
        {assign var='stateIcon'  value='glyphicon-exclamation-sign'}
    {elseif $certState == 'cancelled'}
        {assign var='stateLabel' value=$MGLANG->T('sslBannerCancelled')|default:'SSL Certificate Cancelled'}
        {assign var='stateIcon'  value='glyphicon-remove-circle'}
    {elseif $certState == 'reissue'}
        {assign var='stateLabel' value=$MGLANG->T('sslBannerReissue')|default:'Reissue Pending'}
        {assign var='stateIcon'  value='glyphicon-refresh'}
    {else}
        {assign var='stateLabel' value=$MGLANG->T('sslBannerAwaitingConfig')|default:'Awaiting Configuration'}
        {assign var='stateIcon'  value='glyphicon-cog'}
    {/if}

    <div class="ssl-mod-banner ssl-mod-banner-{$certState}">
        <span class="ssl-mod-banner-icon glyphicon {$stateIcon}"></span>
        {$stateLabel}
    </div>

    {if $syncSuccess}
    <div id="ssl-sync-notice" class="alert" style="background:#e8f9ef;color:#1a7a3e;border:1px solid #b7e8cc;border-radius:6px;padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
        <span class="glyphicon glyphicon-ok-circle" style="font-size:18px;flex-shrink:0;"></span>
        <span>{$MGLANG->T('sslSyncSuccess')|default:'Certificate data has been successfully synchronized with the Certificate Authority.'}</span>
        <button type="button" onclick="document.getElementById('ssl-sync-notice').style.display='none';" style="margin-left:auto;background:none;border:none;cursor:pointer;color:#1a7a3e;font-size:18px;line-height:1;">&times;</button>
    </div>
    {/if}

    {* ---- Validation warning notice ---- *}
    {if $showValidationWarning && $certState == 'processing'}
    <div class="alert alert-warning" style="border-radius:6px;margin-bottom:16px;display:flex;align-items:flex-start;gap:12px;padding:14px 18px;">
        <span class="glyphicon glyphicon-warning-sign" style="font-size:22px;margin-top:2px;flex-shrink:0;color:#8a6200;"></span>
        <div style="flex:1;">
            <strong style="font-size:14px;">{$MGLANG->T('validationWarningTitle')|default:'Automatic Validation Failed — Action Required'}</strong>
            <p style="margin:6px 0 10px;font-size:13px;">
                {$MGLANG->T('validationWarningDesc')|default:'The validation file was uploaded to your server, but the Certificate Authority could not verify it after multiple attempts. This may be caused by a redirect, CDN (e.g. Cloudflare), or <code>.htaccess</code> rules blocking access to the file.'}
            </p>
            <p style="margin:0 0 10px;font-size:13px;">
                <strong>{$MGLANG->T('validationWarningAction')|default:'Please switch to Email Approval — it does not require server access and validates instantly.'}</strong>
            </p>
            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-change-dcv" style="font-weight:600;">
                <span class="glyphicon glyphicon-envelope" style="margin-right:5px;"></span>
                {$MGLANG->T('switchToEmailValidation')|default:'Switch to Email Validation'}
            </button>
        </div>
    </div>
    {/if}

    {* ---- Header ---- *}
    {assign var='badgeLabel' value=''}
    {if $certState == 'active_installed'}{assign var='badgeLabel' value=$MGLANG->T('sslBadgeInstalled')|default:'Installed'}
    {elseif $certState == 'active'}{assign var='badgeLabel' value=$MGLANG->T('sslBadgeActive')|default:'Active'}
    {elseif $certState == 'processing'}{assign var='badgeLabel' value=$MGLANG->T('sslBadgePending')|default:'Pending'}
    {elseif $certState == 'expired'}{assign var='badgeLabel' value=$MGLANG->T('sslBadgeExpired')|default:'Expired'}
    {elseif $certState == 'cancelled'}{assign var='badgeLabel' value=$MGLANG->T('sslBadgeCancelled')|default:'Cancelled'}
    {elseif $certState == 'reissue'}{assign var='badgeLabel' value=$MGLANG->T('sslBadgeReissue')|default:'Reissue'}
    {else}{assign var='badgeLabel' value=$MGLANG->T('sslBadgePending')|default:'Pending'}{/if}

    <div class="ssl-mod-header">
        <div class="ssl-mod-header-left">
            <span class="ssl-mod-header-icon glyphicon glyphicon-lock"></span>
            <div>
                <p class="ssl-mod-header-domain">{$domain|default:$productDomain|escape}</p>
                <p class="ssl-mod-header-service">{$MGLANG->T('sslServiceLabel')|default:'Service #'}{$serviceid}</p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
            <span class="ssl-mod-badge ssl-mod-badge-{$certState}">{$badgeLabel}</span>
            {if $certState == 'processing'}
            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-change-dcv" style="font-weight:600;letter-spacing:.3px;">
                <span class="glyphicon glyphicon-refresh" style="margin-right:5px;"></span>{$MGLANG->T('changeDcvMethod')|default:'Change Validation Method'}
            </button>
            {/if}
            <a href="{$actual_link}&refreshapi=1" class="btn btn-default btn-sm" style="font-weight:600;letter-spacing:.3px;color:#ccc;border-color:rgba(255,255,255,.2);background:rgba(255,255,255,.1);" title="Sync certificate data from API">
                <span class="glyphicon glyphicon-refresh" style="margin-right:5px;"></span>{$MGLANG->T('sslSyncBtn')|default:'Sync'}
            </a>
        </div>
    </div>

    {* ---- 3 Cards ---- *}
    <div class="ssl-mod-cards">

        {* Status card *}
        <div class="ssl-mod-card">
            <div class="ssl-mod-status-icon ssl-mod-status-icon-{$certState}">
                {if $certState == 'active_installed' || $certState == 'active'}
                    <span class="glyphicon glyphicon-ok-circle"></span>
                {elseif $certState == 'processing' || $certState == 'reissue'}
                    <span class="glyphicon glyphicon-time"></span>
                {elseif $certState == 'awaiting_config'}
                    <span class="glyphicon glyphicon-cog"></span>
                {else}
                    <span class="glyphicon glyphicon-remove-circle"></span>
                {/if}
            </div>
            <div class="ssl-mod-status-label ssl-mod-status-label-{$certState}">
                {if $certState == 'active_installed'}{$MGLANG->T('sslStatusActiveInstalled')|default:'Active &amp; Installed'}
                {elseif $certState == 'active'}{$MGLANG->T('sslStatusActive')|default:'Active'}
                {elseif $certState == 'processing'}{$MGLANG->T('sslStatusPendingValidation')|default:'Pending Validation'}
                {elseif $certState == 'expired'}{$MGLANG->T('sslStatusExpired')|default:'Expired'}
                {elseif $certState == 'cancelled'}{$MGLANG->T('sslStatusCancelled')|default:'Cancelled'}
                {elseif $certState == 'reissue'}{$MGLANG->T('sslStatusReissue')|default:'Reissue Pending'}
                {else}{$MGLANG->T('sslStatusAwaitingConfig')|default:'Awaiting Configuration'}{/if}
            </div>
            {if $certState == 'active_installed' || $certState == 'active'}
                {if $domain}
                <a href="https://{$domain|escape}" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                    {$MGLANG->T('viewSite')|default:'View Site'} <span class="glyphicon glyphicon-new-window"></span>
                </a>
                {/if}
            {elseif $certState == 'awaiting_config'}
                <a href="{$configurationURL}" class="btn btn-primary btn-sm">
                    {$MGLANG->T('configureSSL')|default:'Configure SSL'}
                </a>
            {elseif $certState == 'reissue'}
                <a href="{$configurationURL}" class="btn btn-warning btn-sm">
                    {$MGLANG->T('reissueSSL')|default:'Reissue'}
                </a>
            {/if}
        </div>

        {* Certificate Details card *}
        <div class="ssl-mod-card">
            <div class="ssl-mod-card-title">{$MGLANG->T('certificateDetails')|default:'Certificate Details'}</div>
            <div class="ssl-mod-detail-label">{$MGLANG->T('sslSerial')|default:'Serial'|upper}</div>
            <div class="ssl-mod-detail-value">{if $certSerial}#{$certSerial|escape}{else}&mdash;{/if}</div>
            {if $certIssued && $validTill}
            <div class="ssl-mod-detail-label">{$MGLANG->T('sslCertExpires')|default:'Certificate Expires'|upper}</div>
            <div class="ssl-mod-detail-value">{$validTill}</div>
            <div>
                {if $nextReissue > 90}
                    <span class="ssl-mod-days-ok">{$nextReissue}{$MGLANG->T('sslDaysLeftSuffix')|default:'d left'}</span>
                {elseif $nextReissue > 30}
                    <span class="ssl-mod-days-warn">{$nextReissue}{$MGLANG->T('sslDaysLeftSuffix')|default:'d left'}</span>
                {elseif $nextReissue >= 0}
                    <span class="ssl-mod-days-crit">{$nextReissue}{$MGLANG->T('sslDaysLeftSuffix')|default:'d left'}</span>
                {else}
                    <span class="ssl-mod-days-crit">{$MGLANG->T('sslExpiredText')|default:'Expired'}</span>
                {/if}
            </div>
            {if $subscriptionEnds}
            <div class="ssl-mod-detail-label">{$MGLANG->T('sslSubscriptionEndsLabel')|default:'Subscription Ends'|upper}</div>
            <div class="ssl-mod-detail-value">{$subscriptionEnds}</div>
            {/if}
            {elseif !$certIssued}
            <div class="ssl-mod-detail-label">{$MGLANG->T('sslExpiresLabel')|default:'Expires'|upper}</div>
            <div class="ssl-mod-detail-value" style="color:#aaa;">{$MGLANG->T('sslPendingIssuance')|default:'Pending issuance'}</div>
            {/if}
        </div>

        {* Download card *}
        <div class="ssl-mod-card">
            <div class="ssl-mod-card-title">{$MGLANG->T('download')|default:'Download'}</div>
            {if $downloadpem}
            <a href="{$downloadpem}" class="btn btn-primary ssl-mod-dl-btn">
                <span class="glyphicon glyphicon-download-alt"></span> {$MGLANG->T('sslDownloadPem')|default:'Certificate (.pem)'}
            </a>
            {elseif $downloadcrt}
            <a href="{$downloadcrt}" class="btn btn-primary ssl-mod-dl-btn">
                <span class="glyphicon glyphicon-download-alt"></span> {$MGLANG->T('sslDownloadCrt')|default:'Certificate (.crt)'}
            </a>
            {else}
            <p style="color:#aaa;font-size:13px;">{$MGLANG->T('sslDownloadNotAvailable')|default:'Not available yet'}</p>
            {/if}
            {if $privateKey}
            <button type="button" class="btn btn-default ssl-mod-dl-btn" id="ssl-mod-show-key">
                <span class="glyphicon glyphicon-eye-open"></span> {$MGLANG->T('sslDownloadPrivateKey')|default:'Private Key (.key)'}
            </button>
            {/if}
            {if $downloadca}
            <a href="{$downloadca}" class="btn btn-default ssl-mod-dl-btn">
                <span class="glyphicon glyphicon-download-alt"></span> {$MGLANG->T('sslDownloadCaBundle')|default:'CA Bundle (.ca)'}
            </a>
            {/if}
            {if $btnInstallCrt}
            <button type="button" class="btn btn-success ssl-mod-dl-btn" id="ssl-mod-install-btn">
                <span class="glyphicon glyphicon-cloud-upload"></span>
                {$MGLANG->T('installCertificate')|default:'Install on Server'}
            </button>
            {/if}
        </div>
    </div>

    {* ---- Private Key modal ---- *}
    {if $privateKey}
    <div class="modal fade" id="modal-private-key" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{$MGLANG->T('sslPrivateKeyTitle')|default:'Private Key'}</h4>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="12" readonly onclick="this.select()">{$privateKey|escape:'html'}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')|default:'Close'}</button>
                </div>
            </div>
        </div>
    </div>
    {/if}

    {* ---- Tabs ---- *}
    <div class="ssl-mod-tabs">
        <div class="ssl-mod-tab active" data-tab="overview">{$MGLANG->T('sslTabOverview')|default:'Overview'}</div>
    </div>

    {* ---- Overview tab ---- *}
    <div id="ssl-tab-overview">

        {* Order Info + Organization *}
        <div class="ssl-mod-info-row">
            <div class="ssl-mod-info-box">
                <div class="ssl-mod-info-box-title">{$MGLANG->T('sslOrderInformation')|default:'Order Information'}</div>
                <table class="ssl-mod-info-table">
                    {if $sslOrderApiId}
                    <tr><td>{$MGLANG->T('sslOrderIdLabel')|default:'SSL Order ID'}</td><td>#{$sslOrderApiId|escape}</td></tr>
                    {/if}
                    {if $partner_order_id}
                    <tr><td>{$MGLANG->T('sslVendorOrderId')|default:'Vendor Order ID'}</td><td>#{$partner_order_id|escape}</td></tr>
                    {/if}
                    <tr>
                        <td>{$MGLANG->T('sslOrderStatusLabel')|default:'Order Status'}</td>
                        <td>
                            {if $certState == 'active_installed' || $certState == 'active'}
                                <span style="color:#27ae60;font-weight:600;">{$MGLANG->T('sslStatusActiveValid')|default:'Active (valid)'}</span>
                            {elseif $certState == 'processing'}
                                <span style="color:#f39c12;font-weight:600;">{$MGLANG->T('sslStatusPendingValidation')|default:'Pending Validation'}</span>
                            {elseif $certState == 'expired'}
                                <span style="color:#e74c3c;font-weight:600;">{$MGLANG->T('sslStatusExpired')|default:'Expired'}</span>
                            {elseif $certState == 'cancelled'}
                                <span style="color:#e74c3c;font-weight:600;">{$MGLANG->T('sslStatusCancelled')|default:'Cancelled'}</span>
                            {elseif $certState == 'reissue'}
                                <span style="color:#e67e22;font-weight:600;">{$MGLANG->T('sslStatusReissue')|default:'Reissue Pending'}</span>
                            {else}
                                <span style="color:#95a5a6;font-weight:600;">{$MGLANG->T('sslStatusAwaitingConfig')|default:'Awaiting Configuration'}</span>
                            {/if}
                        </td>
                    </tr>
                    <tr><td>{$MGLANG->T('sslTypeLabel')|default:'Type'}</td><td>{$certType|escape}</td></tr>
                    <tr><td>{$MGLANG->T('sslWildcardLabel')|default:'Wildcard'}</td><td>{if $certIsWildcard}{$MGLANG->T('sslYes')|default:'Yes'}{else}{$MGLANG->T('sslNo')|default:'No'}{/if}</td></tr>
                    {if $purchasedDate}
                    <tr><td>{$MGLANG->T('sslPurchasedLabel')|default:'Purchased'}</td><td>{$purchasedDate}</td></tr>
                    {/if}
                    {if $validFrom}
                    <tr><td>{$MGLANG->T('validFrom')|default:'Valid From'}</td><td>{$validFrom}</td></tr>
                    {/if}
                </table>
            </div>

            <div class="ssl-mod-info-box">
                <div class="ssl-mod-info-box-title">{$MGLANG->T('sslOrganizationTitle')|default:'Organization'}</div>
                <table class="ssl-mod-info-table">
                    <tr><td>{$MGLANG->T('sslNameLabel')|default:'Name'}</td><td>{if $certOrg}{$certOrg|escape}{else}&mdash;{/if}</td></tr>
                    <tr><td>{$MGLANG->T('sslUnitLabel')|default:'Unit'}</td><td>{if $certOrgUnit}{$certOrgUnit|escape}{else}&mdash;{/if}</td></tr>
                    <tr><td>{$MGLANG->T('sslAddressLabel')|default:'Address'}</td><td>{if $certAddress}{$certAddress|escape}{else}&mdash;{/if}</td></tr>
                </table>
            </div>
        </div>

        {* ---- Contact Details ---- *}
        {if $adminContact || $techContact}
        <div class="ssl-mod-info-row" style="margin-bottom:22px;">
            {if $adminContact}
            <div class="ssl-mod-info-box">
                <div class="ssl-mod-info-box-title">{$MGLANG->T('sslAdminContactTitle')|default:'Administrative Contact'}</div>
                <table class="ssl-mod-info-table">
                    {if $adminContact.name}<tr><td>{$MGLANG->T('sslNameLabel')|default:'Name'}</td><td>{$adminContact.name|escape}</td></tr>{/if}
                    {if $adminContact.organization}<tr><td>{$MGLANG->T('sslOrgFieldLabel')|default:'Organization'}</td><td>{$adminContact.organization|escape}</td></tr>{/if}
                    {if $adminContact.email}<tr><td>{$MGLANG->T('sslEmailLabel')|default:'Email'}</td><td>{$adminContact.email|escape}</td></tr>{/if}
                    {if $adminContact.phone}<tr><td>{$MGLANG->T('sslPhoneLabel')|default:'Phone'}</td><td>{$adminContact.phone|escape}</td></tr>{/if}
                </table>
            </div>
            {/if}
            {if $techContact}
            <div class="ssl-mod-info-box">
                <div class="ssl-mod-info-box-title">{$MGLANG->T('sslTechContactTitle')|default:'Technical Contact'}</div>
                <table class="ssl-mod-info-table">
                    {if $techContact.name}<tr><td>{$MGLANG->T('sslNameLabel')|default:'Name'}</td><td>{$techContact.name|escape}</td></tr>{/if}
                    {if $techContact.organization}<tr><td>{$MGLANG->T('sslOrgFieldLabel')|default:'Organization'}</td><td>{$techContact.organization|escape}</td></tr>{/if}
                    {if $techContact.email}<tr><td>{$MGLANG->T('sslEmailLabel')|default:'Email'}</td><td>{$techContact.email|escape}</td></tr>{/if}
                    {if $techContact.phone}<tr><td>{$MGLANG->T('sslPhoneLabel')|default:'Phone'}</td><td>{$techContact.phone|escape}</td></tr>{/if}
                </table>
            </div>
            {/if}
        </div>
        {/if}

        {* ---- Validation Details ---- *}
        {if $dcv_method || $sans}
        <div class="ssl-mod-val-wrap">
            <div class="ssl-mod-val-title">{$MGLANG->T('sslValidationDetails')|default:'Validation Details'}</div>
            <table class="ssl-mod-val-table">
                <thead>
                    <tr>
                        <th>{$MGLANG->T('domain')|default:'Domain'}</th>
                        <th>{$MGLANG->T('sslMethodLabel')|default:'Method'}</th>
                        <th>{$MGLANG->T('sslStatusHeader')|default:'Status'}</th>
                        <th>{$MGLANG->T('sslNameFile')|default:'Name / File'}</th>
                        <th>{$MGLANG->T('sslContentEntry')|default:'Content / Entry'}</th>
                    </tr>
                </thead>
                <tbody>
                    {* Main domain row *}
                    {if $dcv_method}
                    <tr>
                        <td><strong>{$domain|default:''|escape}</strong></td>
                        <td><span style="text-transform:uppercase;font-size:11px;font-weight:700;color:#555;">{$dcv_method|upper}</span></td>
                        <td>
                            {if $certState == 'active_installed' || $certState == 'active'}
                                <span class="ssl-val-badge ssl-val-badge-validated">{$MGLANG->T('sslValidated')|default:'Validated'}</span>
                            {elseif $certState == 'processing' || $certState == 'reissue'}
                                <span class="ssl-val-badge ssl-val-badge-pending">{$MGLANG->T('sslPendingBadge')|default:'Pending'}</span>
                            {else}
                                <span class="ssl-val-badge ssl-val-badge-na">—</span>
                            {/if}
                        </td>
                        <td>
                            {if $dcv_method == 'email'}
                                {$MGLANG->T('sslNa')|default:'N/A'}
                            {elseif isset($approver_method[$dcv_method].filename)}
                                <code style="font-size:11px;">{$approver_method[$dcv_method].filename}</code>
                            {elseif isset($approver_method[$dcv_method].host)}
                                <code style="font-size:11px;">{$approver_method[$dcv_method].host}</code>
                            {else}
                                {$MGLANG->T('sslNa')|default:'N/A'}
                            {/if}
                        </td>
                        <td>
                            {if $dcv_method == 'email'}
                                {$MGLANG->T('sslNa')|default:'N/A'}
                            {elseif isset($approver_method[$dcv_method].content)}
                                {if is_array($approver_method[$dcv_method].content)}
                                    <span style="font-size:11px;word-break:break-all;">
                                        {foreach $approver_method[$dcv_method].content as $mainContent}
                                            {$mainContent|truncate:60:'...'|escape}
                                        {/foreach}
                                    </span>
                                {else}
                                    <span style="font-size:11px;word-break:break-all;">{$approver_method[$dcv_method].content|truncate:60:'...'|escape}</span>
                                {/if}
                            {else}
                                N/A
                            {/if}
                        </td>
                    </tr>
                    {if $dcv_method == 'email' && $approver_method}
                    <tr>
                        <td colspan="5" class="ssl-mod-val-email">
                            {$MGLANG->T('sslApprovalEmail')|default:'Approval Email'} &nbsp;&nbsp; {$approver_method|escape}
                        </td>
                    </tr>
                    {/if}
                    {/if}

                    {* SAN rows *}
                    {if $sans}
                    {foreach $sans as $sanName => $sanData}
                    <tr>
                        <td>{$sanData.san_name|escape}</td>
                        <td><span style="text-transform:uppercase;font-size:11px;font-weight:700;color:#555;">{$sanData.method|upper}</span></td>
                        <td>
                            {if $certState == 'active_installed' || $certState == 'active'}
                                <span class="ssl-val-badge ssl-val-badge-validated">{$MGLANG->T('sslValidated')|default:'Validated'}</span>
                            {elseif $certState == 'processing'}
                                <span class="ssl-val-badge ssl-val-badge-pending">{$MGLANG->T('sslPendingBadge')|default:'Pending'}</span>
                            {else}
                                <span class="ssl-val-badge ssl-val-badge-na">—</span>
                            {/if}
                        </td>
                        <td>
                            {if $sanData.method == 'email' || $sanData.method == 'dns'}
                                {$MGLANG->T('sslNa')|default:'N/A'}
                            {elseif is_array($sanData.san_validation) && isset($sanData.san_validation.filename)}
                                <code style="font-size:11px;">{$sanData.san_validation.filename|escape}</code>
                            {else}
                                N/A
                            {/if}
                        </td>
                        <td>
                            {if $sanData.method == 'email'}
                                {$MGLANG->T('sslNa')|default:'N/A'}
                            {elseif $sanData.method == 'dns' && is_string($sanData.san_validation)}
                                <span style="font-size:11px;word-break:break-all;">{$sanData.san_validation|escape|truncate:60:'...'}</span>
                            {elseif is_array($sanData.san_validation) && isset($sanData.san_validation.content)}
                                {if is_array($sanData.san_validation.content)}
                                    <span style="font-size:11px;word-break:break-all;">
                                        {foreach $sanData.san_validation.content as $sanContent}
                                            {$sanContent|truncate:60:'...'|escape}
                                        {/foreach}
                                    </span>
                                {else}
                                    <span style="font-size:11px;word-break:break-all;">{$sanData.san_validation.content|truncate:60:'...'|escape}</span>
                                {/if}
                            {else}
                                {$MGLANG->T('sslNa')|default:'N/A'}
                            {/if}
                        </td>
                    </tr>
                    {if $sanData.method == 'email' && $sanData.san_validation}
                    <tr>
                        <td colspan="5" class="ssl-mod-val-email">
                            {$MGLANG->T('sslApprovalEmail')|default:'Approval Email'} &nbsp;&nbsp; {$sanData.san_validation|escape}
                        </td>
                    </tr>
                    {/if}
                    {/foreach}
                    {/if}
                </tbody>
            </table>
        </div>
        {/if}

        {* ---- 2.3 Thank You / Enrollment Status Messages (processing state, autoinstall only) ---- *}
        {if $certState == 'processing' && $installType == 'autoinstall'}
        <div class="panel panel-default" style="margin-bottom:16px;">
            <div class="panel-heading" style="background:#f0f7ff;border-color:#b3d4f5;">
                <strong><span class="glyphicon glyphicon-info-sign" style="margin-right:6px;color:#1565c0;"></span>{$MGLANG->T('autoinstallStatusTitle')|default:'AutoInstall SSL — Validation In Progress'}</strong>
            </div>
            <div class="panel-body" style="font-size:13px;color:#444;">

                {* 2.3.1 Single Domain DV *}
                {if !$certIsWildcard && $certType == 'DV'}
                <p><strong>{$MGLANG->T('thankYou')|default:'Thank You'}</strong></p>
                <p>{$MGLANG->T('autoinstallSingleDvMsg')|default:'AutoInstallSSL is currently performing domain validation for your certificate. Once the CA validates your domain the certificate will be installed on your server. We will notify you once the certificate is installed. You can check the status on your order detail page.'}</p>
                {/if}

                {* 2.3.2 Wildcard Domain DV *}
                {if $certIsWildcard && $certType == 'DV'}
                <p>{$MGLANG->T('autoinstallWildcardDvIntro')|default:'AutoInstallSSL is currently performing domain validation for your certificate.'}</p>
                <p>{$MGLANG->T('autoinstallWildcardDvMsg')|default:'If you chose email validation or if DNS validation is not available the CA will email the domain validation for approval to the following emails:'}
                    {if $dcv_method == 'email' && $approver_method}
                    <strong> {$approver_method|escape}</strong>
                    {elseif $approver_email}
                    <strong> {$approver_email|escape}</strong>
                    {/if}
                </p>
                <p>{$MGLANG->T('autoinstallOutroDomain')|default:'Once the CA validates your domain the certificate will be installed on your server.<br>We will notify you once the certificate is installed. You can check the status on your order detail page.'}</p>
                {/if}

                {* 2.3.3 Single Domain OV *}
                {if !$certIsWildcard && $certType == 'OV'}
                <p>{$MGLANG->T('autoinstallOvIntro')|default:'AutoInstallSSL is currently performing domain validation for your certificate.'}</p>
                <p><strong>{$MGLANG->T('autoinstallOvNextStep')|default:'Your next step:'}</strong><br>
                {$MGLANG->T('autoinstallOvMsg')|default:'Perform Organization Validation, the CA will be emailing and calling the admin contact to confirm the organization is accurate and the contact is authorized.'}</p>
                <p>{$MGLANG->T('autoinstallOutroDomainOrg')|default:'Once the CA validates your domain &amp; organization the certificate will be installed on your server.<br>We will notify you once the certificate is installed. You can check the status on your order detail page.'}</p>
                {/if}

                {* 2.3.4 Wildcard Domain OV *}
                {if $certIsWildcard && $certType == 'OV'}
                <p><strong>{$MGLANG->T('thankYou')|default:'Thank You'}</strong></p>
                <p>{$MGLANG->T('autoinstallWildcardOvIntro')|default:'AutoInstallSSL is currently performing domain validation for your certificate.'}</p>
                <p><strong>{$MGLANG->T('autoinstallWildcardOvNextSteps')|default:'Here are your next steps:'}</strong><br>
                {$MGLANG->T('autoinstallWildcardOvMsg')|default:'Perform Organization Validation, the CA will be emailing and calling the admin contact to confirm the organization is accurate and the contact is authorized. If you chose email validation or if DNS validation is not available the CA will email the domain validation for approval to the following emails:'}
                    {if $dcv_method == 'email' && $approver_method}
                    <strong> {$approver_method|escape}</strong>
                    {elseif $approver_email}
                    <strong> {$approver_email|escape}</strong>
                    {/if}
                </p>
                <p>{$MGLANG->T('autoinstallOutroDomainOrg')|default:'Once the CA validates your domain &amp; organization the certificate will be installed on your server.<br>We will notify you once the certificate is installed. You can check the status on your order detail page.'}</p>
                {/if}

            </div>
        </div>
        {/if}

        {* ---- DCV Instructions (for processing state) ---- *}
        {if $certState == 'processing'}
        <div class="panel panel-info" style="margin-top:10px;">
            <div class="panel-heading"><strong>{$MGLANG->T('dcvInstructions')|default:'Validation Instructions'}</strong></div>
            <div class="panel-body">
                {if $dcv_method == 'http' || $dcv_method == 'https'}
                <p>{$MGLANG->T('httpValidationInfo')|default:'Create the file below on your web server at the exact path shown. The CA will fetch this URL to verify domain ownership.'}</p>
                {if isset($approver_method[$dcv_method]) && isset($approver_method[$dcv_method].filename)}
                {assign var='dcvFilename' value=$approver_method[$dcv_method].filename}
                {assign var='dcvContent'  value=$approver_method[$dcv_method].content}
                <div class="ssl-mod-dcv-block">
                    <div style="margin-bottom:8px;">
                        <span style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.5px;">{$MGLANG->T('sslVerificationUrl')|default:'Verification URL'}</span>
                        <code style="display:block;margin-top:4px;word-break:break-all;">{$dcv_method}://{$domain|escape}/.well-known/pki-validation/{$dcvFilename|escape}</code>
                    </div>
                    <div style="margin-bottom:8px;">
                        <span style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.5px;">{$MGLANG->T('sslDirectoryOnServer')|default:'Directory on server'}</span>
                        <code style="display:block;margin-top:4px;">&lt;document_root&gt;/.well-known/pki-validation/</code>
                    </div>
                    <div style="margin-bottom:8px;">
                        <span style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.5px;">{$MGLANG->T('sslFilenameLabel')|default:'Filename'}</span>
                        <code style="display:block;margin-top:4px;">{$dcvFilename|escape}</code>
                    </div>
                    <div>
                        <span style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.5px;">{$MGLANG->T('sslFileContent')|default:'File content'}</span>
                        <code style="display:block;margin-top:4px;white-space:pre-wrap;text-align: left;">{strip}
                            {if is_array($dcvContent)}
                                {foreach $dcvContent as $line}
                                    {$line|escape}{"\n"}
                                {/foreach}
                            {else}
                                {$dcvContent|escape}
                            {/if}
                            {/strip}</code>
                    </div>
                </div>
                <div class="alert alert-info" style="margin-top:10px;font-size:12px;padding:8px 12px;">
                    <span class="glyphicon glyphicon-info-sign" style="margin-right:5px;"></span>
                    {$MGLANG->T('httpValidationTip')|default:'If your hosting is on cPanel, the document root is typically <strong>/home/cpuser/public_html/</strong>. Create the directories <code>.well-known/pki-validation/</code> inside it and upload the file. If autoinstall is enabled, the cron will attempt to do this automatically.'}
                </div>
                {if $btndownload}
                <a href="{$btndownload}" class="btn btn-default btn-sm" style="margin-top:6px;">
                    <span class="glyphicon glyphicon-download-alt"></span> {$MGLANG->T('sslDownloadValidationFile')|default:'Download Validation File'}
                </a>
                {/if}
                {/if}
                {elseif $dcv_method == 'dns'}
                <p>{$MGLANG->T('dnsValidationInfo')|default:'Add the following DNS record to your domain.'}</p>
                {if isset($approver_method.dns)}
                <div class="ssl-mod-dcv-block">
                    {if isset($approver_method.dns.host)}
                    <div style="margin-bottom:8px;">
                        <span style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.5px;">{$MGLANG->T('sslHostName')|default:'Host / Name'}</span>
                        <code style="display:block;margin-top:4px;word-break:break-all;">{$approver_method.dns.host|escape}</code>
                    </div>
                    <div style="margin-bottom:8px;">
                        <span style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.5px;">{$MGLANG->T('sslRecordType')|default:'Record Type'}</span>
                        <code style="display:block;margin-top:4px;">{if isset($approver_method.dns.type)}{$approver_method.dns.type|escape}{else}CNAME{/if}</code>
                    </div>
                    <div>
                        <span style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.5px;">{$MGLANG->T('sslValueLabel')|default:'Value'}</span>
                        <code style="display:block;margin-top:4px;word-break:break-all;">{$approver_method.dns.content|escape}</code>
                    </div>
                    {else}
                    <strong>{$MGLANG->T('sslRecord')|default:'Record:'}</strong>
                    <code style="word-break:break-all;">
                    {if isset($approver_method.dns.record)}
                        {$approver_method.dns.record|escape}
                    {elseif is_string($approver_method.dns)}
                        {$approver_method.dns|escape}
                    {/if}
                    </code>
                    {/if}
                </div>
                {/if}
                {elseif $dcv_method == 'email'}
                <p>
                    {$MGLANG->T('emailValidationInfo')|default:'A validation email has been sent to'} <strong>{$approver_method|escape}</strong>.
                    {$MGLANG->T('emailValidationClick')|default:'Please click the link in that email to complete validation.'}
                </p>
                {if $serviceid}
                <button type="button" class="btn btn-default btn-sm" id="ssl-resend-email-btn">
                    <span class="glyphicon glyphicon-envelope"></span>
                    {$MGLANG->T('resendValidationEmail')|default:'Resend Validation Email'}
                </button>
                {/if}
                {/if}
            </div>
        </div>
        {/if}

        {* ---- Change DCV Method Modal ---- *}
        {if $certState == 'processing'}
        <div class="modal fade" id="modal-change-dcv" tabindex="-1" role="dialog" aria-labelledby="modal-change-dcv-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modal-change-dcv-label">
                            <span class="glyphicon glyphicon-refresh" style="margin-right:6px;"></span>
                            {$MGLANG->T('changeDcvMethod')|default:'Change Validation Method'}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p style="color:#666;font-size:13px;margin-top:0;">{$MGLANG->T('changeDcvMethodInfo')|default:'Select a new validation method. The change will apply to all domains in this order.'}</p>
                        <div class="form-group">
                            <label style="font-weight:600;">{$MGLANG->T('validationMethod')|default:'Validation Method'}</label>
                            <select class="form-control" id="change-dcv-method-select">
                                {if !in_array($brand, $brandsWithOnlyEmailValidation)}
                                <option value="http">{$MGLANG->T('sslMethodHttp')|default:'HTTP File'}</option>
                                <option value="https">{$MGLANG->T('sslMethodHttps')|default:'HTTPS File'}</option>
                                <option value="dns">{$MGLANG->T('sslMethodDns')|default:'DNS TXT Record'}</option>
                                {/if}
                                <option value="email">{$MGLANG->T('sslMethodEmail')|default:'Email Approval'}</option>
                            </select>
                        </div>
                        <div id="change-dcv-email-section" style="display:none;">
                            <div class="form-group">
                                <label style="font-weight:600;">{$MGLANG->T('approverEmail')|default:'Approver Email'}</label>
                                <select class="form-control" id="change-dcv-email-select">
                                    <option value="">{$MGLANG->T('loadingEmails')|default:'Loading...'}</option>
                                </select>
                            </div>
                        </div>
                        <div id="change-dcv-msg" style="display:none;margin-bottom:0;" class="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('cancel')|default:'Cancel'}</button>
                        <button type="button" class="btn btn-primary" id="change-dcv-submit">
                            <span class="glyphicon glyphicon-ok" style="margin-right:4px;"></span>
                            {$MGLANG->T('saveChanges')|default:'Save Changes'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {* ---- Certificate Activity Timeline (autoinstall only) ---- *}
        {if $installType == 'autoinstall' && (($cronTasks && $cronTasks|@count > 0) || ($serviceLogs && $serviceLogs|@count > 0))}
        <div class="panel panel-default" style="margin-top:16px;">
            <div class="panel-heading" style="background:#f8f8f8;border-bottom:1px solid #e0e0e0;">
                <strong><span class="glyphicon glyphicon-list-alt" style="margin-right:6px;"></span>{$MGLANG->T('timelineTitle')|default:'Certificate Activity'}</strong>
            </div>
            <div class="panel-body" style="padding:0;">
                <ul class="ssl-mod-timeline" style="list-style:none;margin:0;padding:12px 16px;">

                    {if $cronTasks && $cronTasks|@count > 0}
                    {foreach $cronTasks as $task}
                    {assign var='taskIcon' value='glyphicon-time'}
                    {assign var='taskColor' value='#aaa'}
                    {if $task->status == 'done'}
                        {assign var='taskIcon' value='glyphicon-ok-circle'}
                        {assign var='taskColor' value='#27ae60'}
                    {elseif $task->status == 'processing'}
                        {assign var='taskIcon' value='glyphicon-refresh'}
                        {assign var='taskColor' value='#2980b9'}
                    {elseif $task->status == 'error'}
                        {assign var='taskIcon' value='glyphicon-remove-circle'}
                        {assign var='taskColor' value='#c0392b'}
                    {/if}
                    {assign var='statusLabel' value=$MGLANG->T('sslTimelineScheduled')|default:'Scheduled'}
                    {if $task->status == 'processing'}{assign var='statusLabel' value=$MGLANG->T('sslTimelineInProgress')|default:'In Progress'}
                    {elseif $task->status == 'done'}{assign var='statusLabel' value=$MGLANG->T('sslTimelineCompleted')|default:'Completed'}
                    {elseif $task->status == 'error'}{assign var='statusLabel' value=$MGLANG->T('sslTimelineAttention')|default:'Attention Required'}
                    {/if}
                    <li style="display:flex;align-items:center;padding:10px 0;border-bottom:1px solid #f0f0f0;">
                        <span class="glyphicon {$taskIcon}" style="color:{$taskColor};font-size:16px;margin-right:12px;flex-shrink:0;"></span>
                        <div style="flex:1;min-width:0;font-size:13px;color:#333;">{$task->msg nofilter}</div>
                        <div style="text-align:right;flex-shrink:0;margin-left:16px;">
                            <span style="font-size:11px;font-weight:700;color:{$taskColor};text-transform:uppercase;letter-spacing:.5px;">{$statusLabel}</span>
                            <div style="font-size:11px;color:#bbb;margin-top:2px;">{$task->created_at|date_format:'%d %b %Y'}</div>
                        </div>
                    </li>
                    {/foreach}
                    {/if}

                    {if $serviceLogs && $serviceLogs|@count > 0}
                    {foreach $serviceLogs as $log}
                    {if $log->type == 'success'}
                        {assign var='logIcon' value='glyphicon-ok-sign'}
                        {assign var='logColor' value='#27ae60'}
                    {elseif $log->type == 'error'}
                        {assign var='logIcon' value='glyphicon-exclamation-sign'}
                        {assign var='logColor' value='#c0392b'}
                    {else}
                        {assign var='logIcon' value='glyphicon-info-sign'}
                        {assign var='logColor' value='#2980b9'}
                    {/if}
                    <li style="display:flex;align-items:center;padding:10px 0;border-bottom:1px solid #f0f0f0;">
                        <span class="glyphicon {$logIcon}" style="color:{$logColor};font-size:16px;margin-right:12px;flex-shrink:0;"></span>
                        <div style="flex:1;min-width:0;font-size:13px;color:#333;">{$log->msg nofilter}</div>
                        <div style="text-align:right;flex-shrink:0;margin-left:16px;">
                            <div style="font-size:11px;color:#bbb;">{$log->date|date_format:'%d %b %Y'}</div>
                        </div>
                    </li>
                    {/foreach}
                    {/if}

                    {if (!$cronTasks || $cronTasks|@count == 0) && (!$serviceLogs || $serviceLogs|@count == 0)}
                    <li style="padding:12px 0;color:#aaa;font-style:italic;">
                        {$MGLANG->T('timelineEmpty')|default:'No activity recorded yet.'}
                    </li>
                    {/if}

                </ul>
            </div>
        </div>
        {/if}

        {* ---- Renewal notice ---- *}
        {if $displayRenewButton && $certState != 'cancelled' && $certState != 'awaiting_config'}
        <div class="alert alert-warning" style="margin-top:10px;">
            <strong>{$MGLANG->T('renewalWarning')|default:'Your certificate expires soon.'}</strong>
            {if $serviceid}
            <button type="button" class="btn btn-warning btn-sm" style="margin-left:15px;" id="ssl-mod-renew-btn">
                {$MGLANG->T('renew')|default:'Renew Now'}
            </button>
            {/if}
        </div>
        {/if}

    </div>{* end overview *}

{/if}{* end error/else *}

{/if}{* end allOk *}
</div>{* end ssl-mod *}

<script type="text/javascript">
(function ($) {
    var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1&mg-model=home';

    {if $privateKey}
    $('#ssl-mod-show-key').on('click', function () {
        $('#modal-private-key').modal('show');
    });
    {/if}

    {if $btnInstallCrt}
    $('#ssl-mod-install-btn').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="glyphicon glyphicon-refresh"></span> {$MGLANG->T('sslInstalling')|default:'Installing...'}');
        $.post(serviceUrl, { 'mg-action': 'installCertificate' }, function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var d = JSON.parse(ret);
                if (d.data && d.data.success) {
                    $('#MGAlerts').alerts('success', d.data.message || '{$MGLANG->T('sslInstalledSuccessfully')|default:'Certificate installed successfully.'}');
                    btn.html('<span class="glyphicon glyphicon-ok"></span> {$MGLANG->T('sslBadgeInstalled')|default:'Installed'}');
                } else {
                    $('#MGAlerts').alerts('error', (d.data && d.data.message) || '{$MGLANG->T('sslInstallationFailed')|default:'Installation failed.'}');
                    btn.prop('disabled', false).html('<span class="glyphicon glyphicon-cloud-upload"></span> {$MGLANG->T('installCertificate')|default:'Install on Server'}');
                }
            } catch (e) { btn.prop('disabled', false); }
        });
    });
    {/if}

    {if $certState == 'processing'}
    $('#ssl-resend-email-btn').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true);
        $.post(serviceUrl, { 'mg-action': 'resendValidationEmail' }, function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var d = JSON.parse(ret);
                $('#MGAlerts').alerts('success', (d.data && d.data.success) || '{$MGLANG->T('sslEmailSent')|default:'Validation email sent.'}');
            } catch (e) {}
            btn.prop('disabled', false);
        });
    });
    {/if}

    {if $certState == 'processing'}
    (function () {
        var changeDcvDomains = [{if $domain}'{$domain|default:''|addslashes}',{/if}{foreach $sans as $sanName => $sanData}'{$sanName|addslashes}',{/foreach}];
        var changeDcvBrand   = '{$brand|default:''|addslashes}';

        function loadChangeDcvEmails() {
            var $sel = $('#change-dcv-email-select');
            $sel.html('<option value="">{$MGLANG->T('loadingEmails')|default:'Loading...'}</option>');
            var domain = changeDcvDomains.length ? changeDcvDomains[0] : '';
            $.post(serviceUrl, { 'mg-action': 'getApprovalEmailsForDomain', 'domain': domain, 'brand': changeDcvBrand }, function (ret) {
                ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
                try {
                    var d = JSON.parse(ret);
                    var emails = (d.data && d.data.domainEmails) ? d.data.domainEmails : [];
                    $sel.empty();
                    if (emails.length) {
                        $.each(emails, function (i, e) { $sel.append($('<option>').val(e).text(e)); });
                    } else {
                        $sel.html('<option value="">{$MGLANG->T('noEmailsFound')|default:'No emails found'}</option>');
                    }
                } catch (ex) {}
            });
        }

        $('#change-dcv-method-select').on('change', function () {
            if ($(this).val() === 'email') {
                $('#change-dcv-email-section').show();
                loadChangeDcvEmails();
            } else {
                $('#change-dcv-email-section').hide();
            }
        });

        $('#modal-change-dcv').on('show.bs.modal', function () {
            $('#change-dcv-msg').hide().removeClass('alert-success alert-danger');
            $('#change-dcv-submit').prop('disabled', false).html('<span class="glyphicon glyphicon-ok" style="margin-right:4px;"></span>{$MGLANG->T('saveChanges')|default:'Save Changes'}');
            {if in_array($brand, $brandsWithOnlyEmailValidation)}
            $('#change-dcv-method-select').val('email').trigger('change');
            {/if}
        });

        $('#change-dcv-submit').on('click', function () {
            var btn    = $(this);
            var method = $('#change-dcv-method-select').val();
            var email  = $('#change-dcv-email-select').val();
            var $msg   = $('#change-dcv-msg');

            if (method === 'email' && !email) {
                $msg.removeClass('alert-success').addClass('alert alert-danger').text('{$MGLANG->T('selectApproverEmailFirst')|default:'Please select an approver email.'}').show();
                return;
            }

            btn.prop('disabled', true).html('<span class="glyphicon glyphicon-refresh"></span> {$MGLANG->T('saving')|default:'Saving...'}');
            $msg.hide();

            var newDcvMethods = {};
            var effectiveMethod = (method === 'email' && email) ? email : method;
            $.each(changeDcvDomains, function (i, d) { newDcvMethods[d] = effectiveMethod; });

            $.post(serviceUrl, {
                'mg-action':     'revalidate',
                'newDcvMethods': newDcvMethods,
                'newdomains':    changeDcvDomains,
                'brand':         changeDcvBrand
            }, function (ret) {
                ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
                try {
                    var d    = JSON.parse(ret);
                    var ok   = d.data && (d.data.success == 1 || d.data.success === true);
                    var text = (d.data && d.data.msg) || '';

                    if (ok) {
                        var finish = function () {
                            $msg.removeClass('alert-danger').addClass('alert alert-success').text(text || '{$MGLANG->T('changeDcvSuccess')|default:'Validation method changed successfully.'}').show();
                            btn.html('<span class="glyphicon glyphicon-ok"></span> {$MGLANG->T('saveChanges')|default:'Save Changes'}');
                            setTimeout(function () { location.reload(); }, 2000);
                        };
                        if (method === 'email' && email) {
                            $.post(serviceUrl, { 'mg-action': 'changeApproverEmail', 'serviceId': '{$serviceid}', 'newEmail': email }, finish);
                        } else {
                            finish();
                        }
                    } else {
                        $msg.removeClass('alert-success').addClass('alert alert-danger').text(text || '{$MGLANG->T('errorOccurred')|default:'An error occurred.'}').show();
                        btn.prop('disabled', false).html('<span class="glyphicon glyphicon-ok" style="margin-right:4px;"></span>{$MGLANG->T('saveChanges')|default:'Save Changes'}');
                    }
                } catch (ex) {
                    btn.prop('disabled', false).html('<span class="glyphicon glyphicon-ok" style="margin-right:4px;"></span>{$MGLANG->T('saveChanges')|default:'Save Changes'}');
                }
            });
        });
    }());
    {/if}

    {if $displayRenewButton}
    $('#ssl-mod-renew-btn').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true).html('{$MGLANG->T('sslProcessing')|default:'Processing...'}');
        $.post(serviceUrl, { 'mg-action': 'renew' }, function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var d = JSON.parse(ret);
                if (d.data && d.data.success) {
                    $('#MGAlerts').alerts('success', d.data.msg || d.data.success);
                    btn.html('{$MGLANG->T('sslRenewalCreated')|default:'Renewal Invoice Created'}');
                } else {
                    $('#MGAlerts').alerts('error', (d.data && d.data.error) || 'An error occurred.');
                    btn.prop('disabled', false).html('{$MGLANG->T('renew')|default:'Renew Now'}');
                }
            } catch (e) { btn.prop('disabled', false); }
        });
    });
    {/if}

}(jQuery));
</script>
