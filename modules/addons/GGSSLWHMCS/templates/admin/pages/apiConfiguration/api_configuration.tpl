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
                    <legend>{$MGLANG->T('cronSynchronization','header')}</legend>
                    <span class="text-danger bold">{$MGLANG->T('cronSynchronization','pleaseNote')}</span>
                    <span>{$MGLANG->T('cronSynchronization', 'info')}</span><br />
                    <input type="text" class="form-control" value="{$MGLANG->T('cronSynchronization', 'commandLine', 'cronFrequency')} {$cronCommandLine}" readonly="">                    
                </div>
            </div>
        </form>
        {$form} 
    </div>
</div>

<script>
    {literal}
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
        });
    {/literal}
</script>
