<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
    <table id="mainTable" class="table table-bordered">
        <colgroup>
            <col style="width: 25%"/>
            <col style="width: 75%"/>
        </colgroup>
        <tbody>
            <tr>
                <td class="text-left">{$MGLANG->T('acmeOrderStatus')}</td>
                <td class="text-left">
                    {if $subscription && $subscription->status}
                        {assign var=acmeStatus value=$subscription->status|lower}
                    {else}
                        {assign var=acmeStatus value='pending'}
                    {/if}
                    {if $acmeStatus == 'active'}
                        <span class="label label-success">{$acmeStatus|capitalize|escape}</span>
                    {elseif $acmeStatus == 'pending'}
                        <span class="label label-warning">{$acmeStatus|capitalize|escape}</span>
                    {else}
                        {$acmeStatus|capitalize|escape}
                    {/if}
                </td>
            </tr>
            <tr>
                <td class="text-left">{$MGLANG->T('acmeProductName')}</td>
                <td class="text-left">{$productName|escape}</td>
            </tr>
            {if $subscription}
                <tr>
                    <td class="text-left">{$MGLANG->T('acmeAccountId')}</td>
                    <td class="text-left">{$subscription->acme_account_id|escape}</td>
                </tr>
                <tr>
                    <td class="text-left">{$MGLANG->T('acmeEabKid')}</td>
                    <td class="text-left">{$subscription->eab_kid|escape}</td>
                </tr>
                <tr>
                    <td class="text-left">{$MGLANG->T('acmeEabHmacKey')}</td>
                    <td class="text-left"><code>{$subscription->eab_hmac_key|escape}</code></td>
                </tr>
                <tr>
                    <td class="text-left">{$MGLANG->T('acmeServerUrl')}</td>
                    <td class="text-left">{$subscription->server_url|escape}</td>
                </tr>
                <tr>
                    <td class="text-left">{if $subscription->auto_renew}{$MGLANG->T('acmeRenewalDate')}{else}{$MGLANG->T('acmeExpireDate')}{/if}</td>
                    <td class="text-left">{$subscription->renewal_date|escape}</td>
                </tr>
                {if $subscription->auto_renew && $nextInvoiceDate}
                    <tr>
                        <td class="text-left">{$MGLANG->T('acmeNextInvoiceDate')}</td>
                        <td class="text-left">{$nextInvoiceDate|escape}</td>
                    </tr>
                {/if}
                <tr>
                    <td class="text-left">{$MGLANG->T('acmeAutoRenew')}</td>
                    <td class="text-left">
                        {if $subscription->auto_renew}
                            <span class="label label-success">Enabled</span>
                        {else}
                            <span class="label label-default">Disabled</span>
                        {/if}
                    </td>
                </tr>
            {/if}
            <tr>
                <td class="text-left">{$MGLANG->T('acmeDomains')}</td>
                <td class="text-left">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th>{$MGLANG->T('domain')}</th>
                            <th>{$MGLANG->T('acmeDomainType')}</th>
                            <th>{$MGLANG->T('acmeDomainStatus')}</th>
                            <th>{$MGLANG->T('acmeDateAdded')}</th>
                            <th>{$MGLANG->T('Actions')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {if $domains|@count > 0}
                            {foreach $domains as $domainRow}
                                <tr>
                                    <td>{$domainRow->domain|escape}</td>
                                    <td>{$domainRow->domain_type|escape}</td>
                                    <td>
                                        {assign var=acmeDomainStatus value=$domainRow->status|lower}
                                        {if $acmeDomainStatus == 'added'}
                                            <span class="label label-success">{$MGLANG->T('acmeStatusActive')}</span>
                                        {elseif $acmeDomainStatus == 'removed'}
                                            <span class="label label-default">{$MGLANG->T('acmeStatusRemoved')}</span>
                                        {else}
                                            <span class="label label-info">{$acmeDomainStatus|capitalize|escape}</span>
                                        {/if}
                                    </td>
                                    <td>{$domainRow->added_at|escape}</td>
                                    <td>
                                        {if $domainRow->status == 'added' && $subscription}
                                            <button class="btn btn-xs btn-default acme-action" data-action="removeDomain" data-domain="{$domainRow->domain|escape:'htmlall'}">{$MGLANG->T('acmeRemoveDomain')}</button>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="5">{$MGLANG->T('acmeNoDomains')}</td>
                            </tr>
                        {/if}
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="text-left">{$MGLANG->T('acmeTotalSans')}</td>
                <td class="text-left">
                    <span class="label label-default">{$MGLANG->T('acmeSingleDomainsLabel')}: {$singleSansCurrent|intval} / {$singleSansPurchased|intval}</span>
                    <span class="label label-info">{$MGLANG->T('acmeWildcardDomainsLabel')}: {$wildcardSansCurrent|intval} / {$wildcardSansPurchased|intval}</span>
                    <span class="label label-success">{$MGLANG->T('acmeBothTypesLabel')}: {$totalSansCurrent|intval} / {$totalSansPurchased|intval}</span>
                </td>
            </tr>
            <tr>
                <td class="text-left">{$MGLANG->T('Actions')}</td>
                <td class="text-left">
                    {if !$subscription || !$subscription->api_order_id}
                        <button style="margin-bottom: 10px;" class="btn btn-default acme-action" data-action="createSubscription">{$MGLANG->T('acmeCreateSubscription')}</button>
                    {else}
                        {if $canAddDomains}
                            <button style="margin-bottom: 10px;" class="btn btn-primary acme-action" data-action="addDomain">Add domain</button>
                        {/if}
                        <a style="margin-bottom: 10px;" class="btn btn-default" href="upgrade.php?type=configoptions&id={$serviceid|intval}">{$MGLANG->T('acmeBuyMoreDomains')}</a>
                        {if $subscription->auto_renew}
                            <button style="margin-bottom: 10px;" class="btn btn-default acme-action" data-action="stopAutoRenewal">{$MGLANG->T('acmeStopAutoRenewal')}</button>
                        {/if}
                        <button style="margin-bottom: 10px;" class="btn btn-danger acme-action" data-action="cancelSubscription">{$MGLANG->T('acmeCancelSubscription')}</button>
                    {/if}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="modal fade" id="acmeRemoveDomainModal" tabindex="-1" role="dialog" aria-labelledby="acmeRemoveDomainModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="acmeRemoveDomainModalLabel">{$MGLANG->T('acmeRemoveDomain')}</h4>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove domain <strong id="acmeRemoveDomainName"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                    <button type="button" class="btn btn-danger" id="acmeConfirmRemoveDomain">{$MGLANG->T('acmeRemoveDomain')}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="acmeAddDomainModal" tabindex="-1" role="dialog" aria-labelledby="acmeAddDomainModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="text-align: left;">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="acmeAddDomainModalLabel">Add domain</h4>
                </div>
                <div class="modal-body">
                    <div id="acmeAddDomainError" class="alert alert-danger" style="display:none;"></div>
                    <div class="alert alert-info">
                        Available slots:
                        Single: {$availableSingleSlots|intval}
                        {if $allowWildcard}
                            | Wildcard: {$availableWildcardSlots|intval}
                        {/if}
                    </div>
                    {if $availableSingleSlots > 0}
                        <div class="form-group">
                            <label for="acmeAddSingleDomainsInput">{$MGLANG->T('acmeSingleDomainsLabel')} ({$availableSingleSlots|intval})</label>
                            <textarea id="acmeAddSingleDomainsInput" rows="4" class="form-control"></textarea>
                        </div>
                    {/if}
                    {if $allowWildcard && $availableWildcardSlots > 0}
                        <div class="form-group">
                            <label for="acmeAddWildcardDomainsInput">{$MGLANG->T('acmeWildcardDomainsLabel')} ({$availableWildcardSlots|intval})</label>
                            <textarea id="acmeAddWildcardDomainsInput" rows="4" class="form-control"></textarea>
                        </div>
                    {/if}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                    <button type="button" class="btn btn-primary" id="acmeConfirmAddDomain">Add domain</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="acmeStopAutoRenewalModal" tabindex="-1" role="dialog" aria-labelledby="acmeStopAutoRenewalModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="acmeStopAutoRenewalModalLabel">{$MGLANG->T('acmeStopAutoRenewal')}</h4>
                </div>
                <div class="modal-body">
                    {$MGLANG->T('acmeStopAutoRenewalConfirm')}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                    <button type="button" class="btn btn-warning" id="acmeConfirmStopAutoRenewal">{$MGLANG->T('acmeStopAutoRenewal')}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="acmeCancelSubscriptionModal" tabindex="-1" role="dialog" aria-labelledby="acmeCancelSubscriptionModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="acmeCancelSubscriptionModalLabel">{$MGLANG->T('acmeCancelSubscription')}</h4>
                </div>
                <div class="modal-body">
                    {$MGLANG->T('acmeCancelSubscriptionConfirm')}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                    <button type="button" class="btn btn-danger" id="acmeConfirmCancelSubscription">{$MGLANG->T('acmeCancelSubscription')}</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';
            var selectedDomainToRemove = null;

            function runAction(action, data, options) {
                options = options || {};
                data = data || {};
                data['mg-action'] = action;
                $.post(serviceUrl, data, function (ret) {
                    ret = ret.replace("<JSONRESPONSE#", "").replace("#ENDJSONRESPONSE>", "");
                    var payload = JSON.parse(ret);
                    if (payload.result === 'error') {
                        var errorMessage = payload.error || 'Error';
                        if (typeof options.onError === 'function') {
                            options.onError(errorMessage, payload);
                        } else {
                            $('#MGAlerts').alerts('error', errorMessage);
                        }
                        return;
                    }
                    var msg = (payload.data && payload.data.message) ? payload.data.message : 'Done';
                    if (typeof options.onSuccess === 'function') {
                        options.onSuccess(payload);
                    }
                    $('#MGAlerts').alerts('success', msg);
                    window.setTimeout(function () { window.location.reload(); }, 1200);
                });
            }

            $('.acme-action').on('click', function () {
                var action = $(this).data('action');
                var domain = $(this).data('domain');

                if (action === 'createSubscription') {
                    var domainsInput = $('#acmeDomainsInput').val();
                    var domainType = $('#acmeDomainType').val();
                    runAction('createSubscription', { domains: domainsInput, domain_type: domainType });
                    return;
                }
                if (action === 'addDomain') {
                    $('#acmeAddDomainError').hide().text('');
                    $('#acmeAddDomainModal').modal('show');
                    return;
                }
                if (action === 'removeDomain') {
                    selectedDomainToRemove = domain || null;
                    $('#acmeRemoveDomainName').text(selectedDomainToRemove || '');
                    $('#acmeRemoveDomainModal').modal('show');
                    return;
                }
                if (action === 'cancelSubscription') {
                    $('#acmeCancelSubscriptionModal').modal('show');
                    return;
                }
                if (action === 'stopAutoRenewal') {
                    $('#acmeStopAutoRenewalModal').modal('show');
                }
            });

            $('#acmeConfirmRemoveDomain').on('click', function () {
                if (!selectedDomainToRemove) {
                    return;
                }
                $('#acmeRemoveDomainModal').modal('hide');
                runAction('removeDomain', { domain: selectedDomainToRemove });
            });

            $('#acmeConfirmAddDomain').on('click', function () {
                var singleDomainsInput = $('#acmeAddSingleDomainsInput').length ? $('#acmeAddSingleDomainsInput').val() : '';
                var wildcardDomainsInput = $('#acmeAddWildcardDomainsInput').length ? $('#acmeAddWildcardDomainsInput').val() : '';
                runAction('buyMoreDomains', {
                    single_domains: singleDomainsInput,
                    wildcard_domains: wildcardDomainsInput
                }, {
                    onError: function (errorMessage) {
                        $('#acmeAddDomainError').text(errorMessage).show();
                    },
                    onSuccess: function () {
                        $('#acmeAddDomainError').hide().text('');
                        $('#acmeAddDomainModal').modal('hide');
                    }
                });
            });

            $('#acmeConfirmStopAutoRenewal').on('click', function () {
                $('#acmeStopAutoRenewalModal').modal('hide');
                runAction('stopAutoRenewal', {});
            });

            $('#acmeConfirmCancelSubscription').on('click', function () {
                $('#acmeCancelSubscriptionModal').modal('hide');
                runAction('cancelSubscription', {});
            });
        });
    </script>
{/if}
