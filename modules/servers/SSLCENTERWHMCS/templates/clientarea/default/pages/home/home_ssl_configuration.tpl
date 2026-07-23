<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<style>
#ssl-config-wrap { max-width: 700px; margin: 0 auto; text-align:left;}
#ssl-config-wrap h2 { font-size: 22px; font-weight: 400; text-align: center; margin-bottom: 20px; }
#ssl-config-wrap .ssl-install-toggle { text-align: center; margin-bottom: 25px; }
#ssl-config-wrap .ssl-install-toggle .btn { min-width: 140px; }
#ssl-config-wrap .ssl-section-title { font-size: 18px; font-weight: 400; margin: 0 0 5px; }
#ssl-config-wrap .ssl-section-sub { font-size: 13px; color: #c0392b; margin-bottom: 15px; }
#ssl-config-wrap .ssl-section-sub a { color: #c0392b; text-decoration: underline; }
#ssl-config-wrap .form-group label { font-weight: 700; margin-bottom: 4px; display: block; }
#ssl-config-wrap .form-group label.normal { font-weight: 400; }
#ssl-config-wrap .san-row { display: flex; align-items: center; margin-bottom: 6px; }
#ssl-config-wrap .san-row select { flex: 1; }
#ssl-config-wrap .san-row .san-btns { margin-left: 6px; white-space: nowrap; }
#ssl-config-wrap .san-row .san-btns .btn { border-radius: 50%; width: 28px; height: 28px; padding: 0; line-height: 26px; font-size: 18px; }
#ssl-config-wrap .san-note { font-style: italic; font-size: 12px; color: #555; margin: 4px 0 10px; }
#ssl-config-wrap .dcv-info { color: #c0392b; font-weight: 700; font-size: 13px; margin-top: 5px; }
#ssl-config-wrap .ssl-csr-section { margin-top: 25px; border-top: 1px solid #e0e0e0; padding-top: 20px; }
#ssl-config-wrap .ssl-csr-section h3 { font-size: 18px; font-weight: 400; margin-bottom: 4px; }
#ssl-config-wrap .ssl-csr-section .csr-sub { font-size: 13px; color: #555; margin-bottom: 15px; }
#ssl-config-wrap .ssl-contact-section { margin-top: 20px; border-top: 1px solid #e0e0e0; padding-top: 20px; }
#ssl-config-wrap .ssl-contact-section h3 { font-size: 18px; font-weight: 400; margin-bottom: 15px; }
#ssl-config-wrap .ssl-manual-csr-section { margin-top: 15px; }
#ssl-config-wrap select optgroup { font-weight: 700; font-style: normal; }
#ssl-config-wrap select optgroup option { font-weight: 400; padding-left: 12px; }
</style>

