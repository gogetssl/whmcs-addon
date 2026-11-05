<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div class="row" style="text-align: left;">
    <div class="col-sm-12">
        <h3>{$MGLANG->T('smimeConfigurationTitle')}</h3>
        <p class="text-muted">{$productName|escape}</p>

        <div id="smime-alerts"></div>

        <form id="smime-form" autocomplete="off" onsubmit="return false;">
            <div class="panel panel-default card mb-3">
                <div class="panel-heading card-header"><strong>{$MGLANG->T('smimeEmailsSection')}</strong></div>
                <div class="panel-body card-body">
                    <div class="form-group">
                        <label for="smime-email">{$MGLANG->T('smimeEmailLabel')} <span class="text-danger">*</span></label>
                        <input type="email" id="smime-email" name="smime_email" class="form-control" required
                               value="{$smimeClient.email|escape:'htmlall'}" placeholder="john.doe@example.com">
                        <span class="help-block form-text text-muted">{$MGLANG->T('smimeEmailHint')}</span>
                    </div>
                    <div class="form-group">
                        <label for="smime-additional-emails">{$MGLANG->T('smimeAdditionalEmailsLabel')}</label>
                        <textarea id="smime-additional-emails" name="smime_additional_emails" class="form-control" rows="3"
                                  placeholder="j.doe@example.com"></textarea>
                        <span class="help-block form-text text-muted">{$MGLANG->T('smimeAdditionalEmailsHint')}</span>
                    </div>
                    <div class="alert alert-info" style="margin-bottom: 0;">{$MGLANG->T('smimeValidationInfo')}</div>
                </div>
            </div>

            {if $smimeRequiresOrganization}
            <div class="panel panel-default card mb-3">
                <div class="panel-heading card-header"><strong>{$MGLANG->T('smimeOrganizationSection')}</strong></div>
                <div class="panel-body card-body">
                    <p class="text-muted">{$MGLANG->T('smimeOrganizationHint')}</p>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="smime-org-name">{$MGLANG->T('smimeOrg_name')} <span class="text-danger">*</span></label>
                            <input type="text" id="smime-org-name" name="org[name]" class="form-control" required value="{$smimeClient.companyname|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-division">{$MGLANG->T('smimeOrg_division')}</label>
                            <input type="text" id="smime-org-division" name="org[division]" class="form-control" value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-phone">{$MGLANG->T('smimeOrg_phone')} <span class="text-danger">*</span></label>
                            <input type="text" id="smime-org-phone" name="org[phone]" class="form-control" required value="{$smimeClient.phonenumber|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-country">{$MGLANG->T('smimeOrg_country')} <span class="text-danger">*</span></label>
                            {if $smimeCountries}
                                <select id="smime-org-country" name="org[country]" class="form-control" required>
                                    {foreach from=$smimeCountries key=code item=name}
                                        <option value="{$code|escape:'htmlall'}"{if $code == $smimeClient.country} selected{/if}>{$name|escape}</option>
                                    {/foreach}
                                </select>
                            {else}
                                <input type="text" id="smime-org-country" name="org[country]" class="form-control" required maxlength="2" value="{$smimeClient.country|escape:'htmlall'}">
                            {/if}
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-region">{$MGLANG->T('smimeOrg_region')}</label>
                            <input type="text" id="smime-org-region" name="org[region]" class="form-control" value="{$smimeClient.state|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-city">{$MGLANG->T('smimeOrg_city')} <span class="text-danger">*</span></label>
                            <input type="text" id="smime-org-city" name="org[city]" class="form-control" required value="{$smimeClient.city|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-postal">{$MGLANG->T('smimeOrg_postal_code')}</label>
                            <input type="text" id="smime-org-postal" name="org[postal_code]" class="form-control" value="{$smimeClient.postcode|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-address1">{$MGLANG->T('smimeOrg_address_line_1')} <span class="text-danger">*</span></label>
                            <input type="text" id="smime-org-address1" name="org[address_line_1]" class="form-control" required value="{$smimeClient.address1|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-address2">{$MGLANG->T('smimeOrg_address_line_2')}</label>
                            <input type="text" id="smime-org-address2" name="org[address_line_2]" class="form-control" value="{$smimeClient.address2|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-duns">{$MGLANG->T('smimeOrg_duns')}</label>
                            <input type="text" id="smime-org-duns" name="org[duns]" class="form-control" value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-org-lei">{$MGLANG->T('smimeOrg_lei')}</label>
                            <input type="text" id="smime-org-lei" name="org[lei]" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default card mb-3">
                <div class="panel-heading card-header"><strong>{$MGLANG->T('smimeAdministratorSection')}</strong></div>
                <div class="panel-body card-body">
                    <p class="text-muted">{$MGLANG->T('smimeAdministratorHint')}</p>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-first">{$MGLANG->T('smimeAdmin_first_name')} <span class="text-danger">*</span></label>
                            <input type="text" id="smime-admin-first" name="admin[first_name]" class="form-control" required value="{$smimeClient.firstname|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-last">{$MGLANG->T('smimeAdmin_last_name')} <span class="text-danger">*</span></label>
                            <input type="text" id="smime-admin-last" name="admin[last_name]" class="form-control" required value="{$smimeClient.lastname|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-title">{$MGLANG->T('smimeAdmin_title')}</label>
                            <input type="text" id="smime-admin-title" name="admin[title]" class="form-control" value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-email">{$MGLANG->T('smimeAdmin_email')} <span class="text-danger">*</span></label>
                            <input type="email" id="smime-admin-email" name="admin[email]" class="form-control" required value="{$smimeClient.email|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-phone">{$MGLANG->T('smimeAdmin_phone')} <span class="text-danger">*</span></label>
                            <input type="text" id="smime-admin-phone" name="admin[phone]" class="form-control" required value="{$smimeClient.phonenumber|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-country">{$MGLANG->T('smimeAdmin_country')} <span class="text-danger">*</span></label>
                            {if $smimeCountries}
                                <select id="smime-admin-country" name="admin[country]" class="form-control" required>
                                    {foreach from=$smimeCountries key=code item=name}
                                        <option value="{$code|escape:'htmlall'}"{if $code == $smimeClient.country} selected{/if}>{$name|escape}</option>
                                    {/foreach}
                                </select>
                            {else}
                                <input type="text" id="smime-admin-country" name="admin[country]" class="form-control" required maxlength="2" value="{$smimeClient.country|escape:'htmlall'}">
                            {/if}
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-region">{$MGLANG->T('smimeAdmin_region')}</label>
                            <input type="text" id="smime-admin-region" name="admin[region]" class="form-control" value="{$smimeClient.state|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-city">{$MGLANG->T('smimeAdmin_city')}</label>
                            <input type="text" id="smime-admin-city" name="admin[city]" class="form-control" value="{$smimeClient.city|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-postal">{$MGLANG->T('smimeAdmin_postal_code')}</label>
                            <input type="text" id="smime-admin-postal" name="admin[postal_code]" class="form-control" value="{$smimeClient.postcode|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="smime-admin-address1">{$MGLANG->T('smimeAdmin_address_line_1')}</label>
                            <input type="text" id="smime-admin-address1" name="admin[address_line_1]" class="form-control" value="{$smimeClient.address1|escape:'htmlall'}">
                        </div>
                    </div>
                </div>
            </div>
            {/if}

            <div class="panel panel-default card mb-3">
                <div class="panel-heading card-header">
                    <a href="#" id="smime-csr-toggle"><strong>{$MGLANG->T('smimeCsrSection')}</strong></a>
                    <span class="text-muted">({$MGLANG->T('smimeOptional')})</span>
                </div>
                <div class="panel-body card-body" id="smime-csr-body" style="display: none;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="smime-csr">{$MGLANG->T('smimeCsrLabel')}</label>
                        <textarea id="smime-csr" name="smime_csr" class="form-control" rows="6" style="font-family: monospace;"
                                  placeholder="-----BEGIN CERTIFICATE REQUEST-----"></textarea>
                        <span class="help-block form-text text-muted">{$MGLANG->T('smimeCsrHint')}</span>
                    </div>
                </div>
            </div>

            <button id="smime-submit" type="button" class="btn btn-primary">
                {$MGLANG->T('smimeSubmitOrder')}
            </button>
        </form>
    </div>
