<span id="spanhideme"></span>
<script type="text/javascript">
    $(document).ready(function () {
        var hideMe = $('#spanhideme');
        function hideJsHtmlInjection() {
            hideMe.closest('tr').hide();
        }
        hideJsHtmlInjection();
        
        {if $version == '8'}
            var tokenf = $('#frm1 input[name="token"]').val();
        
            $('#profileContent').find('#frm1').after('<form id="loginAndRedirectForm" target="_blank" action="index.php?rp=/{$adminpath}/client/{$userid}/login" method="GET"><input type="hidden" name="token" value="'+tokenf+'" /><input type="hidden" name="goto" value="clientarea.php?action=productdetails&id=3"><input type="hidden" name="redirectToProductDetails" value="true"/><input type="hidden" name="username" value="{$email}"/><input type="hidden" name="serviceID" value="{$serviceid}"/></form>');
            $('#loginAndRedirectForm').attr('method', 'POST');
        {else}
            $('#profileContent').find('#frm1').after('<form id="loginAndRedirectForm" target="_blank" action="../dologin.php?language=" action="POST"><input type="hidden" name="redirectToProductDetails" value="true"/><input type="hidden" name="username" value="{$email}"/><input type="hidden" name="serviceID" value="{$serviceid}"/></form>');
        {/if}
        $('#btnManage_SSL').removeAttr('onclick');
        $('#btnManage_SSL').on('click', function(e) { 
            //$('#modcmdbtns').css('opacity', '0.2');
            //$('#modcmdworking').css('display', 'block').css('text-align', 'left').css('position', 'relative').css('left', '50px').css('bottom', '60px').css('z-index', '1');    
            $('#loginAndRedirectForm').submit();
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
                <h4 class="modal-title">Change Approver Email</h4>
            </div>
            <div class="modal-body panel-body" id="modalChangeApprovedEmailBody">
                <div class="alert alert-success hidden" id="modalChangeApprovedEmailSuccess">
                    <strong>Success!</strong> <span></span>
                </div>
                <div class="alert alert-danger hidden" id="modalChangeApprovedEmailDanger">
                    <strong>Error!</strong> <span></span>
                </div>
                <div class="form-group newApproverEmailFormGroup" id="modalChangeApprovedEmailForm">
                    <label class="col-sm-3 control-label">New Approver Email:</label>
                    <div class="col-sm-9">
                        <select type="text" name="newApproverEmailInput" id="modalChangeApprovedEmailInput" class="form-control"/>                            
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer panel-footer">
                <button type="button" id="modalChangeApprovedEmailSubmit" class="btn btn-primary">
                    Submit
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var serviceUrl = 'clientsservices.php?userid={$userid}&id={$serviceid}',
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
                changeEmailForm = $('#modalChangeApprovedEmailForm');
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
            show(changeEmailSuccessAlert);
            hide(changeEmailDangerAlert);
            changeEmailSuccessAlert.children('span').html(msg);
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
        function getDomainEmails(serviceUrl){            
            $.ajax({
                type: "POST",
                url: serviceUrl,
                data: {
                    action: 'getApprovalEmailsForDomain'
                },
                beforeSend: function(){
                    changeEmailInput.append('<option id="loadingDomainEmails">Loading...</option>');
                },
                success: function (ret) {
                    var data;    
                    changeEmailInput.empty();
                    ret = ret.replace("<JSONRESPONSE#", "");
                    ret = ret.replace("#ENDJSONRESPONSE>", "");
                            
                    data = JSON.parse(ret);
                    if (data.success === 1) {
                        var  htmlOptions = [];
                        htmlOptions += '<option>'+'{$MGLANG->T('Please choose one...')}'+'</option>';
                        var domainEmails = data.domainEmails;
                        for (var i = 0; i < domainEmails.length; i++) {  
                            htmlOptions += '<option value="' + domainEmails[i] + '">' + domainEmails[i] + '</option>';                                        
                        }
                        changeEmailInput.append(htmlOptions);
                    } else {
                        showDangerAlert(data.error);
                    }
                },
                error: function (jqXHR, errorText, errorThrown) {
                    nErrorOccurred();
                }
            });
        }
        function submitChangeEmailModal() {
            addSpiner(changeEmailSubmitBtn);
            disable(changeEmailSubmitBtn);

            var data = {
                changeEmailModal: 'yes',
                newEmail: changeEmailInput.val(),
                serviceId: {$serviceid},
                userID: {$userid}
            };
            $.ajax({
                type: "POST",
                url: serviceUrl,
                data: data,
                success: function (ret) {
                    var data;
                    if (!isJsonString(ret)) {
                        anErrorOccurred();
                        return;
                    }
                    data = JSON.parse(ret);
                    if (data.success === 1) {
                        showSuccessAlert(data.msg);
                        changeEmailInput.val('');
                        hide(changeEmailSubmitBtn);
                        hide(changeEmailForm);
                    } else {
                        showDangerAlert(data.msg);
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
        changeEmailBtn.on('click', function(){            
            getDomainEmails(serviceUrl);
        });     
    });
</script>

<div class="modal fade" id="modalReissue" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-primary">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title">Reissue Certificate</h4>
            </div>
            <div class="modal-body panel-body" id="modalReissueBody">
                <div class="alert alert-success hidden" id="modalReissueSuccessAlert">
                    <strong>Success!</strong> <span></span>
                </div>
                <div class="alert alert-danger hidden" id="modalReissueDangerAlert">
                    <strong>Error!</strong> <span></span>
                </div>

                <form class="form-horizontal" role="form" id="modalReissueForm">
                    <input type="hidden" name="formStep" id="modalReissueFormStepInput">
                    <div class="form-group mg-js-step-one">
                        <label class="col-sm-3 control-label">Web Server</label>
                        <div class="col-sm-9">
                            <select type="text" name="webServer" class="form-control" id="modalReissueWebServerInput">
                                <option>Loading...</option>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix mg-js-step-one"></div>
                    <br class="mg-js-step-one">
                    <div class="form-group mg-js-step-one">
                        <label  class="col-sm-3 control-label">CSR</label>
                        <div class="col-sm-9">
                            <textarea rows="3" class="form-control" name="csr" id="modalReissueCsrInput">-----BEGIN CERTIFICATE REQUEST-----

-----END CERTIFICATE REQUEST-----</textarea>
                        </div>
                    </div>

                    <div class="clearfix mg-js-step-one"></div>
                    <br class="mg-js-step-one">

                    {if $sansLimit}
                        <div class="form-group mg-js-step-one">
                            <label  class="col-sm-3 control-label">SAN Single Domains ({$sansLimit})</label>
                            <div class="col-sm-9">
                                <textarea rows="3" class="form-control" name="sanDomains" id="modalReissueSansInput"></textarea>
                            </div>
                        </div>
                    {/if}

                    <div class="clearfix mg-js-step-one"></div>
                    <br class="mg-js-step-one">

                    {if $sansLimitWildcard}
                        <div class="form-group mg-js-step-one">
                            <label  class="col-sm-3 control-label">SAN Wildcard Domains ({$sansLimitWildcard})</label>
                            <div class="col-sm-9">
                                <textarea rows="3" class="form-control" name="sanDomainsWildcard" id="modalReissueSansWildcardInput"></textarea>
                            </div>
                        </div>
                    {/if}

                    <div class="form-group mg-js-step-two">
                        <label  class="col-sm-3 control-label">Email Approvals</label>
                        <div class="col-sm-9">
                            <div id="modalReissueEmailApprovalsArea"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer panel-footer">
                <button type="button" id="modalReissueContinue" disabled class="btn btn-primary mg-js-step-one">
                    Continue
                </button>
                <button type="button" id="modalReissueSubmit" class="btn btn-primary mg-js-step-two">
                    Submit
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        var serviceUrl = 'clientsservices.php?userid={$userid}&id={$serviceid}',
                reisueBtn = $('#btnReissue_Certificate'),
                reisueModal,
                reisueBody,
                reissueCsrInput,
                reissueCsrDefault,
                reissueSansInput,
                reissueWebServerInput,
                reissueFormStepInput,
                reissueEmailApprovalsArea,
                reissueSubmitBtn,
                reissueSubmitContinue,
                reissueDangerAlert,
                reissueSuccessAlert,
                webServersLoaded = false,
                optionsHtml = '',
                body = $('body');

        function assignModalElements(init) {
            reisueModal = $('#modalReissue');
            reisueBody = $('#modalReissueBody');
            
            if(init) {
                reisueBody.contents()
                .filter(function(){
                    return this.nodeType === 8;
                })
                .replaceWith(function(){
                    return this.data;
                });
            }
            
            if (!init) {
                reissueSubmitBtn = $('#modalReissueSubmit');
                reissueSubmitContinue = $('#modalReissueContinue');
                reissueCsrInput = $('#modalReissueCsrInput');
                reissueCsrDefault = reissueCsrInput.val();
                reissueSansInput = $('#modalReissueSansInput');
                reissueWebServerInput = $('#modalReissueWebServerInput');
                reissueFormStepInput = $('#modalReissueFormStepInput');
                reissueDangerAlert = $('#modalReissueDangerAlert');
                reissueSuccessAlert = $('#modalReissueSuccessAlert');
                reissueEmailApprovalsArea = $('#modalReissueEmailApprovalsArea');
            }
        }

        function moveModalToBody() {
            body.append(reisueModal.clone());
            reisueModal.remove();
            assignModalElements(false);
        }

        function unbindOnClickForChangeEmailBtn() {
            reisueBtn.attr('onclick', '');
        }

        function bindModalFroChangeEmailBtn() {
            reisueBtn.off().on('click', function () {
                reisueModal.modal('show');
                fetchWebServers();
                switchToStepOne();
                hideAlerts();
            });
        }

        function bindSubmitBtn() {
            reissueSubmitBtn.off().on('click', function () {
                submitReissueModal();
            });
            reissueSubmitContinue.off().on('click', function () {
                submitReissueModal();
            });
        }

        function showSuccessAlert(msg) {
            show(reissueSuccessAlert);
            hide(reissueDangerAlert);
            reissueSuccessAlert.children('span').html(msg);
        }

        function showDangerAlert(msg) {
            hide(reissueSuccessAlert);
            show(reissueDangerAlert);
            reissueDangerAlert.children('span').html(msg);
        }

        function hideAlerts() {
            hide(reissueSuccessAlert);
            hide(reissueDangerAlert);
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

        function addSpiner(element) {
            element.append('<i class="fa fa-spinner fa-spin"></i>');
        }

        function removeSpiner(element) {
            element.find('.fa-spinner').remove();
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

        function submitReissueModal() {

            var data = {
                reissueModal: 'yes',
                serviceId: {$serviceid},
                userID: {$userid},
            }, formData = $('#modalReissueForm').serializeArray();

            if (reissueFormStepInput.val() === 'one') {
                data['action'] = 'getApprovals';
            } else {
                data['action'] = 'reissueCertificate';
            }

            data['webServer'] = $('select#modalReissueWebServerInput').val();
            data['csr'] = $('textarea#modalReissueCsrInput').val();
            data['sanDomains'] = $('textarea#modalReissueSansInput').val();
            data['sanDomainsWildcard'] = $('textarea#modalReissueSansWildcardInput').val();

            hideAlerts();
            disable(reissueSubmitContinue);
            addSpiner(reissueSubmitContinue);
            disable(reissueSubmitBtn);
            addSpiner(reissueSubmitBtn);

            data['approveremails'] = [];
            $('input[name^="approveremail"]:checked').each(function( index ) {
                data['approveremails'].push($(this).val());
            });

            $.ajax({
                type: "POST",
                url: serviceUrl,
                data: data,
                success: function (ret) {
                    var data;
                    if (!isJsonString(ret)) {
                        anErrorOccurred();
                        return;
                    }
                    data = JSON.parse(ret);
                    if (data.success === 1) {
                        if (reissueFormStepInput.val() === 'one') {
                            replaceRadioInputs(data.data);
                            switchToStepTwo();
                        } else {
                            showSuccessAlert(data.msg);
                            switchToStepThree();
                        }
                    } else {
                        showDangerAlert(data.msg);
                    }

                },
                error: function (jqXHR, errorText, errorThrown) {
                    anErrorOccurred();
                },
                complete: function () {
                    removeSpiner(reissueSubmitContinue);
                    enable(reissueSubmitContinue);
                    removeSpiner(reissueSubmitBtn);
                    enable(reissueSubmitBtn);
                }
            });
        }

        function renderWebServers(list) {
            optionsHtml = optionsHtml + '<option value="0">'+'{$MGLANG->T('Please choose one...')}'+'</option>';
            for (var i = 0; i < list.length; i++) {
                optionsHtml = optionsHtml + '<option value="' + list[i].id + '">' + list[i].software + '</option>';
            }
            reissueWebServerInput.html(optionsHtml);
            enable(reissueSubmitContinue);
        }

        function fetchWebServers() {

            if (webServersLoaded) {
                return;
            }

            var data = {
                reissueModal: 'yes',
                serviceId: {$serviceid},
                userID: {$userid},
                action: 'webServers'
            };
            $.ajax({
                type: "POST",
                url: serviceUrl,
                data: data,
                success: function (ret) {
                    var data;
                    if (!isJsonString(ret)) {
                        anErrorOccurred();
                        return;
                    }
                    data = JSON.parse(ret);
                    if (data.success === 1) {
                        renderWebServers(data.data);
                        webServersLoaded = true;
                    } else {
                        showDangerAlert(data.msg);
                    }
                },
                error: function (jqXHR, errorText, errorThrown) {
                    anErrorOccurred();
                }
            });
        }

        assignModalElements(true);
        moveModalToBody();
        unbindOnClickForChangeEmailBtn();
        bindModalFroChangeEmailBtn();
        bindSubmitBtn();

        function getRadioInputHtml(name, value, checked) {
            if (checked) {
                var ck = ' checked';
            } else {
                var ck = '';
            }
            return '<div class="radio"><label><input type="radio" name="' + name + '" value="' + value + '"' + ck + '>' + value + '</label></div>';
        }
        function getRowHtml(title, body) {
            return '<div class="col-sm-12 margin-top-10" style="padding-top: 10px;"><p>' + title + '</p><div class="form-group">' + body + '</div>';
        }
        function getNameForRadioInput(x, domain) {
            if (x === 0) {
                return 'approveremail';
            }
            return 'approveremails[' + domain + ']';
        }
        function replaceRadioInputs(sanEmails) {
            var fullHtml = '',
                    partHtml = '',
                    x = 0;

            reissueEmailApprovalsArea.parent().find('*').not(reissueEmailApprovalsArea).remove();

            $.each(sanEmails, function (domain, emails) {
                for (var i = 0; i < emails.length; i++) {
                    partHtml = partHtml + getRadioInputHtml(getNameForRadioInput(x, domain), emails[i], i === 0);
                }
                fullHtml = fullHtml + getRowHtml(domain, partHtml);
                reissueEmailApprovalsArea.before(fullHtml);
                partHtml = fullHtml = '';
                x++;
            });
        }

        function switchToStepOne() {
            $('.mg-js-step-one').show();
            $('.mg-js-step-two').hide();
            reissueFormStepInput.val('one');
        }

        function switchToStepTwo() {
            $('.mg-js-step-one').hide();
            $('.mg-js-step-two').show();
            reissueFormStepInput.val('two');
        }

        function switchToStepThree() {
            $('.mg-js-step-one').hide();
            $('.mg-js-step-two').hide();
            reissueCsrInput.val(reissueCsrDefault);
            reissueSansInput.val('');
            reissueFormStepInput.val('three');
            reissueWebServerInput.val('0');
        }

    {literal}var sanEmails = JSON.parse('{\"friz.pl\":[\"admin@friz.pl\",\"administrator@friz.pl\"],\"kot.pl\":[\"admin@kot.pl\",\"administrator@kot.pl\"]}');{/literal}
            replaceRadioInputs(sanEmails);

        });
</script>
 
<div class="modal fade" id="modalView" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-primary">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="ModuleSuspendLabel">View Certificate</h4>
            </div>
            <div class="modal-body panel-body" id="modalViewBody">
                <div class="alert alert-success hidden" id="modalViewSuccessAlert">
                    <strong>Success!</strong> <span></span>
                </div>
                <div class="alert alert-danger hidden" id="modalViewDangerAlert">
                    <strong>Error!</strong> <span></span>
                </div>
                <div class="text-center hidden" id="modalViewLoading">
                    Loading...
                </div>
                <form class="form" role="form" id="modalViewForm">
                    <div class="form-group hidden">
                        <label class="col-sm-3 control-label">Certificate (CRT)</label>
                        <textarea class="form-control" onfocus="this.select();" rows="5" id="viewCRTInput"></textarea>
                    </div>
                    <div class="clearfix"></div>
                    
                    <div class="form-group hidden">
                        <label class="col-sm-3 control-label">Intermediate/Chain files</label>
                        <textarea class="form-control" onfocus="this.select();" rows="10" id="viewCertificateInput"></textarea>
                    </div>
                    <div class="clearfix"></div>

                    <div class="form-group hidden">
                        <label class="col-sm-3 control-label">CSR (Certificate Signing Request)</label>
                        <textarea class="form-control" onfocus="this.select();" rows="5" id="viewCSRInput"></textarea>
                    </div>
                    <div class="clearfix"></div>
                </form>

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
    $(document).ready(function () {

        var serviceUrl = 'clientsservices.php?userid={$userid}&id={$serviceid}',
                viewBtn = $('#btnView_Certificate'),
                viewModal,
                viewBody,
                viewLoading,
                viewDangerAlert,
                viewSuccessAlert,
                viewCertificateInput,
                viewCRTInput,
                viewCSRInput,
                body = $('body');

        function assignModalElements(init) {
            viewModal = $('#modalView');
            viewBody = $('#modalViewBody');
            
            if (init) {
                viewBody.contents()
                .filter(function(){
                    return this.nodeType === 8;
                })
                .replaceWith(function(){
                    return this.data;
                });
            }

            if (!init) {
                viewDangerAlert = $('#modalViewDangerAlert');
                viewSuccessAlert = $('#modalViewSuccessAlert');
                viewLoading = $('#modalViewLoading');
                viewCertificateInput = $('#viewCertificateInput');
                viewCRTInput = $('#viewCRTInput');
                viewCSRInput = $('#viewCSRInput');
            }
        }

        function moveModalToBody() {
            body.append(viewModal.clone());
            viewModal.remove();
            assignModalElements(false);
        }

        function unbindOnClickForViewCertificateBtn() {
            viewBtn.attr('onclick', '');
        }

        function bindModalToViewCertificateBtn() {
            viewBtn.off().on('click', function () {
                viewModal.modal('show');
                fetchCertificate();
            });
        }

        function showSuccessAlert(msg) {
            show(viewSuccessAlert);
            hide(viewDangerAlert);
            viewSuccessAlert.children('span').html(msg);
        }

        function showDangerAlert(msg) {
            hide(viewSuccessAlert);
            show(viewDangerAlert);
            viewDangerAlert.children('span').html(msg);
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
            hide(viewDangerAlert);
            hide(viewSuccessAlert);
            hide(viewCertificateInput.parent('.form-group'));
            hide(viewCRTInput.parent('.form-group'));
            hide(viewCSRInput.parent('.form-group'));
            show(viewLoading); // xD
        }

        function renderCertificates(data) {
            hide(viewLoading);

            if (typeof data === 'undefined') {
                return;
            }

            if (typeof data.ca !== 'undefined') {
                show(viewCertificateInput.parent('.form-group'));
                viewCertificateInput.val(data.ca);
            }

            if (typeof data.crt !== 'undefined') {
                show(viewCRTInput.parent('.form-group'));
                viewCRTInput.val(data.crt);
            }

            if (typeof data.csr !== 'undefined') {
                show(viewCSRInput.parent('.form-group'));
                viewCSRInput.val(data.csr);
            }
        }

        function fetchCertificate() {

            hideAll();

            var data = {
                viewModal: 'yes',
                serviceId: {$serviceid},
                userID: {$userid},
            };
            $.ajax({
                type: "POST",
                url: serviceUrl,
                data: data,
                success: function (ret) {
                    var data;
                    if (!isJsonString(ret)) {
                        anErrorOccurred();
                        return;
                    }
                    data = JSON.parse(ret);
                    if (data.success === 1) {
                        renderCertificates(data.data);
                    } else {
                        renderCertificates(data.data);
                        showDangerAlert(data.msg);
                    }
                },
                error: function (jqXHR, errorText, errorThrown) {
                    anErrorOccurred();
                }
            });
        }

        assignModalElements(true);
        moveModalToBody();
        unbindOnClickForViewCertificateBtn();
        bindModalToViewCertificateBtn();
    });
</script>


<div class="modal fade" id="modalRecheck" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-primary">
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
                    <table id="details" class="table" style="width:100%;">
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
    $(document).ready(function () {

        var serviceUrl = 'clientsservices.php?userid={$userid}&id={$serviceid}',
                recheckBtn = $('#btnRecheck_Certificate_Details'),
                recheckModal,
                recheckBody,
                recheckLoading,
                recheckDangerAlert,
                recheckSuccessAlert,
                recheckDetails,
                body = $('body');

        function assignModalElements(init) {
            recheckModal = $('#modalRecheck');
            recheckBody = $('#modalRecheckBody');
            
            if (init) {
                recheckBody.contents()
                .filter(function(){
                    return this.nodeType === 8;
                })
                .replaceWith(function(){
                    return this.data;
                });
            }

            if (!init) {
                recheckDangerAlert = $('#modalRecheckDangerAlert');
                recheckSuccessAlert = $('#modalRecheckSuccessAlert');
                recheckLoading = $('#modalRecheckLoading');
                recheckDetails = $('#modalRecheckDetails');
            }
        }
        
        function showLoader()
        {
            show(recheckLoading);
        }

        function moveModalToBody() {
            body.append(recheckModal.clone());
            recheckModal.remove();
            assignModalElements(false);
        }

        function unbindOnClickForViewCertificateBtn() {
            recheckBtn.attr('onclick', '');
        }

        function bindModalToViewCertificateBtn() {
            recheckBtn.off().on('click', function () {
                recheckModal.modal('show');
                fetchCertificateDetails();
            });
        }

        function showSuccessAlert(msg) {
            show(recheckSuccessAlert);
            hide(recheckDangerAlert);
            recheckSuccessAlert.children('span').html(msg);
        }

        function showDangerAlert(msg) {
            hide(recheckSuccessAlert);
            show(recheckDangerAlert);
            recheckDangerAlert.children('span').html(msg);
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
            hide(recheckDangerAlert);
            hide(recheckSuccessAlert);
            hide(recheckDetails);
        }

        function renderCertificates(data) {
            
            $("table#details").empty();

            if (typeof data === 'undefined') {
                return;
            }
     
            for(var key in data)
            {
                var tr = $("<tr />");
                var td = $("<td />").text(key);
                tr.append(td);
                var td = $("<td />").text(data[key]);
                tr.append(td);
                $("table#details").append(tr);
            }
            
            show(recheckDetails);
        }

        function fetchCertificateDetails() {

            hideAll();
            showLoader();

            var data = {
                recheckModal: 'yes',
                serviceId: {$serviceid},
                userID: {$userid},
            };
            $.ajax({
                type: "POST",
                url: serviceUrl,
                data: data,
                success: function (ret) {
                    var data;
                    if (!isJsonString(ret)) {
                        anErrorOccurred();
                        return;
                    }
                    data = JSON.parse(ret);
                    
                    hide(recheckLoading);
                    
                    if (data.success === 1) 
                    {
                       renderCertificates(data.data);
                    } 
                    else 
                    {
                        showDangerAlert(data.msg);
                    }
                },
                error: function (jqXHR, errorText, errorThrown) {
                    anErrorOccurred();
                }
            });
        }

        assignModalElements(true);
        moveModalToBody();
        unbindOnClickForViewCertificateBtn();
        bindModalToViewCertificateBtn();
    });
</script>