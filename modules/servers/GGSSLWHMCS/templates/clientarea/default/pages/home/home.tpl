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
            {/if}
            {if $domain}
                <tr>
                    <td class="text-left">{$MGLANG->T('domain')}</td>
                    <td class="text-left">{$domain}</td>
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
                        <td class="text-left" style="max-width:200px; word-wrap: break-word;">{$approver_method.$dcv_method.link|strtolower}</td>
                    </tr>
                    <tr>
                        <td class="text-left">{$MGLANG->T('content')}</td>
                        <td class="text-left" style="max-width:200px; word-wrap: break-word;">{foreach from=$approver_method.$dcv_method.content item=$content}{$content|strtolower}<br />{/foreach}</td>
                    </tr>                    
                {else}
                    <tr id="validationData" >
                        {if $dcv_method == 'email'}
                            <td class="text-left">{$MGLANG->T('validationEmail')}</td>
                            <td class="text-left" >{$approver_method}</td>
                        {/if}
                        {if $dcv_method == 'dns'}
                            <td class="text-left ">{$MGLANG->T('dnsCnameRecord')}</td>
                            <td class="text-left" style="max-width:200px; word-wrap: break-word;">{$approver_method.dns.record|strtolower}</td>
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
                            {foreach from=$sans item=san} 
                                <tr>
                                    <td colspan="2" class="text-center">{$MGLANG->T({$san.san_name})}</td>
                                </tr>
                                {if $san.method == 'http' || $san.method == 'https'}
                                    <tr>
                                        <td style="width: 15%" class="text-left">{$MGLANG->T('hashFile')}</td>
                                        <td class="text-left" style="max-width:200px; word-wrap: break-word;">{$san.san_validation.link|strtolower}</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 15%" class="text-left">{$MGLANG->T('content')}</td>
                                        <td class="text-left" style="max-width:200px; word-wrap: break-word;">{foreach from=$san.san_validation.content item=$content}{$content|strtolower}<br />{/foreach}</td>
                                    </tr> 
                                    {else}
                                        {if $san.method == 'dns'}
                                            <tr>
                                                <td style="width: 15%" class="text-left">{$MGLANG->T('dnsCnameRecord')}</td>
                                                <td class="text-left" style="max-width:200px; word-wrap: break-word;">{$san.san_validation|strtolower}</td>
                                            </tr> 
                                        {else}
                                            {if $san.san_validation != ''}
                                            <tr>
                                                <td style="width: 15%" class="text-left">{$MGLANG->T('validationEmail')}</td>
                                                <td class="text-left" style="word-wrap: break-word;">{$san.san_validation}</td>
                                            </tr> 
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
                    {if $serviceBillingCycle == 'One Time' && $displayRenewButton}
                        <button type="button" id="btnRenew" class="btn btn-default" style="margin:2px">{$MGLANG->T('renew')}</button>
                    {/if}
                    {if $dcv_method == 'email'}
                        <button type="button" id="resend-validation-email" class="btn btn-default" style="margin:2px">{$MGLANG->T('resendValidationEmail')}</button>
                    {/if}
                    {if $configurationStatus != 'Awaiting Configuration'}
                        {if $dcv_method == 'email' && !$sans}
                            <button type="button" id="btnChange_Approver_Email" class="btn btn-default" style="margin:2px">{$MGLANG->T('changeValidationEmail')}</button>
                        {/if}
                        {if $activationStatus !== 'active'}
                            <button type="button" id="btnRevalidate" class="btn btn-default" style="margin:2px">{$MGLANG->T('revalidate')}</button>
                        {else}
                            <a class="btn btn-default" role="button" href="" id="Action_Custom_Module_Button_Reissue_Certificate">{$MGLANG->T('reissueCertificate')}</a>
                        {/if}                        
                        <!--<button type="button" id="{if $dcv_method == 'email'}btnChange_Approver_Email{else}btnRevalidate{/if}" class="btn btn-default" style="margin:2px">{if $dcv_method == 'email'}{$MGLANG->T('changeValidationEmail')}{else}{$MGLANG->T('revalidate')}{/if}</button>-->
                        {if $privateKey}
                        <button type="button" id="getPrivateKey" class="btn btn-default" style="margin:2px">{$MGLANG->T('getPrivateKeyBtn')}</button>
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
                                            <th>{$MGLANG->T('revalidateModalEmailLabel')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{$domain}</td>
                                            <td>
                                                <div class="form-group">  
                                                    <select style="width:70%;" type="text" name="newDcvMethod_0" class="form-control modalRevalidateInput" >
                                                        <option value="" selected>{$MGLANG->T('pleaseChooseOne')}</option>
                                                        <option value="email">{$MGLANG->T('revalidateModalMethodEmail')}</option>
                                                        {if !$brand|in_array:$brandsWithOnlyEmailValidation}                                                            
                                                        <option value="http">{$MGLANG->T('revalidateModalMethodHttp')}</option>
                                                        <option value="https">{$MGLANG->T('revalidateModalMethodHttps')}</option>
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
                                        {if $sans && !$brand|in_array:$brandsWithOnlyEmailValidation}
                                            {$i = 1}
                                            {foreach from=$sans item=san}
                                                <tr>
                                                    <td>{$san.san_name}</td>
                                                    <td>
                                                        <div class="form-group">  
                                                            <select style="width:70%;" type="text" name="newDcvMethod_{$i}" class="form-control modalRevalidateInput">
                                                                <option value="" selected>{$MGLANG->T('pleaseChooseOne')}</option>
                                                                <option value="email">{$MGLANG->T('revalidateModalMethodEmail')}</option>
                                                                {if !$brand|in_array:$brandsWithOnlyEmailValidation}                                                            
                                                                <option value="http">{$MGLANG->T('revalidateModalMethodHttp')}</option>
                                                                <option value="https">{$MGLANG->T('revalidateModalMethodHttps')}</option>
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
                                                </tr>  
                                            {$i=$i+1}
                                            {/foreach}
                                        {/if}
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
                        noEmailError = '{$MGLANG->T('noEmailSelectedForDomain')}' + key;
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
                    serviceId: {$serviceid},
                    userID: {$userid},
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
