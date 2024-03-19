<style>
    .sansTable {
        max-width: 100% !important;
        overflow-x:auto;
        border-collapse: collapse;
        border-style: hidden;
    }
    .sansTable th, .sansTable td {
    border: 1px solid #ddd;
    text-align: left;
    padding-left:8px;
    padding-right:2px;

    }
    #sansTd {
        padding: 0px !important;
    }
    .table {
        margin-bottom: 0px !important;
    }
    .modal .btn {
        margin: 2px !important;
    }
    #Action_Custom_Module_Button_Reissue_Certificate {
        margin: 2px !important;
    }
    #viewPrivateKey h4 {
        text-align: left !important;
    }
</style>
<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>
{if $allOk === true}
    <table id="mainTable" class="table table-bordered">
        <colgroup>
            <col style="width: 20%"/>
            <col style="width: 80%"/>
        </colgroup>
        <tbody>
            {if $activationStatus === 'active'}
                {if $configoption23}
                    <tr>
                        <td class="text-left">{$MGLANG->T('issued_ssl_message')}</td>
                        <td class="text-left">{$configoption23|nl2br}</td>
                    </tr>
                {/if}
            {/if}
            {if $activationStatus === 'processing' && $custom_guide} 
            <tr>
                <td class="text-left">{$MGLANG->T('custom_guide')}</td>
                <td class="text-left">{$custom_guide|nl2br}</td>
            </tr>
            {/if}
            {if $activationStatus === 'processing' && $configoption24}
            <tr>
                <td class="text-left">{$MGLANG->T('custom_guide')}</td>
                <td class="text-left">{$configoption24|nl2br}</td>
            </tr>
            {/if}
            <tr>
                <td class="text-left" >{$MGLANG->T('configurationStatus')}</td>
                <td class="text-left">{$MGLANG->T($configurationStatus)}{if $configurationStatus === 'Awaiting Configuration'} - <a href="{$configurationURL}">{$MGLANG->T('configureNow')}</a>{/if}</td>
            </tr>
            {if $activationStatus}
                <tr>
                    <td class="text-left">{$MGLANG->T('activationStatus')}</td>
                    <td class="text-left">
                        {if $activationStatus === 'active'}
                            {$MGLANG->T('activationStatusActive')}
                        {elseif $activationStatus === 'new_order'}
                            {$MGLANG->T('activationStatusNewOrder')}
                        {elseif $activationStatus === 'pending'}
                            {$MGLANG->T('activationStatusPending')}
                        {elseif $activationStatus === 'cancelled'}
                            {$MGLANG->T('activationStatusCancelled')}
                        {elseif $activationStatus === 'payment needed'}
                            {$MGLANG->T('activationStatusPaymentNeeded')}
                        {elseif $activationStatus === 'processing'}
                            {$MGLANG->T('activationStatusProcessing')}
                        {elseif $activationStatus === 'incomplete'}
                            {$MGLANG->T('activationStatusIncomplete')}
                        {elseif $activationStatus === 'rejected'}
                            {$MGLANG->T('activationStatusRejected')}
                        {else}
                            {$activationStatus|ucfirst}
                        {/if}
                    </td>
                </tr>
            {/if}
            {if $activationStatus === 'active'}
                <tr>
                    <td class="text-left">{$MGLANG->T('validFrom')}</td>
                    <td class="text-left">{$validFrom}</td>
                </tr>
                <tr>
                    <td class="text-left">{$MGLANG->T('validTill')}</td>
                    <td class="text-left">{$validTill}</td>
                </tr>
                {if $serviceBillingCycle != 'Annually'}
                    <tr>
                        <td class="text-left">{$MGLANG->T('subscriptionStarts')}</td>
                        <td class="text-left">{$subscriptionStarts}</td>
                    </tr>
                    <tr>
                        <td class="text-left">{$MGLANG->T('subscriptionEnds')}</td>
                        <td class="text-left">{$subscriptionEnds}</td>
                    </tr>
                    <tr>
                        <td class="text-left">{$MGLANG->T('nextReissue')}</td>
                        <td class="text-left"><strong>{$MGLANG->T('Reissue SSL within')} {$nextReissue} {$MGLANG->T('days')}</strong></td>
                    </tr>
                {else}
                    <tr>
                        <td class="text-left">{$MGLANG->T('nextRenewal')}</td>
                        <td class="text-left"><strong>{$MGLANG->T('Renew SSL within')} {$nextReissue} {$MGLANG->T('days')}</strong></td>
                    </tr>
                {/if}
            {/if}
            <!--{if $order_id}
                <tr>
                    <td class="text-left">{$MGLANG->T('Order ID')}</td>
                    <td class="text-left">{$order_id}</td>
                </tr>
            {/if}
            -->
            {if $domain}
                <tr>
                    <td class="text-left">{$MGLANG->T('domain')}</td>
                    <td class="text-left">{$domain}</td>
                </tr>
            {/if}
            {if $approver_email}
                <tr>
                    <td class="text-left">{$MGLANG->T('Approver email')}</td>
                    <td class="text-left">{$approver_email}</td>
                </tr>
            {/if}
            {if $partner_order_id}
                <tr>
                    <td class="text-left">{$MGLANG->T('Partner Order ID')}</td>
                    <td class="text-left">{$partner_order_id}</td>
                </tr>
            {/if}

            {if $approver_method}
                {if $dcv_method == 'http' || $dcv_method == 'https'}
                    <tr>
                        <td class="text-left">{$MGLANG->T('hashFile')}</td>
                        <td class="text-left" style="max-width:200px; word-wrap: break-word;">{$approver_method.$dcv_method.link}</td>
                    </tr>
                    <tr>
                        <td class="text-left">{$MGLANG->T('content')}</td>
                        <td class="text-left" style="max-width:200px; word-wrap: break-word;">{foreach $approver_method.$dcv_method.content as $content}{$content}<br />{/foreach}</td>
                    </tr>
                {else}
                    <tr id="validationData" >
                        {if $dcv_method == 'email'}
                            <td class="text-left">{$MGLANG->T('validationEmail')}</td>
                            <td class="text-left" >{$approver_method}</td>
                        {/if}
                        {if $dcv_method == 'dns'}
                            <td class="text-left ">{$MGLANG->T('dnsCnameRecord')}</td>
                            <td class="text-left" style="max-width:200px; word-wrap: break-word;">{$approver_method.dns.record|strtolower|replace:'cname':'CNAME'}</td>
                        {/if}
                    </tr>
                {/if}
            {/if}

            {if $sans}
                <tr>
                    <td class="text-left">{$MGLANG->T('sans')}</td>
                    <td id="sansTd" colspan="2" class="text-left">
                            <table class="sansTable table table-bordered" >
                            <tbody>
                            {foreach $sans as $san}
                                <tr>
                                    <td colspan="2" class="text-center">{$MGLANG->T({$san.san_name})}</td>
                                </tr>
                                {if $san.method == 'http' || $san.method == 'https'}
                                    {if $activationStatus === 'processing'}
                                        <tr>
                                            <td style="width: 15%" class="text-left">{$MGLANG->T('hashFile')}</td>
                                            <td class="text-left" style="max-width:200px; word-wrap: break-word;">{$san.san_validation.link}</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 15%" class="text-left">{$MGLANG->T('content')}</td>
                                            <td class="text-left" style="max-width:200px; word-wrap: break-word;">{foreach $san.san_validation.content as $content}{$content}<br />{/foreach}</td>
                                        </tr>
                                    {/if}
                               {else}
                                    {if $san.method == 'dns'}
                                        {if $activationStatus === 'processing'}
                                            <tr>
                                                <td style="width: 15%" class="text-left">{$MGLANG->T('dnsCnameRecord')}</td>
                                                <td class="text-left" style="max-width:200px; word-wrap: break-word;">{$san.san_validation|strtolower|replace:'cname':'CNAME'}</td>
                                            </tr>
                                        {/if}
                                    {else}
                                        {if $san.san_validation != ''}
                                            {if $activationStatus === 'processing'}
                                                <tr>
                                                    <td style="width: 15%" class="text-left">{$MGLANG->T('validationEmail')}</td>
                                                    <td class="text-left" style="word-wrap: break-word;">{$san.san_validation}</td>
                                                </tr>
                                            {/if}
                                        {/if}
                                    {/if}
                                {/if}
                            {/foreach}
                            </tbody>
                        </table>
                    </td>
                </tr>
               <!--<tr>
                    <td class="text-left">{$MGLANG->T('sans')}</td>
                    <td class="text-left">{$sans}</td>
                </tr>-->
            {/if}
            {if $crt}
                <tr>
                    <td class="text-left">{$MGLANG->T('crt')}</td>
                    <td class="text-left"><textarea onfocus="this.select()" rows="5" class="form-control">{$crt}</textarea></td>
                </tr>
            {/if}
            {if $ca}
                <tr>
                    <td class="text-left">{$MGLANG->T('ca_chain')}</td>
                    <td class="text-left"><textarea onfocus="this.select()" rows="5" class="form-control">{$ca}</textarea></td>
                </tr>
            {/if}
            {if $csr}
                <tr>
                    <td class="text-left">{$MGLANG->T('csr')}</td>
                    <td class="text-left"><textarea onfocus="this.select()" rows="5" class="form-control">{$csr}</textarea></td>
                </tr>
            {/if}
            <tr id="additionalActionsTr">
                <td class="text-left">{$MGLANG->T('Actions')}</td>
                <td id="additionalActionsTd" class="text-left">
                    {if $visible_renew_button}
                    {if $displayRenewButton}
                        <button type="button" id="btnRenew" class="btn btn-default" style="margin:2px">{$MGLANG->T('renew')}</button>
                    {/if}
                    {/if}
                    {if $dcv_method == 'email'}
                        <button type="button" id="resend-validation-email" class="btn btn-default" style="margin:2px">{$MGLANG->T('resendValidationEmail')}</button>
                    {/if}
                    {if $activationStatus == 'processing' || $activationStatus == 'cancelled'}
                    {if $btndownload}
                        <a href="{$btndownload}"><button type="button" class="btn btn-default" style="margin:2px">{$MGLANG->T('download')}</button></a>
                    {/if}
                    {if isset($approver_method.https) || isset($approver_method.http) || isset($approver_method.dns) || $san_revalidate}
                        <button type="button" id="btnRevalidateNew" class="btn btn-default" style="margin:2px">{$MGLANG->T('revalidate')}</button>
                    {/if}
                    {/if}

                    {if $btnInstallCrt}
                        <button type="button" id="installCertificate" class="btn btn-default" style="margin:2px">{$MGLANG->T('installCertificateBtn')}</button>
                    {/if}

                    {if $configurationStatus != 'Awaiting Configuration'}
                        {if $dcv_method == 'email' && !$sans}
                            <button type="button" id="btnChange_Approver_Email" class="btn btn-default" style="margin:2px">{$MGLANG->T('changeValidationEmail')}</button>
                        {/if}
                        {if $activationStatus == 'processing'}
                            <button type="button" id="btnRevalidate" class="btn btn-default" style="margin:2px">{$MGLANG->T('domainvalidationmethod')}</button>
                        {elseif $activationStatus == 'active'}
                            <a class="btn btn-default" role="button" href="" id="Action_Custom_Module_Button_Reissue_Certificate">{$MGLANG->T('reissueCertificate')}</a>
                            <button type="button" id="send-certificate-email" class="btn btn-default" style="margin:2px">{$MGLANG->T('sendCertificate')}</button>
                            {if $downloadca}<a href="{$downloadca}"><button type="button" id="download-ca" class="btn btn-default" style="margin:2px">{$MGLANG->T('downloadca')}</button></a>{/if}
                            {if $downloadcrt}<a href="{$downloadcrt}"><button type="button" id="download-crt" class="btn btn-default" style="margin:2px">{$MGLANG->T('downloadcrt')}</button></a>{/if}
                            {if $downloadcsr}<a href="{$downloadcsr}"><button type="button" id="download-csr" class="btn btn-default" style="margin:2px">{$MGLANG->T('downloadcsr')}</button></a>{/if}
                            {if $downloadpem}<a href="{$downloadpem}"><button type="button" id="download-ca" class="btn btn-default" style="margin:2px">{$MGLANG->T('downloadpem')}</button></a>{/if}
                        {/if}
                        <!--<button type="button" id="{if $dcv_method == 'email'}btnChange_Approver_Email{else}btnRevalidate{/if}" class="btn btn-default" style="margin:2px">{if $dcv_method == 'email'}{$MGLANG->T('changeValidationEmail')}{else}{$MGLANG->T('revalidate')}{/if}</button>-->
                        {if $privateKey}
                        <button type="button" id="getPrivateKey" class="btn btn-default" style="margin:2px">{$MGLANG->T('getPrivateKeyBtn')}</button>
                        {/if}
                        {if $activationStatus == 'unpaid'}
                            <button type="button" id="recheckDetails" class="btn btn-default" style="margin:2px">{$MGLANG->T('recheckCertificateDetails')}</button>
                        {/if}
                    {/if}
                </td>
            </tr>
        </tbody>
    </table>
    <script type="text/javascript">
        $(document).ready(function () {
            {if $activationStatus !== 'active'}
                //$('#Primary_Sidebar-Service_Details_Actions-Custom_Module_Button_Reissue_Certificate').remove();
            {else}
                $('#resend-validation-email').remove();
                $('#btnChange_Approver_Email').remove();
            {/if}
            var reissueUrl= $('#Primary_Sidebar-Service_Details_Actions-Custom_Module_Button_Reissue_Certificate').attr('href');
            $('#Action_Custom_Module_Button_Reissue_Certificate').prop('href', reissueUrl);
            $('#Primary_Sidebar-Service_Details_Actions-Custom_Module_Button_Reissue_Certificate').remove();
        });
    </script>

    <!--RENEW MODAL-->
    <div class="modal fade" id="modalRenew" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content panel panel-primary">
                <div class="modal-header panel-heading">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">{$MGLANG->T('Close')}</span>
                    </button>
                    <h4 class="modal-title">{$MGLANG->T('renewModalTitle')}</h4>
                </div>
                <div class="modal-body panel-body" id="modalRenewBody">

                    <div class="alert alert-success hidden" id="modalRenewSuccess">
                        <strong>Success!</strong> <span></span>
                    </div>
                    <div class="alert alert-danger hidden" id="modalRenewDanger">
                        <strong>Error!</strong> <span></span>
                    </div>
                    <form class="form-horizontal" role="form" id="modalRenewForm">
                            <div class="col-sm-12" style="padding: 25px;">
                                {$MGLANG->T('renewModalConfirmInformation')}
                            </div>
                    </form>
                </div>
                <div class="modal-footer panel-footer">
                    <button type="button" id="modalRenewSubmit" class="btn btn-primary">
                        {$MGLANG->T('Submit')}
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {$MGLANG->T('Close')}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {

            var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1',
                    renewBtn = $('#btnRenew'),
                    renewForm,
                    renewModal,
                    renewBody,
                    renewInput,
                    renewDangerAlert,
                    renewSuccessAlert,
                    renewSubmitBtn,
                    body = $('body');

            function assignModalElements(init) {
                renewModal = $('#modalRenew');
                renewBody = $('#modalRenewBody');

                if (init) {
                    renewBody.contents()
                    .filter(function(){
                        return this.nodeType === 8;
                    })
                    .replaceWith(function(){
                        return this.data;
                    });
                }

                if (!init) {
                    renewForm = $('#modalRenewForm');
                    renewSubmitBtn = $('#modalRenewSubmit');
                    //renewInput = $('.modalRenewInput');
                    renewBody = $('#modalRenewBody');
                    renewDangerAlert = $('#modalRenewDanger');
                    renewSuccessAlert = $('#modalRenewSuccess');
                }
            }

            function moveModalToBody() {

                body.append(renewModal.clone());
                assignModalElements(false);
                renewModal.remove();
            }

            function unbindOnClickForrenewBtn() {
                renewBtn.attr('onclick', '');
            }

            function bindModalFrorenewBtn() {
                renewBtn.off().on('click', function () {
                    renewModal.modal('show');
                    show(renewSubmitBtn);
                    show(renewForm);
                    hideAll();
                });
            }

            function bindSubmitBtn() {
                renewSubmitBtn.off().on('click', function () {
                    submitrenewModal();
                });
            }

            function showSuccessAlert(msg) {
                var reloadInfo = '{$MGLANG->T('redirectToInvoiceInformation')}'
                show(renewSuccessAlert);
                hide(renewDangerAlert);
                renewSuccessAlert.children('span').html(msg + ' ' + reloadInfo);
            }

            function showDangerAlert(msg) {
                hide(renewSuccessAlert);
                show(renewDangerAlert);
                renewDangerAlert.children('span').html(msg);
            }

            function addSpiner(element) {
                element.append('<i class="fa fa-spinner fa-spin"></i>');
            }

            function removeSpiner(element) {
                element.find('.fa-spinner').remove();
            }

            function show(element) {
                element.removeClass('hidden');
            }

            function hide(element) {
                element.addClass('hidden');
            }

            function enable(element) {
                element.removeAttr('disabled')
                element.removeClass('disabled');
            }

            function disable(element) {
                element.attr("disabled", true);
                element.addClass('disabled');
            }

            function hideAll() {
                hide(renewDangerAlert);
                hide(renewSuccessAlert);
            }

            function anErrorOccurred() {
                showDangerAlert('{$MGLANG->T('anErrorOccurred')}');
            }

            function isJsonString(str) {
                try {
                    JSON.parse(str);
                } catch (e) {
                    return false;
                }
                return true;
            }

            function resize(element) {
                element.css('height', "");
            }

            function submitrenewModal() {
                addSpiner(renewSubmitBtn);
                disable(renewSubmitBtn);

                var data = {
                    renewModal: 'yes',
                    serviceId: {$serviceid},
                    userID: {$userid},
                    'mg-action': 'renew'
                };
                $.ajax({
                    url: serviceUrl,
                    data: data,
                    json: 1,
                    success: function (ret) {
                        var data;
                        ret = ret.replace("<JSONRESPONSE#", "");
                        ret = ret.replace("#ENDJSONRESPONSE>", "");
                        if (!isJsonString(ret)) {
                            anErrorOccurred();
                            return;
                        }
                        data = JSON.parse(ret);
                        if (data.success === 1 || data.success === true) {
                            showSuccessAlert(data.data.msg);
                            hide(renewSubmitBtn);
                            resize(renewBody);
                            hide(renewForm);
                            window.setTimeout(function(){ window.location.replace('viewinvoice.php?id=' + data.data.invoiceID) }, 5000);
                        } else {
                            if(typeof data.data.invoiceID !== 'undefined')
                            {
                                var reloadInfo = '{$MGLANG->T('redirectToInvoiceInformation')}'
                                showDangerAlert(data.error + ' ' + reloadInfo);
                                window.setTimeout(function(){ window.location.replace('viewinvoice.php?id=' + data.data.invoiceID) }, 5000);
                            } else {
                                showDangerAlert(data.error);
                            }
                        }
                    },
                    error: function (jqXHR, errorText, errorThrown) {
                        anErrorOccurred();
                    },
                    complete: function () {
                        removeSpiner(renewSubmitBtn);
                        enable(renewSubmitBtn);
                    }
                });
            }

            assignModalElements(true);
            moveModalToBody();
            renewForm.trigger("reset");
            unbindOnClickForrenewBtn();
            bindModalFrorenewBtn();
            bindSubmitBtn();
        });
    </script>
    <!--END RENEW MODAL-->
    <div class="modal fade" id="modalRevalidate" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content panel panel-primary">
                <div class="modal-header panel-heading">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">{$MGLANG->T('Close')}</span>
                    </button>
                    <h4 class="modal-title">{$MGLANG->T('revalidateModalTitle')}</h4>
                </div>
                <div {if $sans && !$brand|in_array:$brandsWithOnlyEmailValidation}style="overflow-y: auto; height:{if $sans|@count == 1 }200{elseif $sans|@count == 2}275{else}350{/if}px;"{/if} class="modal-body panel-body" id="modalRevalidateBody">

                    <div class="alert alert-success hidden" id="modalRevalidateSuccess">
                        <strong>Success!</strong> <span></span>
                    </div>
                    <div class="alert alert-danger hidden" id="modalRevalidateDanger">
                        <strong>Error!</strong> <span></span>
                    </div>
                    <form class="form-horizontal" role="form" id="modalRevalidateForm">
                            <div class="col-sm-12">
                                <table class="table revalidateTable">
                                    <thead>
                                        <tr>
                                            <th>{$MGLANG->T('revalidateModalDomainLabel')}</th>
                                            <th style="width:35%;">{$MGLANG->T('revalidateModalMethodLabel')}</th>
                                            <th> {if 'email'|in_array:$disabledValidationMethods} {else}{$MGLANG->T('revalidateModalEmailLabel')}{/if}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{$domain}</td>
                                            <td>
                                                <div class="form-group">
                                                    <select style="width:70%;" type="text" name="newDcvMethod_0" class="form-control modalRevalidateInput" >
                                                        <option value="" selected>{$MGLANG->T('pleaseChooseOne')}</option>
                                                        {if !'email'|in_array:$disabledValidationMethods}
                                                            <option value="email">{$MGLANG->T('revalidateModalMethodEmail')}</option>
                                                        {/if}
                                                        {if !'http'|in_array:$disabledValidationMethods}
                                                            <option value="http">{$MGLANG->T('revalidateModalMethodHttp')}</option>
                                                        {/if}
                                                        {if !'https'|in_array:$disabledValidationMethods}
                                                            <option value="https">{$MGLANG->T('revalidateModalMethodHttps')}</option>
                                                        {/if}
                                                        {if !'dns'|in_array:$disabledValidationMethods}
                                                        <option value="dns">{$MGLANG->T('revalidateModalMethodDns')}</option>
                                                        {/if}
                                                        
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display:none;" class="form-group newApproverEmailFormGroup_0">
                                                    <select type="text" name="newApproverEmailInput_0"class="form-control newApproverEmailInputValidation"/>
                                                        <option id="loadingDomainEmails">{$MGLANG->T('loading')}</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        {*if $sans && !$brand|in_array:$brandsWithOnlyEmailValidation*}
                                            {$i = 1}
                                            {foreach $sans as $san}
                                                <tr>
                                                    {if $brand == 'digicert' || $brand == 'thawte' || $brand == 'rapidssl'}
                                                        <td>{$san.san_name}</td>
                                                        <td></td>
                                                        <td></td>
                                                    {else}
                                                        <td>{$san.san_name}</td>
                                                        <td>
                                                            <div class="form-group">
                                                                <select style="width:70%;" type="text" name="newDcvMethod_{$i}" class="form-control modalRevalidateInput">
                                                                    <option value="" selected>{$MGLANG->T('pleaseChooseOne')}</option>
                                                                    {if !'email'|in_array:$disabledValidationMethods}
                                                                        <option value="email">{$MGLANG->T('revalidateModalMethodEmail')}</option>
                                                                    {/if}
                                                                    {if !'http'|in_array:$disabledValidationMethods}
                                                                        <option value="http">{$MGLANG->T('revalidateModalMethodHttp')}</option>
                                                                    {/if}
                                                                    {if !'https'|in_array:$disabledValidationMethods}
                                                                        <option value="https">{$MGLANG->T('revalidateModalMethodHttps')}</option>
                                                                    {/if}
                                                                    {if !'dns'|in_array:$disabledValidationMethods}
                                                                        <option value="dns">{$MGLANG->T('revalidateModalMethodDns')}</option>
                                                                    {/if}
                                                                </select>
                                                            </div>
                                                        <td>
                                                            <div style="display:none;" class="form-group newApproverEmailFormGroup_{$i}">
                                                                <select type="text" name="newApproverEmailInput_{$i}" class="form-control newApproverEmailInputValidation"/>
                                                                    <option id="loadingDomainEmails">{$MGLANG->T('loading')}</option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                    {/if}
                                                </tr>
                                            {$i=$i+1}
                                            {/foreach}
                                        {*/if*}
                                    </tbody>
                                </table>
                            </div>
                    </form>
                </div>
                <div class="modal-footer panel-footer">
                    <button type="button" id="modalRevalidateSubmit" class="btn btn-primary">
                        {$MGLANG->T('Submit')}
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {$MGLANG->T('Close')}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            
            var wildcard = false;
            
            $('.revalidateTable tbody tr').each(function() {
                var string = $(this).find('td:first-child').text();
                var substring = '*.';
                if(string.indexOf(substring) !== -1)
                {
                    wildcard = true;
                }
            });
            
            var brand = '{$brand}';
            
            if(brand == 'digicert' || brand == 'geotrust' || brand == 'thawte' || brand == 'rapidssl')
            {
                if(wildcard == true)
                {
                    $('.revalidateTable select option[value="http"]').remove();
                    $('.revalidateTable select option[value="https"]').remove();
                }
            }
            else
            {
                $('.revalidateTable tbody tr').each(function() {
                    var string = $(this).find('td:first-child').text();
                    var substring = '*.';
                    if(string.indexOf(substring) !== -1)
                    {
                        $(this).find('option[value="http"]').remove();
                        $(this).find('option[value="https"]').remove();
                    }
                });
            }
            var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1',
                    revalidateBtn = $('#btnRevalidate'),
                    revalidateForm,
                    revalidateModal,
                    revalidateBody,
                    revalidateInput,
                    revalidateDangerAlert,
                    revalidateSuccessAlert,
                    revalidateSubmitBtn,
                    body = $('body');

            function assignModalElements(init) {
                revalidateModal = $('#modalRevalidate');
                revalidateBody = $('#modalRevalidateBody');

                if (init) {
                    revalidateBody.contents()
                    .filter(function(){
                        return this.nodeType === 8;
                    })
                    .replaceWith(function(){
                        return this.data;
                    });
                }

                if (!init) {
                    revalidateForm = $('#modalRevalidateForm');
                    revalidateSubmitBtn = $('#modalRevalidateSubmit');
                    revalidateInput = $('.modalRevalidateInput');
                    revalidateBody = $('#modalRevalidateBody');
                    revalidateEmail = $('.newApproverEmailInputValidation');
                    revalidateDangerAlert = $('#modalRevalidateDanger');
                    revalidateSuccessAlert = $('#modalRevalidateSuccess');
                }
            }

            function moveModalToBody() {

                body.append(revalidateModal.clone());
                assignModalElements(false);
                revalidateModal.remove();
            }

            function unbindOnClickForrevalidateBtn() {
                revalidateBtn.attr('onclick', '');
            }

            function bindModalFrorevalidateBtn() {
                revalidateBtn.off().on('click', function () {
                    revalidateModal.modal('show');
                    show(revalidateSubmitBtn);
                    show(revalidateForm);
                    hideAll();
                });
            }

            function bindSubmitBtn() {
                revalidateSubmitBtn.off().on('click', function () {
                    submitrevalidateModal();
                });
            }

            function showSuccessAlert(msg) {
                var reloadInfo = '{$MGLANG->T('reloadInformation')}'
                show(revalidateSuccessAlert);
                hide(revalidateDangerAlert);
                revalidateSuccessAlert.children('span').html(msg + ' ' + reloadInfo);
            }

            function showDangerAlert(msg) {
                hide(revalidateSuccessAlert);
                show(revalidateDangerAlert);
                revalidateDangerAlert.children('span').html(msg);
            }

            function addSpiner(element) {
                element.append('<i class="fa fa-spinner fa-spin"></i>');
            }

            function removeSpiner(element) {
                element.find('.fa-spinner').remove();
            }

            function show(element) {
                element.removeClass('hidden');
            }

            function hide(element) {
                element.addClass('hidden');
            }

            function enable(element) {
                element.removeAttr('disabled')
                element.removeClass('disabled');
            }

            function disable(element) {
                element.attr("disabled", true);
                element.addClass('disabled');
            }

            function hideAll() {
                hide(revalidateDangerAlert);
                hide(revalidateSuccessAlert);
            }

            function anErrorOccurred() {
                showDangerAlert('{$MGLANG->T('anErrorOccurred')}');
            }

            function isJsonString(str) {
                try {
                    JSON.parse(str);
                } catch (e) {
                    return false;
                }
                return true;
            }

            function resize(element) {
                element.css('height', "");
            }

            function submitrevalidateModal() {
                addSpiner(revalidateSubmitBtn);
                disable(revalidateSubmitBtn);
                var newMethods = {};
                var newdomains = {};
                
                $('.revalidateTable tbody tr').each(function(key,value){
                    var domaintemp = $(this).find('td:first-child').text();
                    domaintemp = domaintemp.replace("*", "___");
                    newdomains[domaintemp] = domaintemp;
                });
                
                
                revalidateInput.each(function(key,value){
                    var node = $('.revalidateTable>tbody').find('tr:eq('+key+')').find('td:eq(0)')[1];
                    if(typeof node !== 'undefined') {
                        domain = node.textContent;
                    }
                    domain = domain.replace("*", "___");
                    if(this.value === 'email') {
                        if(key === 0) {
                            newMethods[domain] = $('select[name="newApproverEmailInput_'+key+'"]')[2].value;
                        } else {
                            newMethods[domain] = $('select[name="newApproverEmailInput_'+key+'"]')[1].value;
                        }
                    } else {
                        if(this.value !== "") {
                            newMethods[domain] = this.value;
                        }
                    }
                    
                });
                if(jQuery.isEmptyObject(newMethods)) {
                    showDangerAlert('{$MGLANG->T('noValidationMethodSelected')}');
                    removeSpiner(revalidateSubmitBtn);
                    enable(revalidateSubmitBtn);
                    return;
                }
                var noEmailError = '';
                $.each(newMethods,function(key, value){
                    if(value === '{$MGLANG->T('pleaseChooseOne')}' || value === '{$MGLANG->T('loading')}') {
                        noEmailError = '{$MGLANG->T('noEmailSelectedForDomain')}' + key.replace("___", "*");
                        return true;
                    }
                });
                if(noEmailError !== '') {
                    showDangerAlert(noEmailError);
                    removeSpiner(revalidateSubmitBtn);
                    enable(revalidateSubmitBtn);
                    return;
                }
                var data = {
                    revalidateModal: 'yes',
                    newDcvMethods: newMethods,
                    newdomains: newdomains,
                    serviceId: {$serviceid},
                    userID: {$userid},
                    brand: '{$brand}',
                    'mg-action': 'revalidate'
                };
                $.ajax({
                    url: serviceUrl,
                    data: data,
                    json: 1,
                    success: function (ret) {
                        var data;
                        ret = ret.replace("<JSONRESPONSE#", "");
                        ret = ret.replace("#ENDJSONRESPONSE>", "");
                        if (!isJsonString(ret)) {
                            anErrorOccurred();
                            return;
                        }
                        data = JSON.parse(ret);
                        if (data.success === 1 || data.success === true) {
                            showSuccessAlert(data.data.msg);
                            revalidateInput.val('');
                            hide(revalidateSubmitBtn);
                            resize(revalidateBody);
                            hide(revalidateForm);
                            window.setTimeout(function(){ location.reload() }, 5000);
                        } else {
                            showDangerAlert(data.data.msg);
                        }
                    },
                    error: function (jqXHR, errorText, errorThrown) {
                        anErrorOccurred();
                    },
                    complete: function () {
                        removeSpiner(revalidateSubmitBtn);
                        enable(revalidateSubmitBtn);
                    }
                });
            }

            assignModalElements(true);
            moveModalToBody();
            revalidateForm.trigger("reset");
            unbindOnClickForrevalidateBtn();
            bindModalFrorevalidateBtn();
            bindSubmitBtn();
            revalidateInput.on("change", function() {
                    var fieldIndex = this.name.replace('newDcvMethod_', '');
                    var domain = $(this).closest('td').prev('td').text();
                    var selectedMethod = '';
                    selectedMethod = $(this).find(":selected").val();
                    if(selectedMethod === 'email') {
                        $(".newApproverEmailFormGroup_"+fieldIndex).css('display', 'block');
                        getDomainEmails(null, domain, fieldIndex);
                    } else {
                        $(".newApproverEmailFormGroup_"+fieldIndex).css('display', 'none');
                    }
            });
        });
    </script>
    <div class="modal fade" id="modalChangeApprovedEmail" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content panel panel-primary">
                <div class="modal-header panel-heading">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title">{$MGLANG->T('changeApproverEmailModalModalTitle')}</h4>
                </div>
                <div class="modal-body panel-body" id="modalChangeApprovedEmailBody">
                    <div class="alert alert-success hidden" id="modalChangeApprovedEmailSuccess">
                        <strong>Success!</strong> <span></span>
                    </div>
                    <div class="alert alert-danger hidden" id="modalChangeApprovedEmailDanger">
                        <strong>Error!</strong> <span></span>
                    </div>
                    <div class="form-group newApproverEmailFormGroup">
                        <label class="col-sm-3 control-label">{$MGLANG->T('newApproverEmailModalModalLabel')}</label>
                        <div class="col-sm-9">
                            <select type="text" name="newApproverEmailInput_0" id="modalChangeApprovedEmailInput" class="form-control"/>
                                <option id="loadingDomainEmails">{$MGLANG->T('loading')}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer panel-footer">
                    <button type="button" id="modalChangeApprovedEmailSubmit" class="btn btn-primary">
                        {$MGLANG->T('Submit')}
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {$MGLANG->T('Close')}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}',
                    changeEmailBtn = $('#btnChange_Approver_Email'),
                    changeEmailForm,
                    changeEmailModal,
                    changeEmailBody,
                    changeEmailInput,
                    changeEmailDangerAlert,
                    changeEmailSuccessAlert,
                    changeEmailSubmitBtn,
                    body = $('body');
            function assignModalElements(init) {
                changeEmailModal = $('#modalChangeApprovedEmail');
                changeEmailBody = $('#modalChangeApprovedEmailBody');

                if (init) {
                    changeEmailBody.contents()
                    .filter(function(){
                        return this.nodeType === 8;
                    })
                    .replaceWith(function(){
                        return this.data;
                    });
                }

                if (!init) {
                    changeEmailForm = $('.newApproverEmailFormGroup');
                    changeEmailSubmitBtn = $('#modalChangeApprovedEmailSubmit');
                    changeEmailInput = $('#modalChangeApprovedEmailInput');
                    changeEmailDangerAlert = $('#modalChangeApprovedEmailDanger');
                    changeEmailSuccessAlert = $('#modalChangeApprovedEmailSuccess');
                }
            }

            function moveModalToBody() {
                body.append(changeEmailModal.clone());
                assignModalElements(false);

                changeEmailModal.remove();
            }

            function unbindOnClickForChangeEmailBtn() {
                changeEmailBtn.attr('onclick', '');
            }

            function bindModalFroChangeEmailBtn() {
                changeEmailBtn.off().on('click', function () {
                    changeEmailModal.modal('show');
                    show(changeEmailSubmitBtn);
                    show(changeEmailForm);
                    hideAll();
                });
            }

            function bindSubmitBtn() {
                changeEmailSubmitBtn.off().on('click', function () {
                    submitChangeEmailModal();
                });
            }

            function showSuccessAlert(msg) {
                var reloadInfo = '{$MGLANG->T('reloadInformation')}'
                show(changeEmailSuccessAlert);
                hide(changeEmailDangerAlert);
                changeEmailSuccessAlert.children('span').html(msg + ' ' + reloadInfo);
            }

            function showDangerAlert(msg) {
                hide(changeEmailSuccessAlert);
                show(changeEmailDangerAlert);
                changeEmailDangerAlert.children('span').html(msg);
            }

            function addSpiner(element) {
                element.append('<i class="fa fa-spinner fa-spin"></i>');
            }

            function removeSpiner(element) {
                element.find('.fa-spinner').remove();
            }

            function show(element) {
                element.removeClass('hidden');
            }

            function hide(element) {
                element.addClass('hidden');
            }

            function enable(element) {
                element.removeAttr('disabled')
                element.removeClass('disabled');
            }

            function disable(element) {
                element.attr("disabled", true);
                element.addClass('disabled');
            }

            function hideAll() {
                hide(changeEmailDangerAlert);
                hide(changeEmailSuccessAlert);
            }

            function anErrorOccurred() {
                showDangerAlert('{$MGLANG->T('anErrorOccurred')}');
            }

            function isJsonString(str) {
                try {
                    JSON.parse(str);
                } catch (e) {
                    return false;
                }
                return true;
            }

            function submitChangeEmailModal() {
                addSpiner(changeEmailSubmitBtn);
                disable(changeEmailSubmitBtn);

                var data = {
                    newEmail: changeEmailInput.val(),
                    serviceId: {$serviceid},
                    userID: {$userid},
                    json: 1,
                    'mg-action': 'changeApproverEmail'
                };
                $.ajax({
                    type: "POST",
                    url: serviceUrl,
                    data: data,
                    success: function (ret) {
                        var data;
                        ret = ret.replace("<JSONRESPONSE#", "");
                        ret = ret.replace("#ENDJSONRESPONSE>", "");
                        if (!isJsonString(ret)) {
                            anErrorOccurred();
                            return;
                        }
                        data = JSON.parse(ret);
                        if (data.success) {
                            showSuccessAlert(data.data.msg);
                            changeEmailInput.val('');
                            hide(changeEmailSubmitBtn);
                            hide(changeEmailForm);
                            window.setTimeout(function(){ location.reload() }, 5000);
                        } else {
                            showDangerAlert(data.error);
                        }
                    },
                    error: function (jqXHR, errorText, errorThrown) {
                        anErrorOccurred();
                    },
                    complete: function () {
                        removeSpiner(changeEmailSubmitBtn);
                        enable(changeEmailSubmitBtn);
                    }
                });
            }

            assignModalElements(true);
            moveModalToBody();
            unbindOnClickForChangeEmailBtn();
            bindModalFroChangeEmailBtn();
            bindSubmitBtn();
        });
    </script>
{/if}
    <div class="modal fade" id="viewPrivateKey" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content panel panel-primary">
                <div class="modal-header panel-heading">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title">{$MGLANG->T('viewPrivateKeyModalTitle')}</h4>
                </div>
                <div class="modal-body panel-body" id="modalViewPrivateKey">
                     <div class="form-group">
                        <textarea id="privateKey" class="form-control"  rows="13" style="overflow:auto;resize:none"></textarea>
                     </div>
                </div>
                <div class="modal-footer panel-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {$MGLANG->T('Close')}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">

        {literal}

            function getDomainEmails(serviceid = null, domain, index){
                var brand = {/literal}'{$brand}'{literal};
                var serviceUrl = 'clientarea.php?action=productdetails&json=1&mg-action=getApprovalEmailsForDomain&brand=' + brand + '&domain=' + domain;

                serviceUrl += '&id=' + {/literal}'{$serviceid}'{literal};

                $.ajax({
                        type: "POST",
                        url: serviceUrl,
                        success: function (ret) {
                            var data;
                            $('select[name="newApproverEmailInput_'+index+'"]').empty();
                            ret = ret.replace("<JSONRESPONSE#", "");
                            ret = ret.replace("#ENDJSONRESPONSE>", "");

                            data = JSON.parse(ret);
                            if (data.success === 1) {
                                var  htmlOptions = [];
                                htmlOptions += '<option>'+{/literal}'{$MGLANG->T('pleaseChooseOne')}'{literal}+'</option>';
                                var domainEmails = data.data.domainEmails;
                                for (var i = 0; i < domainEmails.length; i++) {
                                     htmlOptions += '<option value="' + domainEmails[i] + '">' + domainEmails[i] + '</option>';
                                }

                                $('select[name="newApproverEmailInput_'+index+'"]').append(htmlOptions);
                            } else {
                                showDangerAlert(data.msg);
                            }
                        },
                        error: function (jqXHR, errorText, errorThrown) {
                            nErrorOccurred();
                        }
                    });
            }
            $(document).ready(function () {

                var serviceid = {/literal}'{$serviceid}'{literal};
                var domain =   {/literal}'{$domain}'{literal};
                jQuery('#btnChange_Approver_Email').on("click", function(){
                    getDomainEmails(serviceid, domain, 0);
                });
                var additionalActions = $('#additionalActionsTd').html().trim();
                if(additionalActions.length == 0) {
                    $('#additionalActionsTr').remove();
                }
                jQuery('#resend-validation-email').on("click",function(){
                    $('#resend-validation-email').append(' <i id="resendSpinner" class="fa fa-spinner fa-spin"></i>');
                    JSONParser.request('resendValidationEmail',{json: 1, id: serviceid}, function (data) {
                        if (data.success == true) {
                            $('#MGAlerts>div[data-prototype="success"]').show();
                            $('#MGAlerts>div[data-prototype="success"] strong').html(data.message);
                        } else if (data.success == false) {
                            $('#MGAlerts>div[data-prototype="error"]').show();
                            $('#MGAlerts>div[data-prototype="error"] strong').html(data.message);
                        }
                        $('#resend-validation-email').find('.fa-spinner').remove();
                    }, false);
                });
                jQuery('#send-certificate-email').on("click",function(){
                    $('#send-certificate-email').find('.fa-spinner').remove();
                    $('#send-certificate-email').append(' <i id="resendSpinner" class="fa fa-spinner fa-spin"></i>');
                    JSONParser.request('sendCertificateEmail',{json: 1, id: serviceid}, function (data) {
                        if (data.success == true) {
                            $('#MGAlerts>div[data-prototype="success"]').show();
                            $('#MGAlerts>div[data-prototype="success"] strong').html(data.message);
                        } else if (data.success == false) {
                            $('#MGAlerts>div[data-prototype="error"]').show();
                            $('#MGAlerts>div[data-prototype="error"] strong').html(data.message);
                        }
                        $('#send-certificate-email').find('.fa-spinner').remove();
                    }, false);
                });
                jQuery('#getPrivateKey').on("click",function(){

                    $('#getPrivateKey').append(' <i class="fa fa-spinner fa-spin"></i>');
                    JSONParser.request('getPrivateKey',{json: 1,id: serviceid}, function (data) {
                        if (data.success == true) {
                            $('#MGAlerts>div').css('display', 'none');
                            $('#getPrivateKey').find('.fa-spinner').remove();
                            $('#viewPrivateKey').modal('toggle');
                            $('#privateKey').text(data.privateKey);
                        } else if (data.success == false) {
                            $('#getPrivateKey').find('.fa-spinner').remove();
                            $('#MGAlerts>div[data-prototype="error"]').show();
                            $('#MGAlerts>div[data-prototype="error"] strong').html(data.message);
                        }
                    }, false);
                });

                jQuery('#installCertificate').on("click",function(){

                    $('#installCertificate').append(' <i class="fa fa-spinner fa-spin"></i>');
                    JSONParser.request('installCertificate',{json: 1,id: serviceid}, function (data) {
                        if (data.success == true) {
                            $('#MGAlerts>div').css('display', 'none');
                            $('#installCertificate').find('.fa-spinner').remove();
                            $('#MGAlerts>div[data-prototype="success"]').show();
                            $('#MGAlerts>div[data-prototype="success"] strong').html(data.message);
                        } else if (data.success == false) {
                            $('#installCertificate').find('.fa-spinner').remove();
                            $('#MGAlerts>div[data-prototype="error"]').show();
                            $('#MGAlerts>div[data-prototype="error"] strong').html(data.message);
                        }
                    }, false);
                });


                jQuery('#btnRevalidateNew').on("click",function(){

                    $('#btnRevalidateNew').append(' <i class="fa fa-spinner fa-spin"></i>');
                    JSONParser.request('revalidateNew',{json: 1,id: serviceid}, function (data) {
                        if (data.success == true) {
                            $('#MGAlerts>div').css('display', 'none');
                            $('#btnRevalidateNew').find('.fa-spinner').remove();
                            $('#MGAlerts>div[data-prototype="success"]').show();
                            $('#MGAlerts>div[data-prototype="success"] strong').html(data.message);
                        } else if (data.success == false) {
                            $('#btnRevalidateNew').find('.fa-spinner').remove();
                            $('#MGAlerts>div[data-prototype="error"]').show();
                            $('#MGAlerts>div[data-prototype="error"] strong').html(data.message);
                        }
                    }, false);
                });

                jQuery('#reissue-order').on("click",function(){
                    JSONParser.request('reIssueOrder',{json: 1}, function (data) {
                        if (data.success == true) {
                            $('#MGAlerts>div[data-prototype="success"]').show();
                            $('#MGAlerts>div[data-prototype="success"] strong').html(data.message);
                        } else if (data.success == false) {
                            $('#MGAlerts>div[data-prototype="error"]').show();
                            $('#MGAlerts>div[data-prototype="error"] strong').html(data.message);
                        }
                    }, false);
                });

                //for template simplicity modal header bug
                var color = $('#modalRevalidate').find('.panel-heading').css('background-color');
                $('#viewPrivateKey').find('.panel-heading').css('background-color', color);
            });
        {/literal}
    </script>

