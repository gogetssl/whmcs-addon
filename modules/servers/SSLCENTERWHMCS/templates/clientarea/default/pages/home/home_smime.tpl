<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div id="smime-alerts">
    {if $refreshError}
        <div class="alert alert-warning">{$MGLANG->T('smimeRefreshFailed')} {$refreshError|escape}</div>
    {/if}
</div>

{if $smimeStatus == 'pending' || $smimeStatus == 'processing'}
    <div class="alert alert-info">
        {if $smimeAllApproved}
            {$MGLANG->T('smimeInfoApproved')}
        {else}
            {$MGLANG->T('smimeInfoPending')}
        {/if}
    </div>
{elseif $smimeStatus == 'active'}
    <div class="alert alert-success">{$MGLANG->T('smimeInfoActive')}</div>
{elseif $smimeStatus == 'incomplete' || $smimeStatus == 'unpaid'}
    <div class="alert alert-warning">{$MGLANG->T('smimeInfoContactSupport')}</div>
{elseif $smimeStatus == 'failed' || $smimeStatus == 'rejected'}
    <div class="alert alert-danger">{$MGLANG->T('smimeInfoContactSupport')}</div>
{/if}

<table id="mainTable" class="table table-bordered">
    <colgroup>
        <col style="width: 25%"/>
        <col style="width: 75%"/>
    </colgroup>
    <tbody>
        <tr>
            <td class="text-left">{$MGLANG->T('smimeOrderStatus')}</td>
            <td class="text-left">
                {if $smimeStatus == 'active'}
                    <span class="label label-success">{$smimeStatusText}</span>
                {elseif $smimeStatus == 'pending' || $smimeStatus == 'processing'}
                    <span class="label label-warning">{$smimeStatusText}</span>
                {elseif $smimeStatus == 'failed' || $smimeStatus == 'rejected' || $smimeStatus == 'cancelled' || $smimeStatus == 'expired'}
                    <span class="label label-danger">{$smimeStatusText}</span>
                {else}
                    <span class="label label-default">{$smimeStatusText}</span>
                {/if}
            </td>
        </tr>
        <tr>
            <td class="text-left">{$MGLANG->T('smimeProductName')}</td>
            <td class="text-left">{$productName|escape}</td>
        </tr>
        <tr>
            <td class="text-left">{$MGLANG->T('smimeOrderId')}</td>
            <td class="text-left">{$smimeOrderId|escape}{if $smimeVendorOrderId} <span class="text-muted">({$MGLANG->T('smimeVendorOrderId')}: {$smimeVendorOrderId|escape})</span>{/if}</td>
        </tr>
        <tr>
            <td class="text-left">{$MGLANG->T('smimeEmails')}</td>
            <td class="text-left">
                {foreach from=$smimeEmails item=entry name=emails}
                    <div style="margin-bottom: 4px;">
                        <code>{$entry.email|escape}</code>
                        {if $smimeStatus == 'active' || $entry.approved}
                            <span class="label label-success">{$MGLANG->T('smimeEmailValidated')}</span>
                        {elseif $smimeStatus == 'pending' || $smimeStatus == 'processing'}
                            <span class="label label-warning">{$MGLANG->T('smimeEmailAwaitingValidation')}</span>
                        {/if}
                        {if $smarty.foreach.emails.first && $smimeEmails|@count > 1}
                            <span class="text-muted">({$MGLANG->T('smimeCommonName')})</span>
                        {/if}
                    </div>
                {foreachelse}
                    <span class="text-muted">-</span>
                {/foreach}
            </td>
        </tr>
        {if $smimeOrderedAt}
        <tr>
            <td class="text-left">{$MGLANG->T('smimeOrderedAt')}</td>
            <td class="text-left">{$smimeOrderedAt|escape}</td>
        </tr>
        {/if}
        {if $smimeValidFrom}
        <tr>
            <td class="text-left">{$MGLANG->T('smimeValidFrom')}</td>
            <td class="text-left">{$smimeValidFrom|escape}</td>
        </tr>
        {/if}
        {if $smimeValidTill}
        <tr>
            <td class="text-left">{$MGLANG->T('smimeValidTill')}</td>
            <td class="text-left">{$smimeValidTill|escape}</td>
        </tr>
        {/if}
        <tr>
            <td class="text-left">{$MGLANG->T('Actions')}</td>
            <td class="text-left">
                <button id="smime-refresh" type="button" class="btn btn-default btn-sm">
                    {$MGLANG->T('smimeRefreshDetails')}
                </button>
            </td>
        </tr>
    </tbody>
</table>

<script type="text/javascript">
{literal}
(function () {
    var alerts = document.getElementById('smime-alerts');
    var btn    = document.getElementById('smime-refresh');
    if (!btn) { return; }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        alerts.innerHTML = '';
        $.post(
            'clientarea.php?action=productdetails&id={/literal}{$serviceid}{literal}&json=1&mg-model=home',
            {'mg-action': 'refreshSmimeDetails'},
            function (ret) {
                ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
                try {
                    var payload = JSON.parse(ret);
                    if (payload.result === 'error') {
                        alerts.innerHTML = '<div class="alert alert-danger">' + (payload.error || '{/literal}{$MGLANG->T('smimeErrorOccurred')}{literal}') + '</div>';
                        btn.disabled = false;
                    } else {
                        window.location.reload();
                    }
                } catch (e) {
                    alerts.innerHTML = '<div class="alert alert-danger">{/literal}{$MGLANG->T('smimeErrorOccurred')}{literal}</div>';
                    btn.disabled = false;
                }
            }
        ).fail(function () {
            alerts.innerHTML = '<div class="alert alert-danger">{/literal}{$MGLANG->T('smimeErrorOccurred')}{literal}</div>';
            btn.disabled = false;
        });
    });
}());
{/literal}
</script>
{/if}
