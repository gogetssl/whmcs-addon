<script type="text/javascript">
    $(document).ready(function () {
        var serverTypes = JSON.parse('{$serverTypes}'),
                selectedServerId = '{$selectedServerId}',
                optionsHtml = '',
                optionAttributes = '';

        if (selectedServerId === '0') {
            optionsHtml = '<option value="" selected>Please choose one...</option>';
        }

        for (var i = 0; i < serverTypes.length; i++) {
            if (serverTypes[i].id == selectedServerId) {
                optionAttributes = ' selected';
            } else {
                optionAttributes = '';
            }
            optionsHtml = optionsHtml + '<option value="' + serverTypes[i].id + '" ' + optionAttributes + '>' + serverTypes[i].software + '</option>';
        }

        $('#inputServerType').html(optionsHtml);
    });
</script>
