<div id="divhideme" style="display: none"></div>
<script type="text/javascript">
    $(document).ready(function () {
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