</div>

<script type="text/javascript">
{literal}
(function () {
    var form    = document.getElementById('smime-form');
    var alerts  = document.getElementById('smime-alerts');
    var btn     = document.getElementById('smime-submit');
    var btnText = btn.textContent;

    document.getElementById('smime-csr-toggle').addEventListener('click', function (e) {
        e.preventDefault();
        var body = document.getElementById('smime-csr-body');
        body.style.display = (body.style.display === 'none') ? '' : 'none';
    });

    function showError(message) {
        alerts.innerHTML = '<div class="alert alert-danger">' + message + '</div>';
        window.scrollTo(0, alerts.getBoundingClientRect().top + window.pageYOffset - 80);
    }

    btn.addEventListener('click', function () {
        alerts.innerHTML = '';

        if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
            return;
        }

        var data = $(form).serializeArray();
        data.push({name: 'mg-action', value: 'createSmime'});

        btn.disabled = true;
        btn.textContent = '...';

        $.post(
            'clientarea.php?action=productdetails&id={/literal}{$serviceid}{literal}&json=1&mg-model=home',
            $.param(data),
            function (ret) {
                ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
                try {
                    var payload = JSON.parse(ret);
                    if (payload.result === 'error') {
                        showError(payload.error || '{/literal}{$MGLANG->T('smimeErrorOccurred')}{literal}');
                        btn.disabled = false;
                        btn.textContent = btnText;
                    } else {
                        var msg = (payload.data && payload.data.message) ? payload.data.message : '{/literal}{$MGLANG->T('smimeOrderedSuccessfully')}{literal}';
                        alerts.innerHTML = '<div class="alert alert-success">' + msg + '</div>';
                        setTimeout(function () {
                            window.location.href = 'clientarea.php?action=productdetails&id={/literal}{$serviceid}{literal}';
                        }, 1500);
                    }
                } catch (e) {
                    showError('{/literal}{$MGLANG->T('smimeErrorOccurred')}{literal}');
                    btn.disabled = false;
                    btn.textContent = btnText;
                }
            }
        ).fail(function () {
            showError('{/literal}{$MGLANG->T('smimeErrorOccurred')}{literal}');
            btn.disabled = false;
            btn.textContent = btnText;
        });
    });
}());
{/literal}
</script>
{/if}
