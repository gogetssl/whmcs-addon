<script type="text/javascript">
    $(document).ready(function () {
        var privateKey = '{$privateKey}';
        $('input[name="privateKey"]').remove();
        $('#inputCsr').closest('.form-group').after('<input class="form-control" type="hidden" name="privateKey" value="' + privateKey + '" />');           
    });
</script>
