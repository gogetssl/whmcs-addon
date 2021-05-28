<script type="text/javascript">
    $(document).ready(function () {
        var optionsHtml = '';
          
        optionsHtml = '<option value="-1" selected>Any Other</option>';
        
        if($('#inputServerType').length > 0)
        {
            $('#inputServerType').html(optionsHtml);
            $('#inputServerType').hide();
            $('#inputServerType').prev('label').hide();
        }
        //for control template
        if($('#servertype').length > 0)
        {
            $('#servertype').html(optionsHtml);
            $('#servertype').hide();
            $('#servertype').prev('label').hide();
        }
    });
</script>