<div id="ssl-config-wrap">
    <h2>{$MGLANG->T('sslConfigGenerateCertTitle')} {$productName|escape}</h2>

    {* Install type toggle *}
    <div class="ssl-install-toggle">
        {if $hasCpanel}
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-success" id="btn-autoinstall">
                <span class="glyphicon glyphicon-ok"></span> {$MGLANG->T('sslConfigAutoinstall')}
            </button>
            <button type="button" class="btn btn-default" id="btn-manual">
                {$MGLANG->T('sslConfigManual')}
            </button>
        </div>
        {else}
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-default disabled" id="btn-autoinstall">
                {$MGLANG->T('sslConfigAutoinstall')}
            </button>
            <button type="button" class="btn btn-success" id="btn-manual">
                {$MGLANG->T('sslConfigManual')}
            </button>
        </div>
        {/if}
    </div>
    <input type="hidden" id="install_type" name="install_type" value="{if $hasCpanel}autoinstall{else}manual{/if}">

    {* ===== AUTOINSTALL SECTION ===== *}
    <div id="section-autoinstall" {if !$hasCpanel}style="display:none;"{/if}>
        <h3 class="ssl-section-title">{$MGLANG->T('sslConfigServerInfoTitle')}</h3>
        <p class="ssl-section-sub">
            {$MGLANG->T('sslConfigServerInfoText')}
        </p>

        <div class="form-group">
            <label>{$MGLANG->T('sslConfigDomainLabel')} <span class="text-danger">*</span></label>
            <select class="form-control" id="main_domain" name="main_domain">
                <option value="">{$MGLANG->T('sslConfigPleaseChoose')}</option>
                {foreach $domainGroups as $group}
                    <optgroup label="{$group.label|escape}">
                        {foreach $group.domains as $domain}
                            <option value="{$domain|escape}">{$domain|escape}</option>
                        {/foreach}
                    </optgroup>
                {/foreach}
                <option value="__manual__">{$MGLANG->T('sslConfigDomainNotListed')}</option>
            </select>
        </div>

        {if $isWildcard}
        <div class="form-group">
            <label class="normal">
                <input type="checkbox" name="wildcard_selected" id="wildcard_selected_autoinstall" value="1">
                {$MGLANG->T('sslConfigWildcardLabel')|default:'Wildcard certificate'} <span id="wildcard_domain_preview_autoinstall" style="color:#2980b9;font-weight:600;"></span>
            </label>
        </div>
        {/if}

        <div class="form-group">
            <label class="normal">
                <input type="checkbox" name="include_www" id="include_www" value="1">
                {$MGLANG->T('sslConfigIncludeWww')}
            </label>
        </div>

        {if $isMultiDomain && $sansLimit > 0}
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigSanSection')} ({$sansLimit|escape}): <span class="text-danger">*</span></label>
            <div id="san-rows-container"></div>
            <p class="san-note">{$MGLANG->T('sslConfigSanNote')}</p>
        </div>
        {/if}

        {if $wildcardSansLimit > 0}
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigSanWildcard')} (max {$wildcardSansLimit|escape})</label>
            <p class="san-note" style="margin-bottom:8px;">{$MGLANG->T('sslConfigSanWildcardAutoNote')}</p>
            <div id="wildcard-san-rows-container"></div>
        </div>
        {/if}

        <div class="form-group">
            <label>{$MGLANG->T('sslConfigValidationMethod')} <span class="text-danger">*</span></label>
            <select class="form-control" id="dcv_method_autoinstall" name="dcv_method_autoinstall">
                {foreach $dcvMethods as $method}
                    <option value="{$method|escape}" {if $method == 'http'}selected{/if}>
                        {if $method == 'email'}{$MGLANG->T('sslConfigValidationEmail')}
                        {elseif $method == 'http'}{$MGLANG->T('sslConfigValidationHttp')}
                        {elseif $method == 'https'}{$MGLANG->T('sslConfigValidationHttps')}
                        {elseif $method == 'dns'}{$MGLANG->T('sslConfigValidationDns')}
                        {else}{$method|escape}{/if}
                    </option>
                {/foreach}
            </select>
        </div>

        <div id="approver-email-autoinstall-section" style="display:none;">
            <div id="approver-email-autoinstall-domain-warning" style="display:none;" class="alert alert-warning">
                {$MGLANG->T('sslConfigEmailDcvNeedsDomain')}
            </div>
            <div id="approver-email-autoinstall-select-wrap" class="form-group" style="display:none;">
                <label>{$MGLANG->T('sslConfigDcvEmailLabel')} <span class="text-danger">*</span></label>
                <select class="form-control" id="approver_email_select" name="approver_email">
                    <option value="">{$MGLANG->T('sslConfigFirstSelectDomain')}</option>
                </select>
                <p class="dcv-info">{$MGLANG->T('sslConfigDcvEmailFallback')}</p>
            </div>
        </div>

        <div id="dns-fallback-email-section" style="display:none;">
            <div id="dns-fallback-domain-warning" style="display:none;" class="alert alert-info">
                {$MGLANG->T('sslConfigDnsFallbackNeedsDomain')|default:'Select a domain above to configure a DNS fallback email.'}
            </div>
            <div id="dns-fallback-select-wrap" class="form-group" style="display:none;">
                <label>{$MGLANG->T('sslConfigDnsFallbackLabel')|default:'Fallback email for DNS validation (optional)'}</label>
                <select class="form-control" id="dns_fallback_email_select" name="dns_fallback_email">
                    <option value="">{$MGLANG->T('sslConfigDnsFallbackAutoSelect')|default:'— Auto-select on switch —'}</option>
                </select>
                <p class="dcv-info">{$MGLANG->T('sslConfigDnsFallbackHint')|default:'If DNS validation fails, it will automatically switch to email using this address.'}</p>
            </div>
        </div>
    </div>

    {* ===== MANUAL SECTION ===== *}
    <div id="section-manual" {if $hasCpanel}style="display:none;"{/if}>
        <div class="ssl-manual-csr-section">

            <div class="form-group" id="manual-own-csr-group">
                <label>{$MGLANG->T('sslConfigOwnCsrLabel')|default:'Your CSR Code'} <span class="text-danger">*</span></label>
                <textarea class="form-control" id="manual_own_csr" rows="8"
                    placeholder="-----BEGIN CERTIFICATE REQUEST-----&#10;...&#10;-----END CERTIFICATE REQUEST-----"
                    style="font-family:monospace;font-size:12px;resize:vertical;"></textarea>
                <span class="help-block" style="font-size:12px;color:#888;">{$MGLANG->T('sslConfigOwnCsrHint')|default:'Paste your existing CSR. The domain will be extracted automatically from the CSR.'}</span>
            </div>
            <div class="form-group">
                <label class="normal" style="font-weight:normal;">
                    <input type="checkbox" id="manual_auto_generate_csr" style="margin-right:6px;">
                    {$MGLANG->T('sslConfigAutoGenerateCsr')|default:'Generate CSR automatically'}
                </label>
            </div>
            <div class="form-group" id="manual-domain-group" style="display:none;">
                <label>{$MGLANG->T('sslConfigCsrCommonName')|default:'Common Name (domain)'} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="manual_domain" name="manual_domain"
                    placeholder="{$productDomain|escape}" value="{$productDomain|escape}">
                <span class="help-block" style="font-size:12px;color:#888;">{$MGLANG->T('sslConfigCsrCommonNameHint')|default:'Enter the domain name for the certificate (e.g. example.com). A CSR will be generated automatically.'}</span>
            </div>

            {if $isWildcard}
            <div class="form-group">
                <label class="normal">
                    <input type="checkbox" name="wildcard_selected" id="wildcard_selected_manual" value="1">
                    {$MGLANG->T('sslConfigWildcardLabel')|default:'Wildcard certificate'} <span id="wildcard_domain_preview_manual" style="color:#2980b9;font-weight:600;"></span>
                </label>
            </div>
            {/if}

            {if $isMultiDomain}
            <div class="form-group">
                <label>{$MGLANG->T('sslConfigSanSection')}</label>
                {if $sansLimit > 0}
                <div style="margin-bottom:10px;">
                    <label style="font-weight:400;">{$MGLANG->T('sslConfigSanSingle')} (max {$sansLimit|escape})</label>
                    <textarea class="form-control" name="san_domains" id="san_domains_manual" rows="4"
                        placeholder="domain1.com&#10;domain2.com"></textarea>
                </div>
                {/if}
                {if $wildcardSansLimit > 0}
                <div>
                    <label style="font-weight:400;">{$MGLANG->T('sslConfigSanWildcard')} (max {$wildcardSansLimit|escape})</label>
                    <textarea class="form-control" name="wildcard_san" id="wildcard_san_manual" rows="4"
                        placeholder="*.domain1.com&#10;*.domain2.com"></textarea>
                </div>
                {/if}
            </div>

            {* DCV method for manual enrollment *}
            <div class="form-group" style="margin-top:15px;">
                <label>{$MGLANG->T('sslConfigValidationMethod')} <span class="text-danger">*</span></label>
                <select class="form-control" id="dcv_method_select" name="dcv_method_manual">
                    {foreach $dcvMethods as $method}
                        <option value="{$method|escape}" {if $method == 'http'}selected{/if}>
                            {if $method == 'email'}{$MGLANG->T('sslConfigValidationEmail')}
                            {elseif $method == 'http'}{$MGLANG->T('sslConfigValidationHttp')}
                            {elseif $method == 'https'}{$MGLANG->T('sslConfigValidationHttps')}
                            {elseif $method == 'dns'}{$MGLANG->T('sslConfigValidationDns')}
                            {else}{$method|escape}{/if}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div id="approver-email-manual-section" style="display:none;">
                <div id="approver-email-manual-cn-warning" style="display:none;" class="alert alert-warning">
                    {$MGLANG->T('sslConfigEmailDcvNeedsCn')|default:'Please enter the Common Name (domain) above before selecting email validation.'}
                </div>
                <div id="approver-email-manual-select-wrap" class="form-group" style="display:none;">
                    <label>{$MGLANG->T('sslConfigDcvEmailLabel')} <span class="text-danger">*</span></label>
                    <select class="form-control" id="approver_email_manual_select" name="approver_email_manual">
                        <option value="">{$MGLANG->T('sslConfigFirstSelectDomain')}</option>
                    </select>
                    <p class="dcv-info">{$MGLANG->T('sslConfigDcvEmailFallback')}</p>
                </div>
            </div>
            {else}
            <div class="form-group" style="margin-top:15px;">
                <label>{$MGLANG->T('sslConfigValidationMethod')} <span class="text-danger">*</span></label>
                <select class="form-control" id="dcv_method_select" name="dcv_method_manual">
                    {foreach $dcvMethods as $method}
                        <option value="{$method|escape}" {if $method == 'http'}selected{/if}>
                            {if $method == 'email'}{$MGLANG->T('sslConfigValidationEmail')}
                            {elseif $method == 'http'}{$MGLANG->T('sslConfigValidationHttp')}
                            {elseif $method == 'https'}{$MGLANG->T('sslConfigValidationHttps')}
                            {elseif $method == 'dns'}{$MGLANG->T('sslConfigValidationDns')}
                            {else}{$method|escape}{/if}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div id="approver-email-manual-section" style="display:none;">
                <div id="approver-email-manual-cn-warning" style="display:none;" class="alert alert-warning">
                    {$MGLANG->T('sslConfigEmailDcvNeedsCn')|default:'Please enter the Common Name (domain) above before selecting email validation.'}
                </div>
                <div id="approver-email-manual-select-wrap" class="form-group" style="display:none;">
                    <label>{$MGLANG->T('sslConfigDcvEmailLabel')} <span class="text-danger">*</span></label>
                    <select class="form-control" id="approver_email_manual_select" name="approver_email_manual">
                        <option value="">{$MGLANG->T('sslConfigFirstSelectDomain')}</option>
                    </select>
                    <p class="dcv-info">{$MGLANG->T('sslConfigDcvEmailFallback')}</p>
                </div>
            </div>
            {/if}
        </div>
    </div>

    {* ===== CSR DETAILS ===== *}
    <div class="ssl-csr-section" id="ssl-csr-section">
        <h3>{$MGLANG->T('sslConfigCsrSection')}</h3>
        <p class="csr-sub">{$MGLANG->T('sslConfigCsrSubtext')}</p>

        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrCountry')} <span class="text-danger">*</span></label>
            <select class="form-control" name="csr_country" id="csr_country">
                {foreach $countries as $code => $name}
                    <option value="{$code|escape}" {if $code == $csrDefaultCountry}selected{/if}>{$name|escape}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrState')} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="csr_state" value="{$csrDefaultState|escape}">
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrCity')} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="csr_city" value="{$csrDefaultCity|escape}">
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrOrg')} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="csr_org" value="{$csrDefaultOrg|escape}">
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrOrgUnit')}</label>
            <input type="text" class="form-control" name="csr_org_unit" value="">
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrEmail')}</label>
            <input type="email" class="form-control" name="csr_email" value="{$csrDefaultEmail|escape}">
        </div>
    </div>

    {* ===== ADMIN CONTACT ===== *}
    <div class="ssl-contact-section">
        <h3>{$MGLANG->T('sslConfigAdminContact')}</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigFirstname')} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="admin_firstname" value="{if $client}{$client->firstname|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigLastname')} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="admin_lastname" value="{if $client}{$client->lastname|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigEmail')} <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="admin_email" value="{if $client}{$client->email|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigPhone')} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="admin_phone" value="{if $client}{$client->phonenumber|escape}{/if}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigTitle')}</label>
                    <input type="text" class="form-control" name="admin_title" value="">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigOrganization')}</label>
                    <input type="text" class="form-control" name="admin_organization" value="{if $client}{$client->companyname|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigAddress')}</label>
                    <input type="text" class="form-control" name="admin_addressline1" value="{if $client}{$client->address1|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigCity')}</label>
                    <input type="text" class="form-control" name="admin_city" value="{if $client}{$client->city|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigRegion')}</label>
                    <input type="text" class="form-control" name="admin_region" value="{if $client}{$client->state|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigPostalcode')}</label>
                    <input type="text" class="form-control" name="admin_postalcode" value="{if $client}{$client->postcode|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigCountry')}</label>
                    <input type="text" class="form-control" name="admin_country" value="{if $client}{$client->country|escape}{/if}">
                </div>
            </div>
        </div>
    </div>

    {* ===== TECH CONTACT ===== *}
    <div class="ssl-contact-section">
        <h3>
            {$MGLANG->T('sslConfigTechContact')}
            <label style="font-size:14px; font-weight:400; margin-left:15px; vertical-align:middle;">
                <input type="checkbox" id="same_as_admin" {if $useAdminContact}checked{/if}>
                {$MGLANG->T('sslConfigSameAsAdmin')}
            </label>
        </h3>
        <div id="tech-contact-fields" {if $useAdminContact}style="display:none;"{/if}>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigFirstname')}</label>
                        <input type="text" class="form-control" name="tech_firstname" value="{$techFirstname|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigLastname')}</label>
                        <input type="text" class="form-control" name="tech_lastname" value="{$techLastname|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigEmail')}</label>
                        <input type="email" class="form-control" name="tech_email" value="{$techEmail|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigPhone')}</label>
                        <input type="text" class="form-control" name="tech_phone" value="{$techPhone|escape}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigTitle')}</label>
                        <input type="text" class="form-control" name="tech_title" value="{$techTitle|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigOrganization')}</label>
                        <input type="text" class="form-control" name="tech_organization" value="{$techOrganization|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigAddress')}</label>
                        <input type="text" class="form-control" name="tech_addressline1" value="{$techAddressline1|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigCity')}</label>
                        <input type="text" class="form-control" name="tech_city" value="{$techCity|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigRegion')}</label>
                        <input type="text" class="form-control" name="tech_region" value="{$techRegion|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigPostalcode')}</label>
                        <input type="text" class="form-control" name="tech_postalcode" value="{$techPostalcode|escape}">
                    </div>
                    <div class="form-group">
                        <label>{$MGLANG->T('sslConfigCountry')}</label>
                        <input type="text" class="form-control" name="tech_country" value="{$techCountry|escape}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:25px; padding-top:15px; border-top:1px solid #e0e0e0;">
        <button type="button" class="btn btn-primary btn-lg" id="btn-submit-ssl-config">
            {$MGLANG->T('sslConfigSubmit')}
        </button>
    </div>