<div class="modal fade" id="modalRecheck" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-primary" style="width:900px;left:-25%;">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="ModuleSuspendLabel">Check Certificate Details</h4>
            </div>
            <div class="modal-body panel-body" id="modalRecheckBody">
                <div class="alert alert-success hidden" id="modalRecheckSuccessAlert">
                    <strong>Success!</strong> <span></span>
                </div>
                <div class="alert alert-danger hidden" id="modalRecheckDangerAlert">
                    <strong>Error!</strong> <span></span>
                </div>
                <div class="text-center hidden" id="modalRecheckLoading">
                    Loading...
                </div>
                <div id="modalRecheckDetails">
                    <table id="certificate_details" class="table" style="width:100%;text-align:center;">
                        <colgroup>
                            <col width="40%"/>
                            <col width="60%"/>
                        </colgroup>
                        <tr id="configuration_status">
                            <td class="text-left" >{$MGLANG->T('configurationStatus')}</td>
                            <td class="text-left"></td>
                        </tr>
                        <tr id="order_status">
                            <td class="text-left">{$MGLANG->T('activationStatus')}</td>
                            <td class="text-left"></td>
                        </tr>
                        <tr id="valid_from">
                            <td class="text-left">{$MGLANG->T('validFrom')}</td>
                            <td class="text-left"></td>
                        </tr>
                        <tr id="valid_till">
                            <td class="text-left">{$MGLANG->T('validTill')}</td>
                            <td class="text-left"></td>
                        </tr>
                        <tr id="domain">
                            <td class="text-left">{$MGLANG->T('domain')}</td>
                            <td class="text-left"></td>
                        </tr>
                        <tr id="partner_order_id">
                            <td class="text-left">{$MGLANG->T('Partner Order ID')}</td>
                            <td class="text-left"></td>
                        </tr>
                        <tr id="sans">
                            <td class="text-left">{$MGLANG->T('sans')}</td>
                            <td id="sansTd" colspan="2" class="text-left">
                                <table class="sansTable table table-bordered" >

                                </table>
                            </td>
                        </tr>
                        <tr id="crt">
                            <td class="text-left">{$MGLANG->T('crt')}</td>
                            <td class="text-left"><textarea onfocus="this.select()" rows="5" class="form-control"></textarea></td>
                        </tr>
                        <tr id="ca">
                            <td class="text-left">{$MGLANG->T('ca_chain')}</td>
                            <td class="text-left"><textarea onfocus="this.select()" rows="5" class="form-control"></textarea></td>
                        </tr>
                        <tr id="csr">
                            <td class="text-left">{$MGLANG->T('csr')}</td>
                            <td class="text-left"><textarea onfocus="this.select()" rows="5" class="form-control"></textarea></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="modal-footer panel-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    {literal}
    $(document).ready(function () {

        var serviceUrl = 'clientarea.php?action=productdetails&id='+ {/literal}'{$serviceid}'{literal} + '&mg-action=getCertificateDetails&json=1',
                recheckBtn = $('#recheckDetails');

        function showLoader()
        {
            show($('#modalRecheckLoading'));
        }

        function bindModalToRecheckCertificateBtn() {
            $('#recheckDetails').off().on('click', function () {
                $('#modalRecheck').modal('show');
                fetchCertificateDetails();
            });
        }

        function showSuccessAlert(msg) {
            show($('#modalRecheckSuccessAlert'));
            hide($('#modalRecheckDangerAlert'));
            $('#modalRecheckSuccessAlert').children('span').html(msg);
        }

        function showDangerAlert(msg) {
            hide($('#modalRecheckSuccessAlert'));
            show($('#modalRecheckDangerAlert'));
            $('#modalRecheckDangerAlert').children('span').html(msg);
        }

        function show(element) {
            element.removeClass('hidden');
        }

        function hide(element) {
            element.addClass('hidden');
        }

        function enable(element) {
            element.removeClass('disabled');
        }

        function anErrorOccurred() {
            showDangerAlert('An error occurred');
        }

        function isJsonString(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }

        function hideAll() {
            hide($('#modalRecheckDangerAlert'));
            hide($('#modalRecheckSuccessAlert'));
            hide($('#modalRecheckDetails'));
        }

        function removeData()
        {

            var keys = ["configuration_status", "ca", "crt", "csr", "order_status", "sans", "valid_from", "valid_till",
                "partner_order_id", "domain", "approver_method"];

            for(var i = 0; i < keys.length; i++)
            {
                if(["crt", "ca", "csr"].indexOf(keys[i]) !== -1)
                {
                    $("table#certificate_details tr#" + keys[i] + " td:nth-child(2) textarea").empty();
                }
                else if(keys[i] == "sans")
                {
                    $("table#certificate_details #sans #sansTd table").empty();
                }
                else if(keys[i] == "approver_method")
                {
                    $("table#certificate_details tr[id*='" + keys[i] + "']").remove();
                }
                else
                {
                    $("table#certificate_details tr #" + keys[i] + " td:nth-child(2)").empty();
                }
            }
        }

        function renderCertificates(data) {

            removeData();

            if (typeof data === 'undefined') {
                return;
            }

            var keys = ["configuration_status", "ca", "crt", "csr", "order_status", "sans", "valid_from", "valid_till",
                "partner_order_id", "domain", "approver_method"];

            for(var i = 0; i < keys.length; i++)
            {
                if(!data.hasOwnProperty(keys[i]) || !data[keys[i]])
                {
                    $("table#certificate_details tr#" + keys[i]).hide();
                    continue;
                }

                if(["crt", "ca", "csr"].indexOf(keys[i]) !== -1)
                {
                    $("table#certificate_details tr#" + keys[i] + " td:nth-child(2) textarea").text(data[keys[i]]);
                }
                else if(keys[i] == "sans")
                {
                    for(var sanname in data.sans)
                    {
                        var tr = $("<tr />");
                        tr.append($("<td />", {colspan: "2", class: "text-center", text: sanname}));

                        $('#sans #sansTd table').append(tr);


                        if((data.sans[sanname].method == "http") || (data.sans[sanname].method == "https"))
                        {
                            var tr = $("<tr />");
                            tr.append($("<td />", {width: "15%", class: "text-left", text: "Hash File"}));
                            tr.append($("<td />", {style: "max-width:200px;word-wrap: break-word;", class: "text-left", text: data.sans[sanname].san_validation.link}));

                            $('#sans #sansTd table').append(tr);

                            var tr = $("<tr />");
                            tr.append($("<td />", {width: "15%", class: "text-left", text: "Content"}));

                            var content = "";

                            for(var j = 0; j < (data.sans[sanname].san_validation.content).length; j++)
                            {
                                content += data.sans[sanname].san_validation.content[j] + "<br />";
                            }

                            tr.append($("<td />", {style: "max-width:200px;word-wrap: break-word;", class: "text-left", html: content}));

                            $('#sans #sansTd table').append(tr);
                        }
                        else if(data.sans[sanname].method == "dns")
                        {
                            var tr = $("<tr />");
                            tr.append($("<td />", {width: "15%", class: "text-left", text: "DNS CNAME Record"}));

                            var content = (data.sans[sanname].san_validation).toLowerCase();
                            content = content.replace("cname", "CNAME");

                            tr.append($("<td />", {style: "max-width:200px;word-wrap: break-word;", class: "text-left", text: content}));
                            $('#sans #sansTd table').append(tr);
                        }
                        else
                        {
                            if(data.sans[sanname].san_validation != "")
                            {
                                var tr = $("<tr />");
                                tr.append($("<td />", {width: "15%", class: "text-left", text: "Validation Email"}));
                                tr.append($("<td />", {style: "word-wrap: break-word;", class: "text-left", text: data.sans[sanname].san_validation}));
                                $('#sans #sansTd table').append(tr);
                            }
                        }
                    }
                }
                else if(keys[i] == "approver_method")
                {
                    var dcv_method = Object.keys(data.approver_method)[0];

                    if((dcv_method == "http") || (dcv_method == "https"))
                    {
                        var tr = $("<tr />", {id: "approver_method_link"});
                        tr.append($("<td />", {width: "15%", class: "text-left", text: "Hash File"}));
                        tr.append($("<td />", {style: "max-width:200px;word-wrap: break-word;", class: "text-left", text: data.approver_method[dcv_method].link}));
                        $('table#certificate_details tr#partner_order_id').after(tr);

                        var tr = $("<tr />", {id: "approver_method_content"});
                        tr.append($("<td />", {width: "15%", class: "text-left", text: "Content"}));

                        var content = "";

                        for(var j = 0; j < (data.approver_method[dcv_method].content).length; j++)
                        {
                            content += data.approver_method[dcv_method].content[j] + "<br />";
                        }

                        tr.append($("<td />", {style: "max-width:200px;word-wrap: break-word;", class: "text-left", html: content}));
                        $('table#certificate_details tr#partner_order_id').next().after(tr);
                    }
                    else if(dcv_method == "dns")
                    {
                        var tr = $("<tr />", {id: "approver_method_record"});
                        tr.append($("<td />", {width: "15%", class: "text-left", text: "DNS CNAME Record"}));

                        var content = (data.approver_method[dcv_method].record).toLowerCase();
                        content = content.replace("cname", "CNAME");

                        tr.append($("<td />", {style: "max-width:200px;word-wrap: break-word;", class: "text-left", text: content}));
                        $('table#certificate_details tr#partner_order_id').after(tr);
                    }
                    else
                    {
                        var tr = $("<tr />", {id: "approver_method_email"});
                        tr.append($("<td />", {width: "15%", class: "text-left", text: "Validation Email"}));
                        tr.append($("<td />", {style: "max-width:200px;word-wrap: break-word;", class: "text-left", text: data.approver_method}));
                        $('table#certificate_details tr#partner_order_id').after(tr);
                    }
                }
                else
                {
                    $("table#certificate_details tr#" + keys[i] + " td:nth-child(2)").text(data[keys[i]]);
                }
            }

            show($('#modalRecheckDetails'));
        }

        function fetchCertificateDetails() {

            hideAll();
            showLoader();

            var params = {
                serviceId: {/literal}'{$serviceid}'{literal},
                userID: {/literal}'{$userid}'{literal},
                json: 1
            };
             $.ajax({

                url: serviceUrl,
                data: params,
                success: function (ret) {
                    var data;
                    ret = ret.replace("<JSONRESPONSE#", "");
                    ret = ret.replace("#ENDJSONRESPONSE>", "");
                    if (!isJsonString(ret)) {
                        anErrorOccurred();
                        return;
                    }
                    data = JSON.parse(ret);
                    hide($('#modalRecheckLoading'));
                    if (data.success === 1) {
                        renderCertificates(data.data);
                    }
                    else {
                        showDangerAlert(data.msg);
                    }
                },
                error: function (jqXHR, errorText, errorThrown) {
                    anErrorOccurred();
                }
            });
        }

        bindModalToRecheckCertificateBtn();
    });
    {/literal}
</script>
