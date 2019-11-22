{if $formError}
    <div class="col-lg-12">
        <div class="note note-danger">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
            <p><strong>{$formError}</strong></p>
        </div>
    </div>
{/if}  

{*<div class="panel panel-default">
<div class="panel-heading">How to get API details</div>
<div class="panel-body">
Proin malesuada eros in risus accumsan euismod. Vivamus lacinia pellentesque nunc, pretium varius felis tempus aliquam. In et pretium diam. Fusce in ex a ipsum semper mollis. Sed arcu eros, dictum quis orci vel, luctus volutpat enim. Curabitur vitae ante posuere, facilisis lacus a, tristique urna. Suspendisse rutrum arcu id turpis venenatis rutrum. 
</div>
</div>*}

<div class="panel panel-default">
    <div class="panel-body">
        <form class="form-horizontal normal-form">
            <div class="form-group">
                <div class="col-lg-12 cronSynchronizationInfo">
                    <legend>{$MGLANG->T('crons','header')}</legend>
                    <div class="col-lg-11 marginated">
                        <span class="text-danger bold">{$MGLANG->T('cronSynchronization','pleaseNote')}</span>
                        <span>{$MGLANG->T('cronSynchronization', 'info')}</span>                        
                    </div>
                    <input type="text" class="form-control" value="{$MGLANG->T('cronSynchronization', 'commandLine', 'cronFrequency')} {$cronCommandLine}" readonly=""> 
                    <br />
                    <div class="col-lg-11 marginated">
                        <span class="text-danger bold">{$MGLANG->T('cronSSLSummaryStats','pleaseNote')}</span>
                        <span>{$MGLANG->T('cronSSLSummaryStats', 'info')}</span>
                    </div>
                    <input type="text" class="form-control" value="{$MGLANG->T('cronSSLSummaryStats', 'commandLine', 'cronFrequency')} {$cronCommandLine2}" readonly=""> 
                    <br />
                    <div class="col-lg-11 marginated">
                        <span class="text-danger bold">{$MGLANG->T('cronRenewal','pleaseNote')}</span>
                        <span>{$MGLANG->T('cronRenewal', 'info')}</span>
                    </div>                   
                    <br />
                    <input type="text" class="form-control" value="{$MGLANG->T('cronRenewal', 'commandLine', 'cronFrequency')} {$cronCommandLine3}" readonly=""> 
                    <br />                    
                    <div class="col-lg-11 marginated">
                        <span class="text-danger bold">{$MGLANG->T('cronSendCertificate','pleaseNote')}</span>
                        <span>{$MGLANG->T('cronSendCertificate', 'info')}</span>
                    </div> 
                    <br />
                    <input type="text" class="form-control" value="{$MGLANG->T('cronSendCertificate', 'commandLine', 'cronFrequency')} {$cronCommandLine4}" readonly=""> 
                    <br />                    
                    <div class="col-lg-11 marginated">
                        <span class="text-danger bold">{$MGLANG->T('cronPriceUpdater','pleaseNote')}</span>
                        <span>{$MGLANG->T('cronPriceUpdater', 'info')}</span>
                    </div> 
                    <br />
                    <input type="text" class="form-control" value="{$MGLANG->T('cronPriceUpdater', 'commandLine', 'cronFrequency')} {$cronCommandLine5}" readonly="">
                    <br /> 
                            <div class="col-lg-11 marginated">
                        <span class="text-danger bold">{$MGLANG->T('cronCertificateDetailsUpdater','pleaseNote')}</span>
                        <span>{$MGLANG->T('cronCertificateDetailsUpdater', 'info')}</span>
                    </div> 
                    <br />
                    <input type="text" class="form-control" value="{$MGLANG->T('cronCertificateDetailsUpdater', 'commandLine', 'cronFrequency')} {$cronCommandLine6}" readonly="">
                </div>
            </div>
        </form>
        {$form} 
    </div>
</div>