</div>

{* ===== THANK YOU PANEL (shown after successful autoinstall submit) ===== *}
<div id="ssl-thankyou-wrap" style="display:none; max-width:700px; margin:0 auto;">
    <div class="panel panel-default">
        <div class="panel-heading" style="background:#f0f7ff;border-color:#b3d4f5;margin-bottom:20px;">
            <strong><span class="glyphicon glyphicon-ok-circle" style="margin-right:6px;color:#1565c0;"></span>{$MGLANG->T('autoinstallStatusTitle')|default:'AutoInstall SSL — Validation In Progress'}</strong>
        </div>
        <div class="panel-body" style="font-size:13px;color:#444;">

            {* 2.3.1 Single Domain DV *}
            {if !$isWildcard && $certTypeConfig == 'DV'}
            <p><strong>{$MGLANG->T('thankYou')|default:'Thank You'}</strong></p>
            <p>{$MGLANG->T('autoinstallSingleDvMsg')|default:'AutoInstallSSL is currently performing domain validation for your certificate. Once the CA validates your domain the certificate will be installed on your server. We will notify you once the certificate is installed. You can check the status on your order detail page.'}</p>
            {/if}

            {* 2.3.2 Wildcard Domain DV *}
            {if $isWildcard && $certTypeConfig == 'DV'}
            <p>{$MGLANG->T('autoinstallWildcardDvIntro')|default:'AutoInstallSSL is currently performing domain validation for your certificate.'}</p>
            <p>{$MGLANG->T('autoinstallWildcardDvMsg')|default:'If you chose email validation or if DNS validation is not available the CA will email the domain validation for approval to the following emails:'}
                <strong> <span id="ssl-ty-email"></span></strong>
            </p>
            <p>{$MGLANG->T('autoinstallOutroDomain')|default:'Once the CA validates your domain the certificate will be installed on your server.<br>We will notify you once the certificate is installed. You can check the status on your order detail page.'}</p>
            {/if}

            {* 2.3.3 Single Domain OV *}
            {if !$isWildcard && $certTypeConfig == 'OV'}
            <p>{$MGLANG->T('autoinstallOvIntro')|default:'AutoInstallSSL is currently performing domain validation for your certificate.'}</p>
            <p><strong>{$MGLANG->T('autoinstallOvNextStep')|default:'Your next step:'}</strong><br>
            {$MGLANG->T('autoinstallOvMsg')|default:'Perform Organization Validation, the CA will be emailing and calling the admin contact to confirm the organization is accurate and the contact is authorized.'}</p>
            <p>{$MGLANG->T('autoinstallOutroDomainOrg')|default:'Once the CA validates your domain &amp; organization the certificate will be installed on your server.<br>We will notify you once the certificate is installed. You can check the status on your order detail page.'}</p>
            {/if}

            {* 2.3.4 Wildcard Domain OV *}
            {if $isWildcard && $certTypeConfig == 'OV'}
            <p><strong>{$MGLANG->T('thankYou')|default:'Thank You'}</strong></p>
            <p>{$MGLANG->T('autoinstallWildcardOvIntro')|default:'AutoInstallSSL is currently performing domain validation for your certificate.'}</p>
            <p><strong>{$MGLANG->T('autoinstallWildcardOvNextSteps')|default:'Here are your next steps:'}</strong><br>
            {$MGLANG->T('autoinstallWildcardOvMsg')|default:'Perform Organization Validation, the CA will be emailing and calling the admin contact to confirm the organization is accurate and the contact is authorized. If you chose email validation or if DNS validation is not available the CA will email the domain validation for approval to the following emails:'}
                <strong> <span id="ssl-ty-email"></span></strong>
            </p>
            <p>{$MGLANG->T('autoinstallOutroDomainOrg')|default:'Once the CA validates your domain &amp; organization the certificate will be installed on your server.<br>We will notify you once the certificate is installed. You can check the status on your order detail page.'}</p>
            {/if}

        </div>
        <div class="panel-footer">
            <a href="clientarea.php?action=productdetails&id={$serviceid}" class="btn btn-primary">
                <span class="glyphicon glyphicon-arrow-right" style="margin-right:6px;"></span>
                {$MGLANG->T('goToCertificateDetails')|default:'View My Certificate'}
            </a>
        </div>
    </div>
