<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div id="cs-alerts">
    {if $refreshError}
        <div class="alert alert-warning">{$MGLANG->T('csRefreshFailed')} {$refreshError|escape}</div>
    {/if}
</div>

{if $csStatus == 'pending' || $csStatus == 'processing'}
    <div class="alert alert-info">{$MGLANG->T('csInfoPending')}</div>
{elseif $csStatus == 'active'}
    <div class="alert alert-success">
        {if $csMethod == 'ship-token'}
            {$MGLANG->T('csInfoActiveShipToken')}
        {elseif $csMethod == 'hsm'}
            {$MGLANG->T('csInfoActiveHsm')}
        {else}
            {$MGLANG->T('csInfoActiveExistingToken')}
        {/if}
    </div>
{elseif $csStatus == 'incomplete' || $csStatus == 'unpaid'}
    <div class="alert alert-warning">{$MGLANG->T('csInfoContactSupport')}</div>
{elseif $csStatus == 'failed' || $csStatus == 'rejected'}
    <div class="alert alert-danger">{$MGLANG->T('csInfoContactSupport')}</div>
{/if}

<table id="mainTable" class="table table-bordered">
    <colgroup>
        <col style="width: 25%"/>
        <col style="width: 75%"/>
    </colgroup>
    <tbody>
        <tr>
            <td class="text-left">{$MGLANG->T('csOrderStatus')}</td>
            <td class="text-left">
                {if $csStatus == 'active'}
                    <span class="label label-success">{$csStatusText}</span>
                {elseif $csStatus == 'pending' || $csStatus == 'processing'}
                    <span class="label label-warning">{$csStatusText}</span>
                {elseif $csStatus == 'failed' || $csStatus == 'rejected' || $csStatus == 'cancelled' || $csStatus == 'expired'}
                    <span class="label label-danger">{$csStatusText}</span>
                {else}
                    <span class="label label-default">{$csStatusText}</span>
                {/if}
            </td>
        </tr>
        <tr>
            <td class="text-left">{$MGLANG->T('csProductName')}</td>
            <td class="text-left">{$productName|escape}</td>
        </tr>
        <tr>
            <td class="text-left">{$MGLANG->T('csOrderId')}</td>
            <td class="text-left">{$csOrderId|escape}{if $csVendorOrderId} <span class="text-muted">({$MGLANG->T('csVendorOrderId')}: {$csVendorOrderId|escape})</span>{/if}</td>
        </tr>
        {if $csOrganization}
        <tr>
            <td class="text-left">{$MGLANG->T('csOrganization')}</td>
            <td class="text-left">{$csOrganization|escape}</td>
        </tr>
        {/if}
        <tr>
            <td class="text-left">{$MGLANG->T('csDelivery')}</td>
            <td class="text-left">{$csDelivery|escape}</td>
        </tr>
        {if $csOrderedAt}
        <tr>
            <td class="text-left">{$MGLANG->T('csOrderedAt')}</td>
            <td class="text-left">{$csOrderedAt|escape}</td>
        </tr>
        {/if}
        {if $csValidFrom}
        <tr>
            <td class="text-left">{$MGLANG->T('csValidFrom')}</td>
            <td class="text-left">{$csValidFrom|escape}</td>
        </tr>
        {/if}
        {if $csValidTill}
        <tr>
            <td class="text-left">{$MGLANG->T('csValidTill')}</td>
            <td class="text-left">{$csValidTill|escape}</td>
        </tr>
        {/if}
        <tr>
            <td class="text-left">{$MGLANG->T('Actions')}</td>
            <td class="text-left">
                <button id="cs-refresh" type="button" class="btn btn-default btn-sm">
                    {$MGLANG->T('csRefreshDetails')}
                </button>
            </td>
        </tr>
    </tbody>
</table>

<script type="text/javascript">
{literal}
(function () {
    var alerts = document.getElementById('cs-alerts');
    var btn    = document.getElementById('cs-refresh');
    if (!btn) { return; }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        alerts.innerHTML = '';
        $.post(
            'clientarea.php?action=productdetails&id={/literal}{$serviceid}{literal}&json=1&mg-model=home',
            {'mg-action': 'refreshCodeSigningDetails'},
            function (ret) {
                ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
                try {
                    var payload = JSON.parse(ret);
                    if (payload.result === 'error') {
                        alerts.innerHTML = '<div class="alert alert-danger">' + (payload.error || '{/literal}{$MGLANG->T('csErrorOccurred')|escape:'javascript'}{literal}') + '</div>';
                        btn.disabled = false;
                    } else {
                        window.location.reload();
                    }
                } catch (e) {
                    alerts.innerHTML = '<div class="alert alert-danger">{/literal}{$MGLANG->T('csErrorOccurred')|escape:'javascript'}{literal}</div>';
                    btn.disabled = false;
                }
            }
        ).fail(function () {
            alerts.innerHTML = '<div class="alert alert-danger">{/literal}{$MGLANG->T('csErrorOccurred')|escape:'javascript'}{literal}</div>';
            btn.disabled = false;
        });
    });
}());
{/literal}
</script>
{/if}
