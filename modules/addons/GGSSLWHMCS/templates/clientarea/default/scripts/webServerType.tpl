<script type="text/javascript">
    $(document).ready(function () {
        var serverTypes = JSON.parse('{$serverTypes}'),
                selectedServerId = '{$selectedServerId}',
                optionsHtml = '',
                optionAttributes = '';

        if (selectedServerId === '0') {
            optionsHtml = '<option value="" selected>'+'{$MGLANG->T('Please choose one...')}'+'</option>';
        }

        for (var i = 0; i < serverTypes.length; i++) {
            if (serverTypes[i].id == selectedServerId) {
                optionAttributes = ' selected';
            } else {
                optionAttributes = '';
            }
            optionsHtml = optionsHtml + '<option value="' + serverTypes[i].id + '" ' + optionAttributes + '>' + serverTypes[i].software + '</option>';
        }
        if($('#inputServerType').length > 0)
            $('#inputServerType').html(optionsHtml);
        
        //for control template
        if($('#servertype').length > 0)
            $('#servertype').html(optionsHtml);
    });
</script>
