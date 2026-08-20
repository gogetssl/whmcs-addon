<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div id="ais-alerts"></div>

<table id="mainTable" class="table table-bordered">
    <colgroup>
        <col style="width: 25%"/>
        <col style="width: 75%"/>
    </colgroup>
    <tbody>
        <tr>
            <td class="text-left">{$MGLANG->T('aisOrderStatus')}</td>
            <td class="text-left">
                {if $subscription && $subscription->status}
                    {assign var=aisStatus value=$subscription->status|lower}
                {else}
                    {assign var=aisStatus value='pending'}
                {/if}
                {if $aisStatus == 'active'}
                    <span class="label label-success">{$aisStatus|capitalize|escape}</span>
                {elseif $aisStatus == 'pending'}
                    <span class="label label-warning">{$aisStatus|capitalize|escape}</span>
                {else}
                    {$aisStatus|capitalize|escape}
                {/if}
            </td>
        </tr>
        <tr>
            <td class="text-left">{$MGLANG->T('aisProductName')}</td>
            <td class="text-left">{$productName|escape}</td>
        </tr>

        {if $subscription}
            {if $subscription->autoinstall_status}
            <tr>
                <td class="text-left">{$MGLANG->T('aisAutoinstallStatus')}</td>
                <td class="text-left">{$subscription->autoinstall_status|capitalize|escape}</td>
            </tr>
            {/if}

            {if $subscription->renewal_date}
            <tr>
                <td class="text-left">{$MGLANG->T('aisRenewalDate')}</td>
                <td class="text-left">{$subscription->renewal_date|escape}</td>
            </tr>
            {/if}

            {if $subscription->login_sso_link}
            <tr>
                <td class="text-left">{$MGLANG->T('aisLoginLink')}</td>
                <td class="text-left">
                    <a href="{$subscription->login_sso_link|escape:'htmlall'}" target="_blank" class="btn btn-primary btn-sm">
                        {$MGLANG->T('aisLoginLink')}
                    </a>
                </td>
            </tr>
            {/if}

            {if $subscription->manage_sso_link}
            <tr>
                <td class="text-left">{$MGLANG->T('aisManageLink')}</td>
                <td class="text-left">
                    <a href="{$subscription->manage_sso_link|escape:'htmlall'}" target="_blank" class="btn btn-default btn-sm">
                        {$MGLANG->T('aisManageLink')}
                    </a>
                </td>
            </tr>
            {/if}
        {/if}

        <tr>
            <td class="text-left">{$MGLANG->T('Actions')}</td>
            <td class="text-left">
                <button id="ais-refresh" class="btn btn-default btn-sm">
                    {$MGLANG->T('aisRefreshDetails')}
                </button>
            </td>
        </tr>
    </tbody>
</table>

<script type="text/javascript">
{literal}
document.getElementById('ais-refresh').addEventListener('click', function () {
    var btn    = this;
    var alerts = document.getElementById('ais-alerts');
    btn.disabled = true;
    alerts.innerHTML = '';

    $.post(
        'clientarea.php?action=productdetails&id={/literal}{$serviceid}{literal}&json=1&mg-model=home',
        {'mg-action': 'refreshAisDetails'},
        function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var payload = JSON.parse(ret);
                if (payload.result === 'error') {
                    alerts.innerHTML = '<div class="alert alert-danger">' + (payload.error || 'An error occurred.') + '</div>';
                    btn.disabled = false;
                } else {
                    var msg = (payload.data && payload.data.message) ? payload.data.message : '{/literal}{$MGLANG->T('aisDetailsRefreshed')}{literal}';
                    alerts.innerHTML = '<div class="alert alert-success">' + msg + '</div>';
                    setTimeout(function () { window.location.reload(); }, 1000);
                }
            } catch (e) {
                alerts.innerHTML = '<div class="alert alert-danger">An error occurred.</div>';
                btn.disabled = false;
            }
        }
    );
});
{/literal}
</script>
{/if}
