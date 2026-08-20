<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div class="row">
    <div class="col-sm-12">
        <h3>{$MGLANG->T('aisConfigurationTitle')}</h3>

        <div id="ais-alerts"></div>

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-group">
                    <label for="ais-domain">{$MGLANG->T('aisDomainLabel')}</label>
                    <input type="text"
                           id="ais-domain"
                           class="form-control"
                           placeholder="example.com"
                           value="{$serviceDomain|escape:'htmlall'}">
                    <span class="help-block">{$MGLANG->T('aisDomainHint')}</span>
                </div>

                <button id="ais-submit" class="btn btn-primary">
                    {$MGLANG->T('aisSubmitConfiguration')}
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
{literal}
document.getElementById('ais-submit').addEventListener('click', function () {
    var domain  = document.getElementById('ais-domain').value.trim();
    var alerts  = document.getElementById('ais-alerts');
    var btn     = this;

    alerts.innerHTML = '';

    if (!domain) {
        alerts.innerHTML = '<div class="alert alert-danger">{/literal}{$MGLANG->T('aisDomainRequired')}{literal}</div>';
        return;
    }

    btn.disabled = true;
    btn.textContent = '...';

    $.post(
        'clientarea.php?action=productdetails&id={/literal}{$serviceid}{literal}&json=1&mg-model=home',
        {'mg-action': 'createAis', domain: domain},
        function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var payload = JSON.parse(ret);
                if (payload.result === 'error') {
                    alerts.innerHTML = '<div class="alert alert-danger">' + (payload.error || 'An error occurred.') + '</div>';
                    btn.disabled = false;
                    btn.textContent = '{/literal}{$MGLANG->T('aisSubmitConfiguration')}{literal}';
                } else {
                    var msg = (payload.data && payload.data.message) ? payload.data.message : '{/literal}{$MGLANG->T('aisCreatedSuccessfully')}{literal}';
                    alerts.innerHTML = '<div class="alert alert-success">' + msg + '</div>';
                    setTimeout(function () {
                        window.location.href = 'clientarea.php?action=productdetails&id={/literal}{$serviceid}{literal}';
                    }, 1500);
                }
            } catch (e) {
                alerts.innerHTML = '<div class="alert alert-danger">An error occurred.</div>';
                btn.disabled = false;
                btn.textContent = '{/literal}{$MGLANG->T('aisSubmitConfiguration')}{literal}';
            }
        }
    );
});
{/literal}
</script>
{/if}