<!-- Import Data & Configuration Modal  -->
<div class="modal fade bs-example-modal-lg" id="MGDataMigration" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">{$MGLANG->T('modal','migrationData')}</h4>
            </div>
            <div class="modal-body">
                <div class="modal-alerts">
                    <div style="display:none;" data-prototype="error" id="errorModal">
                        <div class="note note-danger">
                            <strong></strong>
                            <a style="display:none;" class="errorID" href=""></a>
                        </div>
                    </div>
                    <div style="display:none;" data-prototype="success" id="successModal">
                        <div class="note note-success">
                            <strong></strong>
                        </div>
                    </div>
                </div> 
                <h4 class="text-center">{$MGLANG->T('modal','dataMigrationInfo')} </h4>
                <h4 class="text-center">{$MGLANG->T('modal','dataMigrationInfo2')} </h4>
                <div style='padding-left: 15px;'>
                    <ul>
                        <li><h4>{$MGLANG->T('modal','dataMigrationInfoAction', '0')}</h4></li>
                        <li><h4>{$MGLANG->T('modal','dataMigrationInfoAction', '1')}</h4></li>
                        <li><h4>{$MGLANG->T('modal','dataMigrationInfoAction', '2')}</h4></li>
                    </ul> 
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" id="runMigrationButton" data-dismiss="modal">{$MGLANG->T('modal','import')}</button>
                <button type="button" class="btn btn-default btn-inverse" data-dismiss="modal">{$MGLANG->T('modal','close')}</button>
            </div>
        </div>
    </div>
</div>
<script>
    {literal}
        function removeErrorStyle(modal)
        {
            $(modal).find('.form-group').removeClass('has-error');
            cleanModalMessage(modal);
        }
        function cleanModalMessage(modal) {
            $(modal).find('#successModal').attr('style', 'display:none;');
            $(modal).find('#errorModal').attr('style', 'display:none;');
        }
        function successModal(info, modal) {
            cleanModalMessage(modal);
            $(modal).find('#successModal').removeAttr('style');
            $(modal).find('#successModal').find('strong').text(info);
        }
        $(document).ready(function () {
            $('input[name="use_admin_contact[]"]').on('click', function () {
                //$('input[name="use_admin_contact[]"]').prop('checked', true);
                if (!$(this).is(":checked")) {
                    $('input[id^="item_default_tech_"]').prop('readonly', false).prop('required', true);
                    $('#item_default_tech_country').prop('disabled', false).prop('required', true);
                    $('#techCountrHidden').remove();
                } else {
                    var defaultCountry = $('#item_default_tech_country').val();
                    $('#item_default_tech_country').before('<input id="techCountrHidden" name="tech_country" value="' + defaultCountry + '" class="form-control" type="hidden">');
                    $('input[id^="item_default_tech_"]').prop('readonly', true).prop('required', false);
                    $('#item_default_tech_country').prop('disabled', true).prop('required', false);
                    ;

                }
            });
            jQuery('button[name="testConnection"]').click(function () {
                var login = $('#item_default_api_login').val(),
                        password = $('#item_default_api_password').val();

                JSONParser.request('testConnection', {api_login: login, api_password: password}, function (data) {
                    if (data.success == true) {
                        $('#MGAlerts>div[data-prototype="success"]').show();
                        $('#MGAlerts>div[data-prototype="success"] strong').html(data.message);
                    } else if (data.success == false) {
                        $('#MGAlerts>div[data-prototype="error"]').show();
                        $('#MGAlerts>div[data-prototype="error"] strong').html(data.message);
                    }
                }, false);
            });
            $('input[name^="auto_renew_invoice"], input[name^="display_csr_generator[]"]').on('click', function () {
                //$('input[name="use_admin_contact[]"]').prop('checked', true);                
                if (!$(this).is(":checked")) {
                    $(this).parent().parent().parent().find('select').prop('disabled', true);
                } else {
                    $(this).parent().parent().parent().find('select').prop('disabled', false);
                }
            });
            jQuery('button[name="data_migration"]').click(function () {
                var modal = $("#MGDataMigration");
                removeErrorStyle($(modal));
                $(modal).find('.fa-spinner').remove();
                $(modal).modal();
            });
            $(document).on('click', '#runMigrationButton', function () {
                var modal = $("#MGDataMigration");
                $(this).append(' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
                removeErrorStyle($(modal));
                JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=apiConfiguration', 'POST');
                JSONParser.request('runMigration', {}, function (data) {

                    if (data.success) {
                        successModal(data.success, $(modal))
                        window.setTimeout(function () {
                            location.reload()
                        }, 5000);
                    }
                }, );
            });
        });
    {/literal}
</script>
