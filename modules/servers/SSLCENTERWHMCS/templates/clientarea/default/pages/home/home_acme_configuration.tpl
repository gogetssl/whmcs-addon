<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
    <div class="panel panel-default" style="text-align: left">
        <div class="panel-heading">
            <h2>{$MGLANG->T('acmeSubscriptionConfigurationTitle')}</h2>
        </div>
        <div class="panel-body">
            <div class="alert alert-info" style="margin-bottom: 45px;">
                {$MGLANG->T('acmeSubscriptionConfigurationInfo')}
            </div>

            <div style="margin-bottom: 10px;">
                <strong>{$MGLANG->T('acmeAddNewDomainsTitle')}</strong>
                <p style="margin: 5px 0 0 0;">
                    {$MGLANG->T('acmeAddNewDomainsDescription')}
                </p>
            </div>

            <table class="table table-bordered" style="margin-bottom: 10px;">
                <tbody>
                    <tr>
                        <td style="width: 25%; vertical-align: top;">{$MGLANG->T('acmeSingleDomainsLabel')} ({$singleDomainsLimit|escape})</td>
                        <td>
                            <textarea id="acmeSingleDomainsInput" rows="5" class="form-control"></textarea>
                        </td>
                    </tr>
                    {if $allowWildcard}
                        <tr>
                            <td style="width: 25%; vertical-align: top;">{$MGLANG->T('acmeWildcardDomainsLabel')} ({$wildcardDomainsLimit|escape})</td>
                            <td>
                                <textarea id="acmeWildcardDomainsInput" rows="5" class="form-control"></textarea>
                            </td>
                        </tr>
                    {/if}
                </tbody>
            </table>

            <p style="margin-top: 0;">{$MGLANG->T('acmeDomainsHint')}</p>

            <button type="button" class="btn btn-primary" id="acmeSubmitConfiguration">{$MGLANG->T('acmeSubmitConfiguration')}</button>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';

            $('#acmeSubmitConfiguration').on('click', function () {
                var singleDomainsInput = $('#acmeSingleDomainsInput').val();
                var wildcardDomainsInput = $('#acmeWildcardDomainsInput').length ? $('#acmeWildcardDomainsInput').val() : '';

                $.post(serviceUrl, {
                    'mg-action': 'createSubscription',
                    single_domains: singleDomainsInput,
                    wildcard_domains: wildcardDomainsInput
                }, function (ret) {
                    ret = ret.replace("<JSONRESPONSE#", "").replace("#ENDJSONRESPONSE>", "");
                    var payload = JSON.parse(ret);
                    if (payload.result === 'error') {
                        $('#MGAlerts').alerts('error', payload.error || '{$MGLANG->T('anErrorOccurred')}');
                        return;
                    }
                    var msg = (payload.data && payload.data.message) ? payload.data.message : '{$MGLANG->T('acmeConfigurationDone')}';
                    $('#MGAlerts').alerts('success', msg);
                    window.setTimeout(function () { window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}'; }, 1200);
                });
            });
        });
    </script>
{/if}
