<div id="divhideme" style="display: none"></div>
<script type="text/javascript">
    $(document).ready(function () {

        $('#inputCsr').attr('readonly','');

        {if $domains}

            if(!$('.primary-content .card-body .alert-danger').length) {

                let contentPage = $('#inputOrderType').parent('div.form-group').parent('div');

                $(contentPage).hide();
                $(contentPage).parent('div').next("div").hide();
                $(contentPage).parent("div").append('<div class="card-body select-cpanel-server">' +
                    '<h2>{$MGLANG->T('Choose a domain')}</h2>' +
                    '<select id="step-type-data">' +
                    '<option value="custom">{$MGLANG->T('Custom domain')}</option>' +
                    '<optgroup label="cPanel">' +
                    '</optgroup>' +
                    '</select>' +
                    '<div style="margin-top: 20px;" class="form-group"><button id="goto_next_step" class="btn btn-primary" type="button">{$MGLANG->T('Go to next step')}</button></div>' +
                    '</div>');

                {foreach $domains as $domain}
                    $('#step-type-data optgroup').append('<option value="{$domain}">{$domain}</option>');
                {/foreach}

                $('body').on('click', '#goto_next_step', function () {
                    let typeStep = $('#step-type-data').val();
                    if (typeStep == 'custom') {
                        $('.select-cpanel-server').hide();
                        $('.select-cpanel-server').prev('div').show();
                        $('.select-cpanel-server').prev('div').parent('div').next('div').show();
                    } else {
                        $.ajax({
                            url: "index.php?m=SSLCENTERWHMCS&mg-page=Home&mg-action=generateCSR&json=1",
                            type: "post",
                            data: {
                                domain: typeStep,
                                country: $('select[name="C"]').val(),
                                state: $('input[name="ST"]').val(),
                                locality: $('input[name="L"]').val(),
                                organization: $('input[name="O"]').val(),
                                organizationUnit: $('input[name="OU"]').val(),
                                email: $('input[name="EA"]').val()
                            },
                            success: function (response) {
                                let results = JSON.parse(response);
                                $('#inputCsr').val(results.public_key);
                                $('#inputCsr').parent('div').append('<input class="form-control" type="hidden" name="privateKey" value="' + results.private_key + '">');
                                $('#inputCsr').parent('div').hide();
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                console.log(textStatus, errorThrown);
                            }
                        });


                        $('.select-cpanel-server').hide();
                        $('.select-cpanel-server').prev('div').show();
                        $('.select-cpanel-server').prev('div').parent('div').next('div').show();
                    }
                });
            } else {
                $('#inputCsr').parent('div').hide();
            }
        {/if}

        var brand = JSON.parse('{$brand}');
        $('textarea[name="csr"]').closest('.form-group').after('<input class="form-control" type="hidden" name="sslbrand" value="' + brand + '" />');

        var element = $('#divhideme').closest('.form-group');
        //for control template       
        if (element.parent()[0].className === 'panel-body') {
            element.parent().closest('.panel').remove();
        }
        //for six template
        else if (element.parent()[0].className === 'form-horizontal') {
            element.parent().remove();
        }

        $('input, textarea, select').addClass('form-control');

        //remove (Required if Organization Name is set) comment
        var jobTitleInput = $('input[name="jobtitle"]');         
        var jobTitleLabel = jobTitleInput.parent().find('label');//for simplicity template
        
        jobTitleInput.parent().html(jobTitleInput);        
        //for simplicity template
        if(jobTitleInput.parent().find('label').length === 0) {            
            //$( "p:contains('SANs')").remove();
            jobTitleInput.before(jobTitleLabel);
        }
        if($('textarea[name="fields[sans_domains]"]').length > 1) {
            $('label[for="inputAdditionalField"]')[1].remove();
            $('textarea[name="fields[sans_domains]"]')[1].remove();
        } 
        if($('input[name="fields[org_regions]"]').length > 1) {
            $('input[name="fields[org_regions]"]')[1].remove();
        }

        $('label[for="inputAdditionalField"]').each(function( index ) {
            if($(this).text() == '')
            {
                $(this).parent('.row').parent('fieldset').remove();
            }
        });

    });
</script>
