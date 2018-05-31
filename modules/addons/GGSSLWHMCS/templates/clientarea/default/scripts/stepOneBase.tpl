<div id="divhideme" style="display: none"></div>
<script type="text/javascript">
    $(document).ready(function () {
        var brand = JSON.parse('{$brand}');       
        $('#inputCsr').closest('.form-group').after('<input class="form-control" type="hidden" name="brand" value="'+brand+'" />');
        
        $('#divhideme').closest('.form-horizontal').remove();
        $('input, textarea, select').addClass('form-control');
        
        $('.help-block').remove();
    });
</script>
