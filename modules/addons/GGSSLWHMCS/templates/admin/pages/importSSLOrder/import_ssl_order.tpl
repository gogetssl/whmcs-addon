{if $formError}
    <div class="col-lg-12">
        <div class="note note-danger">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
            <p><strong>{$formError}</strong></p>
        </div>
    </div>
{/if}  

<div class="panel panel-default">
    <div class="panel-body">
        {$form} 
    </div>
</div>

<script>
    {literal}
        $(document).ready(function () {
            jQuery('button[name="importSSL"]').click(function () {
                var orderID = $('#importSSLOrder_default_order_id').val(),
                        clientID = $('#importSSLOrder_default_client_id').val();
                $('.loading-icon').remove();
                $(this).append(' <span class="fa loading-icon"></span>')
                JSONParser.request('importSSL', {order_id: orderID, client_id: clientID}, function (data) {
                    if (data.success == true) {
                        $('#MGAlerts>div[data-prototype="success"]').show();
                        $('#MGAlerts>div[data-prototype="success"] strong').html(data.message);
                    } else if (data.success == false) {
                        $('#MGAlerts>div[data-prototype="error"]').show();
                        $('#MGAlerts>div[data-prototype="error"] strong').html(data.message);
                    }
                    $('.loading-icon').remove();
                }, false);
                
            });
            jQuery('button[class="close"]').click(function () {
                $(this).parent().parent().css('display', 'none');
            });
        });
    {/literal}
</script>
