<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div class="row" style="text-align: left;">
    <div class="col-sm-12">
        <h3>{$MGLANG->T('csConfigurationTitle')}</h3>
        <p class="text-muted">{$productName|escape}</p>

        <div id="cs-alerts"></div>

        <div class="alert alert-info">{$MGLANG->T('csValidationInfo')}</div>

        <form id="cs-form" autocomplete="off" onsubmit="return false;">
            <div class="panel panel-default card mb-3">
                <div class="panel-heading card-header"><strong>{$MGLANG->T('csOrganizationSection')}</strong></div>
                <div class="panel-body card-body">
                    <p class="text-muted">{$MGLANG->T('csOrganizationHint')}</p>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="cs-org-name">{$MGLANG->T('csOrg_name')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-org-name" name="org[name]" class="form-control" required value="{$csClient.companyname|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-org-phone">{$MGLANG->T('csOrg_phone')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-org-phone" name="org[phone]" class="form-control" required value="{$csClient.phonenumber|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-org-address1">{$MGLANG->T('csOrg_address_line_1')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-org-address1" name="org[address_line_1]" class="form-control" required value="{$csClient.address1|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-org-address2">{$MGLANG->T('csOrg_address_line_2')}</label>
                            <input type="text" id="cs-org-address2" name="org[address_line_2]" class="form-control" value="{$csClient.address2|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-org-city">{$MGLANG->T('csOrg_city')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-org-city" name="org[city]" class="form-control" required value="{$csClient.city|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-org-region">{$MGLANG->T('csOrg_region')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-org-region" name="org[region]" class="form-control" required value="{$csClient.state|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-org-postal">{$MGLANG->T('csOrg_postal_code')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-org-postal" name="org[postal_code]" class="form-control" required value="{$csClient.postcode|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-org-country">{$MGLANG->T('csOrg_country')} <span class="text-danger">*</span></label>
                            {if $csCountries}
                                <select id="cs-org-country" name="org[country]" class="form-control" required>
                                    {foreach from=$csCountries key=code item=name}
                                        <option value="{$code|escape:'htmlall'}"{if $code == $csClient.country} selected{/if}>{$name|escape}</option>
                                    {/foreach}
                                </select>
                            {else}
                                <input type="text" id="cs-org-country" name="org[country]" class="form-control" required maxlength="2" value="{$csClient.country|escape:'htmlall'}">
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default card mb-3">
                <div class="panel-heading card-header"><strong>{$MGLANG->T('csAdministratorSection')}</strong></div>
                <div class="panel-body card-body">
                    <p class="text-muted">{$MGLANG->T('csAdministratorHint')}</p>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-first">{$MGLANG->T('csAdmin_first_name')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-first" name="admin[first_name]" class="form-control" required value="{$csClient.firstname|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-last">{$MGLANG->T('csAdmin_last_name')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-last" name="admin[last_name]" class="form-control" required value="{$csClient.lastname|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-title">{$MGLANG->T('csAdmin_title')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-title" name="admin[title]" class="form-control" required value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-email">{$MGLANG->T('csAdmin_email')} <span class="text-danger">*</span></label>
                            <input type="email" id="cs-admin-email" name="admin[email]" class="form-control" required value="{$csClient.email|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-phone">{$MGLANG->T('csAdmin_phone')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-phone" name="admin[phone]" class="form-control" required value="{$csClient.phonenumber|escape:'htmlall'}">
                        </div>
                        <div class="form-group col-sm-12">
                            <label style="font-weight: normal;">
                                <input type="checkbox" id="cs-admin-same" name="admin[same_address]" value="1" checked>
                                {$MGLANG->T('csAdminSameAddress')}
                            </label>
                        </div>
                    </div>
                    <div class="row" id="cs-admin-address" style="display: none;">
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-address1">{$MGLANG->T('csAdmin_address_line_1')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-address1" name="admin[address_line_1]" class="form-control" value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-address2">{$MGLANG->T('csAdmin_address_line_2')}</label>
                            <input type="text" id="cs-admin-address2" name="admin[address_line_2]" class="form-control" value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-city">{$MGLANG->T('csAdmin_city')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-city" name="admin[city]" class="form-control" value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-region">{$MGLANG->T('csAdmin_region')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-region" name="admin[region]" class="form-control" value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-postal">{$MGLANG->T('csAdmin_postal_code')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-postal" name="admin[postal_code]" class="form-control" value="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="cs-admin-country">{$MGLANG->T('csAdmin_country')} <span class="text-danger">*</span></label>
                            <input type="text" id="cs-admin-country" name="admin[country]" class="form-control" maxlength="2" placeholder="LV" value="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default card mb-3">
                <div class="panel-heading card-header"><strong>{$MGLANG->T('csDeliverySection')}</strong></div>
                <div class="panel-body card-body">
                    {if $csIsRenewal}
                        <p class="text-muted">{$MGLANG->T('csRenewalInfo')}</p>
                    {/if}
                    {if $csFixedMethod}
                        <input type="hidden" id="cs-method" name="cs_method" value="{$csFixedMethod|escape:'htmlall'}">
                        <p>
                            {$MGLANG->T('csDeliveryChosen')} <strong>{$csMethods[$csFixedMethod]|escape}{if $csFixedMethod == 'ship-token' && $csShippingLabel} ({$csShippingLabel|escape}){/if}</strong>
                        </p>
                    {else}
                        <div class="form-group">
                            <label for="cs-method">{$MGLANG->T('csDeliveryLabel')} <span class="text-danger">*</span></label>
                            <select id="cs-method" name="cs_method" class="form-control">
                                {foreach from=$csMethods key=method item=label}
                                    <option value="{$method|escape:'htmlall'}">{$label|escape}{if $method == 'ship-token' && $csShippingLabel} ({$csShippingLabel|escape}){/if}</option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}

                    <div class="cs-method-section" data-method="ship-token" style="display: none;">
                        <p class="text-muted">{$MGLANG->T('csShipTokenHint')}</p>
                        <label style="font-weight: normal;">
                            <input type="checkbox" id="cs-ship-same" name="ship[same_address]" value="1" checked>
                            {$MGLANG->T('csShipSameAddress')}
                        </label>
                        <div class="row" id="cs-ship-address" style="display: none;">
                            <div class="form-group col-sm-6">
                                <label for="cs-ship-first">{$MGLANG->T('csShip_first_name')} <span class="text-danger">*</span></label>
                                <input type="text" id="cs-ship-first" name="ship[first_name]" class="form-control" value="">
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="cs-ship-last">{$MGLANG->T('csShip_last_name')} <span class="text-danger">*</span></label>
                                <input type="text" id="cs-ship-last" name="ship[last_name]" class="form-control" value="">
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="cs-ship-address1">{$MGLANG->T('csShip_address_line_1')} <span class="text-danger">*</span></label>
                                <input type="text" id="cs-ship-address1" name="ship[address_line_1]" class="form-control" value="">
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="cs-ship-address2">{$MGLANG->T('csShip_address_line_2')}</label>
                                <input type="text" id="cs-ship-address2" name="ship[address_line_2]" class="form-control" value="">
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="cs-ship-city">{$MGLANG->T('csShip_city')} <span class="text-danger">*</span></label>
                                <input type="text" id="cs-ship-city" name="ship[city]" class="form-control" value="">
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="cs-ship-region">{$MGLANG->T('csShip_region')} <span class="text-danger">*</span></label>
                                <input type="text" id="cs-ship-region" name="ship[region]" class="form-control" value="">
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="cs-ship-postal">{$MGLANG->T('csShip_postal_code')} <span class="text-danger">*</span></label>
                                <input type="text" id="cs-ship-postal" name="ship[postal_code]" class="form-control" value="">
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="cs-ship-country">{$MGLANG->T('csShip_country')} <span class="text-danger">*</span></label>
                                <input type="text" id="cs-ship-country" name="ship[country]" class="form-control" maxlength="2" placeholder="LV" value="">
                            </div>
                        </div>
                    </div>

                    <div class="cs-method-section" data-method="existing-token" style="display: none;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="cs-model">{$MGLANG->T('csTokenModelLabel')} <span class="text-danger">*</span></label>
                            <select id="cs-model" name="cs_model" class="form-control">
                                {foreach from=$csTokenModels key=code item=label}
                                    <option value="{$code|escape:'htmlall'}">{$label|escape}</option>
                                {/foreach}
                            </select>
                            <span class="help-block form-text text-muted">{$MGLANG->T('csTokenModelHint')}</span>
                        </div>
                    </div>

                    <div class="cs-method-section" data-method="hsm" style="display: none;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="cs-csr">{$MGLANG->T('csCsrLabel')} <span class="text-danger">*</span></label>
                            <textarea id="cs-csr" name="cs_csr" class="form-control" rows="6" style="font-family: monospace;"
                                      placeholder="-----BEGIN CERTIFICATE REQUEST-----"></textarea>
                            <span class="help-block form-text text-muted">{$MGLANG->T('csCsrHint')}</span>
                        </div>
                    </div>
                </div>
            </div>

            <button id="cs-submit" type="button" class="btn btn-primary">
                {$MGLANG->T('csSubmitOrder')}
            </button>
        </form>
    </div>
</div>

<script type="text/javascript">
{literal}
(function () {
    var form    = document.getElementById('cs-form');
    var alerts  = document.getElementById('cs-alerts');
    var btn     = document.getElementById('cs-submit');
    var btnText = btn.textContent;
    var method  = document.getElementById('cs-method');

    var adminSame = document.getElementById('cs-admin-same');
    var shipSame  = document.getElementById('cs-ship-same');

    // only visible fields are required: a hidden required field blocks the submit without a message
    function setBlock(block, visible) {
        block.style.display = visible ? '' : 'none';
        $(block).find('input').each(function () {
            this.required = visible && !!this.parentNode.querySelector('.text-danger');
        });
    }

    function showMethod() {
        var current = method.value;
        $('.cs-method-section').each(function () {
            this.style.display = (this.getAttribute('data-method') === current) ? '' : 'none';
        });
        document.getElementById('cs-csr').required = (current === 'hsm');
        setBlock(document.getElementById('cs-ship-address'), current === 'ship-token' && !shipSame.checked);
    }

    adminSame.addEventListener('change', function () { setBlock(document.getElementById('cs-admin-address'), !adminSame.checked); });
    shipSame.addEventListener('change', showMethod);
    if (method.tagName === 'SELECT') {
        method.addEventListener('change', showMethod);
    }
    showMethod();

    function showAlert(type, message) {
        var box = document.createElement('div');
        box.className = 'alert alert-' + type;
        box.textContent = message;
        alerts.innerHTML = '';
        alerts.appendChild(box);
    }

    function showError(message) {
        showAlert('danger', message);
        window.scrollTo(0, alerts.getBoundingClientRect().top + window.pageYOffset - 80);
    }

    btn.addEventListener('click', function () {
        alerts.innerHTML = '';

        if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
            return;
        }

        var data = $(form).serializeArray();
        data.push({name: 'mg-action', value: 'createCodeSigning'});

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
                        showError(payload.error || '{/literal}{$MGLANG->T('csErrorOccurred')|escape:'javascript'}{literal}');
                        btn.disabled = false;
                        btn.textContent = btnText;
                    } else {
                        var msg = (payload.data && payload.data.message) ? payload.data.message : '{/literal}{$MGLANG->T('csOrderedSuccessfully')|escape:'javascript'}{literal}';
                        showAlert('success', msg);
                        setTimeout(function () {
                            window.location.href = 'clientarea.php?action=productdetails&id={/literal}{$serviceid}{literal}';
                        }, 1500);
                    }
                } catch (e) {
                    showError('{/literal}{$MGLANG->T('csErrorOccurred')|escape:'javascript'}{literal}');
                    btn.disabled = false;
                    btn.textContent = btnText;
                }
            }
        ).fail(function () {
            showError('{/literal}{$MGLANG->T('csErrorOccurred')|escape:'javascript'}{literal}');
            btn.disabled = false;
            btn.textContent = btnText;
        });
    });
}());
{/literal}
</script>
{/if}
