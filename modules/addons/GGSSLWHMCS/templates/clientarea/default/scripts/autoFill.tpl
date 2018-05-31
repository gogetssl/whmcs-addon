<script type="text/javascript">
    $(document).ready(function () {
        var fillVars = JSON.parse('{$fillVars}');
        for (var i = 0; i < fillVars.length; i++) {
            $('input[name="' + fillVars[i].name + '"]').val(fillVars[i].value);
            $('textarea[name="' + fillVars[i].name + '"]').val(fillVars[i].value);
            $('select[name="' + fillVars[i].name + '"]').val(fillVars[i].value);
        }
    });
</script>

