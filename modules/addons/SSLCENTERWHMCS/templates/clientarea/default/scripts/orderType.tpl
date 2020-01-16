<script type="text/javascript">
    if($('#inputServerType').length > 0) {
           $('#inputServerType').parent().before('<div class="form-group"><label for="inputOrderType">' + '{$MGLANG->T('orderTypeTitle')}' + '</label><br>' + '{$MGLANG->T('selectOrderTypeDescritpion')}' + '<select name="fields[order_type]" id="inputOrderType" class="form-control"></select></div>');
        }
        //for control theme
        if($('#servertype').length > 0) {            
           $('#servertype').parent().parent().before('<div class="form-group"><label class="col-sm-3 control-label" for="inputOrderType">' + '{$MGLANG->T('orderTypeTitle')}' + '</label>' + '{$MGLANG->T('selectOrderTypeDescritpion')}' + '<div class="col-sm-6"><select name="fields[order_type]" id="inputOrderType" class="form-control"></select><br></div></div>');
        
        }
    $(document).ready(function () {
        var orderTypes = JSON.parse('{$orderTypes}'),
                optionsHtml = '',
                optionAttributes = '';
        
        optionsHtml = '<option value="" selected>'+'{$MGLANG->T('Please choose one...')}'+'</option>';        
        for (var i = 0; i < orderTypes.length; i++) {  
            var type =  orderTypes[i];
            var lang =  '';
            switch(type) {
                case 'new':
                    lang = '{$MGLANG->T('newOrder')}';
                    break;
                case 'renew':
                    lang = '{$MGLANG->T('renewOrder')}';
                    break;
            }            
            optionsHtml = optionsHtml + '<option value="' + type+ '">' + lang + '</option>';
        }
        $('select[name=\'fields[order_type]\']').html(optionsHtml);
        var fillVars = JSON.parse('{$fillVars}');
        if(Array.isArray(fillVars)) {
            for (var i = 0; i < fillVars.length; i++) {
                if(fillVars[i].name === 'fields[order_type]') {
                    $('select[name="fields[order_type]"]').val(fillVars[i].value);
                }
            }  
        } else {     
            $('select[name="fields[order_type]"]').val(fillVars);
        }
        if(orderTypes.length == 1) {            
            $('select[name=\'fields[order_type]\']').val(orderTypes[0]);
            $('select[name=\'fields[order_type]\']').prop('disabled', true);     
            $('select[name=\'fields[order_type]\']').before('<input class="form-control" type="hidden" name="fields[order_type]" value="' + orderTypes[0] + '" />')
        }
       
    });
</script>