</div>

{* Hidden template for SAN row optgroups (autoinstall) *}
<select id="san-domain-template" style="display:none;">
    <option value="">{$MGLANG->T('sslConfigPleaseChoose')}</option>
    {foreach $domainGroups as $group}
        <optgroup label="{$group.label|escape}">
            {foreach $group.domains as $domain}
                <option value="{$domain|escape}">{$domain|escape}</option>
            {/foreach}
        </optgroup>
    {/foreach}
    <option value="__manual__">{$MGLANG->T('sslConfigDomainNotListed')}</option>
</select>

{* Hidden template for wildcard SAN row optgroups (autoinstall) *}
<select id="wildcard-san-domain-template" style="display:none;">
    <option value="">{$MGLANG->T('sslConfigPleaseChoose')}</option>
    {foreach $domainGroups as $group}
        <optgroup label="{$group.label|escape}">
            {foreach $group.domains as $domain}
                <option value="{$domain|escape}">*.{$domain|escape}</option>
            {/foreach}
        </optgroup>
    {/foreach}
</select>

<script type="text/javascript">
(function ($) {
    var serviceUrl       = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';
    var sanLimit         = {$sansLimit|intval};
    var wildcardSanLimit = {$wildcardSansLimit|intval};
    var isAutoinstall    = '{if $hasCpanel}1{else}0{/if}' === '1';

    // ---- Install type toggle ----
    function switchToAutoinstall() {
        $('#install_type').val('autoinstall');
        $('#btn-autoinstall').addClass('btn-success').removeClass('btn-default');
        $('#btn-manual').addClass('btn-default').removeClass('btn-success');
        $('#section-autoinstall').show();
        $('#section-manual').hide();
        $('#ssl-csr-section').show();
    }
    function switchToManual() {
        $('#install_type').val('manual');
        $('#btn-manual').addClass('btn-success').removeClass('btn-default');
        $('#btn-autoinstall').addClass('btn-default').removeClass('btn-success');
        $('#section-autoinstall').hide();
        $('#section-manual').show();
        updateManualCsrMode();
    }

    if (isAutoinstall) {
        $('#btn-autoinstall').on('click', switchToAutoinstall);
        $('#btn-manual').on('click', switchToManual);
    }

    // ---- Own CSR / auto-generate toggle ----
    function updateManualCsrMode() {
        var autoGenerate = $('#manual_auto_generate_csr').is(':checked');
        if (autoGenerate) {
            $('#manual-own-csr-group').hide();
            $('#manual-domain-group').show();
            $('#ssl-csr-section').show();
        } else {
            $('#manual-own-csr-group').show();
            $('#manual-domain-group').hide();
            $('#ssl-csr-section').hide();
        }
        if ($('#dcv_method_select').val() === 'email') {
            refreshManualEmailSection();
        }
    }
    $('#manual_auto_generate_csr').on('change', updateManualCsrMode);

    if (!isAutoinstall) {
        updateManualCsrMode();
    }

    // ---- Approver email AJAX ----
    function loadApproverEmails(domain) {
        if (!domain) return;
        $('#approver-email-autoinstall-domain-warning').hide();
        $('#approver-email-autoinstall-select-wrap').show();
        $('#approver_email_select').html('<option value="">Loading...</option>');
        $.post(serviceUrl, {
            'mg-action': 'getApproverEmails',
            'domain':    domain
        }, function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var payload = JSON.parse(ret);
                var emails  = (payload.data && payload.data.emails) ? payload.data.emails : [];
                var $select = $('#approver_email_select');
                $select.empty();
                if (emails.length) {
                    $.each(emails, function (i, email) {
                        $select.append($('<option>').val(email).text(email));
                    });
                } else {
                    $select.append('<option value="">{$MGLANG->T('sslConfigFirstSelectDomain')}</option>');
                }
            } catch (e) {}
        });
    }

    function refreshAutoinstallEmailSection() {
        if ($('#dcv_method_autoinstall').val() !== 'email') return;
        var domain = $('#main_domain').val();
        $('#approver-email-autoinstall-section').show();
        if (!domain || domain === '__manual__' || domain === '') {
            $('#approver-email-autoinstall-domain-warning').show();
            $('#approver-email-autoinstall-select-wrap').hide();
        } else {
            loadApproverEmails(domain);
        }
    }

    function loadDnsFallbackEmails(domain) {
        if (!domain || domain === '__manual__') return;
        $('#dns-fallback-domain-warning').hide();
        $('#dns-fallback-select-wrap').show();
        $('#dns_fallback_email_select').html('<option value="">Loading...</option>');
        $.post(serviceUrl, {
            'mg-action': 'getApproverEmails',
            'domain':    domain
        }, function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var payload = JSON.parse(ret);
                var emails  = (payload.data && payload.data.emails) ? payload.data.emails : [];
                var $select = $('#dns_fallback_email_select');
                $select.empty().append('<option value="">{$MGLANG->T('sslConfigDnsFallbackAutoSelect')|default:'— Auto-select on switch —'}</option>');
                $.each(emails, function (i, email) {
                    $select.append($('<option>').val(email).text(email));
                });
            } catch (e) {}
        });
    }

    function refreshDnsFallbackSection() {
        if ($('#dcv_method_autoinstall').val() !== 'dns') {
            $('#dns-fallback-email-section').hide();
            return;
        }
        $('#dns-fallback-email-section').show();
        var domain = $('#main_domain').val();
        if (!domain || domain === '__manual__' || domain === '') {
            $('#dns-fallback-domain-warning').show();
            $('#dns-fallback-select-wrap').hide();
        } else {
            loadDnsFallbackEmails(domain);
        }
    }

    // "Domain not listed" → switch to manual
    $('#main_domain').on('change', function () {
        if ($(this).val() === '__manual__') {
            switchToManual();
        } else if ($('#dcv_method_autoinstall').val() === 'email') {
            refreshAutoinstallEmailSection();
        } else if ($('#dcv_method_autoinstall').val() === 'dns') {
            refreshDnsFallbackSection();
        }
    });

    // DCV method change in autoinstall section
    $('#dcv_method_autoinstall').on('change', function () {
        var method = $(this).val();
        if (method === 'email') {
            $('#dns-fallback-email-section').hide();
            refreshAutoinstallEmailSection();
        } else if (method === 'dns') {
            $('#approver-email-autoinstall-section').hide();
            refreshDnsFallbackSection();
        } else {
            $('#approver-email-autoinstall-section').hide();
            $('#dns-fallback-email-section').hide();
        }
    });

    // Initialize on page load
    if ($('#dcv_method_autoinstall').val() === 'email') {
        refreshAutoinstallEmailSection();
    } else if ($('#dcv_method_autoinstall').val() === 'dns') {
        refreshDnsFallbackSection();
    }

    // ---- SAN rows (autoinstall) ----
    var sanRowCount = 0;
    function buildSanOptions() {
        return $('#san-domain-template').html();
    }
    function addSanRow() {
        if (sanRowCount >= sanLimit) return;
        sanRowCount++;
        var idx = sanRowCount;
        var $row = $('<div class="san-row" data-idx="' + idx + '">');
        var $sel = $('<select class="form-control" name="san_domains[]">').html(buildSanOptions());
        var $btns = $('<div class="san-btns">');
        var $add = $('<button type="button" class="btn btn-success san-btn-add">+</button>');
        var $rem = $('<button type="button" class="btn btn-danger san-btn-rem">&minus;</button>');
        $add.on('click', function () { addSanRow(); });
        $rem.on('click', function () {
            $row.remove();
            sanRowCount--;
            updateSanAddButtons();
        });
        $sel.on('change', function () {
            if ($(this).val() === '__manual__') switchToManual();
        });
        $btns.append($add).append(' ').append($rem);
        $row.append($sel).append($btns);
        $('#san-rows-container').append($row);
        updateSanAddButtons();
    }
    function updateSanAddButtons() {
        if (sanRowCount >= sanLimit) {
            $('.san-btn-add').hide();
        } else {
            $('.san-btn-add').show();
        }
    }
    if (sanLimit > 0) {
        addSanRow();
    }

    // ---- Wildcard SAN rows (autoinstall) ----
    var wildcardSanRowCount = 0;
    function buildWildcardSanOptions() {
        return $('#wildcard-san-domain-template').html();
    }
    function addWildcardSanRow() {
        if (wildcardSanRowCount >= wildcardSanLimit) return;
        wildcardSanRowCount++;
        var idx  = wildcardSanRowCount;
        var $row = $('<div class="san-row" data-widx="' + idx + '">');
        var $sel = $('<select class="form-control" name="wildcard_san_domains[]">').html(buildWildcardSanOptions());
        var $btns = $('<div class="san-btns">');
        var $add  = $('<button type="button" class="btn btn-success san-btn-add-wc">+</button>');
        var $rem  = $('<button type="button" class="btn btn-danger san-btn-rem-wc">&minus;</button>');
        $add.on('click', function () { addWildcardSanRow(); });
        $rem.on('click', function () {
            $row.remove();
            wildcardSanRowCount--;
            updateWildcardSanAddButtons();
        });
        $btns.append($add).append(' ').append($rem);
        $row.append($sel).append($btns);
        $('#wildcard-san-rows-container').append($row);
        updateWildcardSanAddButtons();
    }
    function updateWildcardSanAddButtons() {
        if (wildcardSanRowCount >= wildcardSanLimit) {
            $('.san-btn-add-wc').hide();
        } else {
            $('.san-btn-add-wc').show();
        }
    }
    if (wildcardSanLimit > 0) {
        addWildcardSanRow();
    }

    // ---- Manual approver emails (email DCV) ----
    function loadManualApproverEmails(domain) {
        $('#approver-email-manual-cn-warning').hide();
        $('#approver-email-manual-select-wrap').show();
        $('#approver_email_manual_select').html('<option value="">Loading...</option>');
        $.post(serviceUrl, {
            'mg-action': 'getApproverEmails',
            'domain':    domain
        }, function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var payload = JSON.parse(ret);
                var emails  = (payload.data && payload.data.emails) ? payload.data.emails : [];
                var $select = $('#approver_email_manual_select');
                $select.empty();
                if (emails.length) {
                    $.each(emails, function (i, email) {
                        $select.append($('<option>').val(email).text(email));
                    });
                } else {
                    $select.append('<option value="">{$MGLANG->T('sslConfigFirstSelectDomain')}</option>');
                }
            } catch (e) {}
        });
    }

    function extractCnFromCsr(pem) {
        try {
            var b64     = pem.replace(/-----[^-]+-----/g, '').replace(/\s+/g, '');
            var binary  = atob(b64);
            var bytes   = new Uint8Array(binary.length);
            for (var i = 0; i < binary.length; i++) { bytes[i] = binary.charCodeAt(i); }
            // Search for CN OID: 55 04 03
            for (var i = 0; i < bytes.length - 5; i++) {
                if (bytes[i] === 0x55 && bytes[i+1] === 0x04 && bytes[i+2] === 0x03) {
                    var tag = bytes[i+3];
                    // PrintableString(0x13), UTF8String(0x0c), IA5String(0x16)
                    if (tag === 0x13 || tag === 0x0c || tag === 0x16) {
                        var len = bytes[i+4];
                        if (len > 0 && len < 128 && i + 5 + len <= bytes.length) {
                            var cn = '';
                            for (var j = 0; j < len; j++) { cn += String.fromCharCode(bytes[i+5+j]); }
                            return cn.toLowerCase();
                        }
                    }
                }
            }
        } catch (e) {}
        return null;
    }

    function refreshManualEmailSection() {
        if ($('#dcv_method_select').val() !== 'email') return;
        $('#approver-email-manual-section').show();
        var ownCsrMode = !$('#manual_auto_generate_csr').is(':checked');
        if (ownCsrMode) {
            var csrText = $('#manual_own_csr').val().trim();
            if (!csrText) {
                $('#approver-email-manual-cn-warning').text('{$MGLANG->T('sslConfigEmailDcvNeedsCsr')|default:'Please paste your CSR first before selecting email validation.'}').show();
                $('#approver-email-manual-select-wrap').hide();
            } else {
                var cn = extractCnFromCsr(csrText);
                if (!cn) {
                    $('#approver-email-manual-cn-warning').text('{$MGLANG->T('sslConfigEmailDcvCsrNoCn')|default:'Unable to extract domain from CSR. Please check your CSR code.'}').show();
                    $('#approver-email-manual-select-wrap').hide();
                } else {
                    $('#approver-email-manual-cn-warning').hide();
                    loadManualApproverEmails(cn);
                }
            }
        } else {
            var cn = $('#manual_domain').val().trim();
            if (!cn) {
                $('#approver-email-manual-cn-warning').text('{$MGLANG->T('sslConfigEmailDcvNeedsCn')|default:'Please enter the Common Name (domain) above before selecting email validation.'}').show();
                $('#approver-email-manual-select-wrap').hide();
            } else {
                $('#approver-email-manual-cn-warning').hide();
                loadManualApproverEmails(cn);
            }
        }
    }

    // ---- Manual DCV method toggle ----
    $('#dcv_method_select').on('change', function () {
        if ($(this).val() === 'email') {
            refreshManualEmailSection();
        } else {
            $('#approver-email-manual-section').hide();
        }
    });

    // Initialize on page load in case email is already selected
    if ($('#dcv_method_select').val() === 'email') {
        refreshManualEmailSection();
    }

    // When common name changes and email method is active, reload approver emails
    $('#manual_domain').on('change blur', function () {
        if ($('#dcv_method_select').val() === 'email') {
            refreshManualEmailSection();
        }
    });

    // When own CSR changes and email method is active, extract CN and reload approver emails
    $('#manual_own_csr').on('blur', function () {
        if ($('#dcv_method_select').val() === 'email') {
            refreshManualEmailSection();
        }
    });

    // ---- Same as admin toggle ----
    $('#same_as_admin').on('change', function () {
        $('#tech-contact-fields').toggle(!$(this).is(':checked'));
    });

    // ---- Submit ----
    $('#btn-submit-ssl-config').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true);

        var installType = $('#install_type').val();
        var dcvMethod, approverEmail;
        var ownCsrMode  = (installType !== 'autoinstall') && !$('#manual_auto_generate_csr').is(':checked');

        if (installType === 'autoinstall') {
            dcvMethod     = $('#dcv_method_autoinstall').val() || 'http';
            approverEmail = (dcvMethod === 'email') ? ($('#approver_email_select').val() || '') : '';
        } else {
            dcvMethod     = $('#dcv_method_select').val() || 'http';
            approverEmail = (dcvMethod === 'email') ? ($('#approver_email_manual_select').val() || '') : '';
        }

        var postData = {
            'mg-action':        'submitSslConfiguration',
            'install_type':     installType,
            'dcv_method':       dcvMethod,
            'approver_email':   approverEmail,
            'include_www':      $('#include_www').is(':checked') ? 1 : 0,
            'order_type':       'new',
            'csr_country':      $('select[name="csr_country"]').val(),
            'csr_state':        $('input[name="csr_state"]').val(),
            'csr_city':         $('input[name="csr_city"]').val(),
            'csr_org':          $('input[name="csr_org"]').val(),
            'csr_org_unit':     $('input[name="csr_org_unit"]').val(),
            'csr_email':        $('input[name="csr_email"]').val(),
            'admin_firstname':    $('input[name="admin_firstname"]').val(),
            'admin_lastname':     $('input[name="admin_lastname"]').val(),
            'admin_email':        $('input[name="admin_email"]').val(),
            'admin_phone':        $('input[name="admin_phone"]').val(),
            'admin_title':        $('input[name="admin_title"]').val(),
            'admin_organization': $('input[name="admin_organization"]').val(),
            'admin_addressline1': $('input[name="admin_addressline1"]').val(),
            'admin_city':         $('input[name="admin_city"]').val(),
            'admin_region':       $('input[name="admin_region"]').val(),
            'admin_postalcode':   $('input[name="admin_postalcode"]').val(),
            'admin_country':      $('input[name="admin_country"]').val()
        };

        if (installType === 'autoinstall') {
            var rawDomain = $('#main_domain').val();
            var isWildcardChecked = $('#wildcard_selected_autoinstall').is(':checked');
            if (isWildcardChecked && rawDomain && rawDomain !== '__manual__' && rawDomain.indexOf('*.') !== 0) {
                rawDomain = '*.' + rawDomain;
            }
            postData['main_domain'] = rawDomain;
            var sans = [];
            $('select[name="san_domains[]"]').each(function () {
                var v = $(this).val();
                if (v && v !== '__manual__') sans.push(v);
            });
            if (sans.length) postData['san_domains'] = sans;
            var wildcardSans = [];
            $('select[name="wildcard_san_domains[]"]').each(function () {
                var v = $(this).val();
                if (v) {
                    wildcardSans.push(v.indexOf('*.') === 0 ? v : '*.' + v);
                }
            });
            if (wildcardSans.length) postData['wildcard_san'] = wildcardSans.join("\n");
            if (dcvMethod === 'dns') {
                var fallbackEmail = $('#dns_fallback_email_select').val() || '';
                if (fallbackEmail) postData['dns_fallback_email'] = fallbackEmail;
            }
        } else {
            if (ownCsrMode) {
                var ownCsr = $('#manual_own_csr').val().trim();
                if (!ownCsr) {
                    $('#MGAlerts').alerts('error', '{$MGLANG->T('sslConfigOwnCsrRequired')|default:'Please paste your CSR code.'}');
                    btn.prop('disabled', false);
                    return;
                }
                postData['own_csr'] = ownCsr;
            } else {
                var manualDomain = $('#manual_domain').val().trim() || '{$productDomain|escape:'javascript'}';
                var isWildcardManual = $('#wildcard_selected_manual').is(':checked');
                if (isWildcardManual && manualDomain && manualDomain.indexOf('*.') !== 0) {
                    manualDomain = '*.' + manualDomain;
                }
                if (!manualDomain) {
                    $('#MGAlerts').alerts('error', '{$MGLANG->T('sslConfigDomainRequired')|default:'Domain is required.'}');
                    btn.prop('disabled', false);
                    return;
                }
                postData['manual_domain'] = manualDomain;
            }
            postData['san_domains']   = $('#san_domains_manual').val();
            postData['wildcard_san']  = $('#wildcard_san_manual').val();
        }

        if (!$('#same_as_admin').is(':checked')) {
            postData['tech_firstname']    = $('input[name="tech_firstname"]').val();
            postData['tech_lastname']     = $('input[name="tech_lastname"]').val();
            postData['tech_email']        = $('input[name="tech_email"]').val();
            postData['tech_phone']        = $('input[name="tech_phone"]').val();
            postData['tech_title']        = $('input[name="tech_title"]').val();
            postData['tech_organization'] = $('input[name="tech_organization"]').val();
            postData['tech_addressline1'] = $('input[name="tech_addressline1"]').val();
            postData['tech_city']         = $('input[name="tech_city"]').val();
            postData['tech_region']       = $('input[name="tech_region"]').val();
            postData['tech_postalcode']   = $('input[name="tech_postalcode"]').val();
            postData['tech_country']      = $('input[name="tech_country"]').val();
        }

        $.post(serviceUrl, postData, function (ret) {
            ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
            try {
                var payload = JSON.parse(ret);
                if (payload.success) {
                    if (installType === 'autoinstall') {
                        if (payload.approver_email) {
                            $('#ssl-ty-email').text(payload.approver_email);
                        }
                        $('#ssl-config-wrap').hide();
                        $('#ssl-thankyou-wrap').show();
                        $('html, body').animate({ scrollTop: 0 }, 300);
                    } else {
                        window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}';
                    }
                } else if (payload.error) {
                    $('#MGAlerts').alerts('error', payload.error);
                    btn.prop('disabled', false);
                }
            } catch (e) {
                btn.prop('disabled', false);
            }
        });
    });

    // Wildcard domain preview
    function updateWildcardPreview(checkboxId, selectOrInputId, previewId) {
        var checked = $('#' + checkboxId).is(':checked');
        var domainVal = $('#' + selectOrInputId).val();
        if (checked && domainVal && domainVal !== '__manual__' && domainVal !== '') {
            $('#' + previewId).text('(*.' + domainVal + ')');
        } else {
            $('#' + previewId).text('');
        }
    }

    $('#wildcard_selected_autoinstall').on('change', function () {
        updateWildcardPreview('wildcard_selected_autoinstall', 'main_domain', 'wildcard_domain_preview_autoinstall');
    });
    $('#main_domain').on('change', function () {
        updateWildcardPreview('wildcard_selected_autoinstall', 'main_domain', 'wildcard_domain_preview_autoinstall');
    });

    $('#wildcard_selected_manual').on('change', function () {
        updateWildcardPreview('wildcard_selected_manual', 'manual_domain', 'wildcard_domain_preview_manual');
    });
    $('#manual_domain').on('change blur', function () {
        updateWildcardPreview('wildcard_selected_manual', 'manual_domain', 'wildcard_domain_preview_manual');
    });

}(jQuery));
</script>
{/if}
