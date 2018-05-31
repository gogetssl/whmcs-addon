<script type="text/javascript">
    $(document).ready(function () {
        var dcvMethods = JSON.parse('{$methodTypes}'),
                selectedMethod = '',
                optionsHtml = '',
                optionAttributes = '';
        if (selectedMethod === '' && dcvMethods.length > 1) {
            optionsHtml = '<option value="" selected>Please choose one...</option>';
        }
        for (var i = 0; i < dcvMethods.length; i++) {
            if (dcvMethods[i].id == selectedMethod) {
                optionAttributes = ' selected';
            } else {
                optionAttributes = '';
            }
            optionsHtml = optionsHtml + '<option value="' + dcvMethods[i]+ '">' + dcvMethods[i] + '</option>';
        }
        
        $('select[name=\'fields[dcv_method]\']').html(optionsHtml);
        var fillVars = JSON.parse('{$fillVars}');
        if(Array.isArray(fillVars)) {
            for (var i = 0; i < fillVars.length; i++) {
                if(fillVars[i].name === 'fields[dcv_method]') {
                    $('select[name="fields[dcv_method]"]').val(fillVars[i].value);
                }
            }  
        } else {     
            $('select[name="fields[dcv_method]"]').val(fillVars);
        }
        if(dcvMethods.length == 1) {            
            $('select[name=\'fields[dcv_method]\']').val(dcvMethods[0]);
            $('select[name=\'fields[dcv_method]\']').prop('disabled', true);     
            $('select[name=\'fields[dcv_method]\']').before('<input class="form-control" type="hidden" name="fields[dcv_method]" value="' + dcvMethods[0] + '" />')
        }
       
    });
</script>
