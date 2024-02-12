<style>
    #ordersTable table th::after {
        display: none;
    }
</style>
<div class="box light">
    <div class="row">
        <div class="col-lg-12" id="mg-home-content" >
            <legend>{$MGLANG->T('title')}</legend>
            <div id="ordersTable">
                <table width="100%" class="table table-striped" >
                    <thead>
                    <th>{$MGLANG->T('table', 'id')}</th>
                    <th>{$MGLANG->T('table', 'client')}</th>
                    <th>{$MGLANG->T('table', 'service')}</th>
                    <th>{$MGLANG->T('table', 'order')}</th>
                    <th>{$MGLANG->T('table', 'verification_method')}</th>
                    <th>{$MGLANG->T('table', 'status')}</th>
                    <th>{$MGLANG->T('table', 'date')}</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    {literal}
    function initDatatable()
    {
        $('#ordersTable table').DataTable({
            "destroy": true,
            "responsive": true,
            "lengthChange": false,
            "searching": true,
            "processing": true,
            "serverSide": true,
            "order": [[0, "asc"]],
            "bInfo": false,
            ajax: function (data, callback, settings) {
                let filter = {};
                JSONParser.request(
                    'getOrders',
                    {json: true, 'mg-page': 'orders',filter:filter,order:data.order[0],limit: data.length,offset: data.start,search:data.search.value},
                    function (data) {
                        callback(data);
                    }
                );
            },
            "aoColumns": [
                {'sType': 'natural', "bVisible": true, "responsivePriority": 1},
                {'sType': 'natural', "bVisible": true, "responsivePriority": 2},
                {'sType': 'natural', "bVisible": true, "responsivePriority": 3},
                {'sType': 'natural', "bVisible": true, 'bSortable': false, "responsivePriority": 4},
                {'sType': 'natural', "bVisible": true, 'bSortable': false, "responsivePriority": 5},
                {'sType': 'natural', "bVisible": true, 'bSortable': false, "responsivePriority": 6},
                {'sType': 'natural', "bVisible": true, "responsivePriority": 7}
            ]
        });
    }
    $(document).ready(function () {
        initDatatable();

        $('body').on('click', '.setVerified', function (){
            let id = $(this).attr('data-id');
            JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=orders', 'POST');
            JSONParser.request('setVerified', {id:id}, function (data) {
                if (data.success) {
                    $('#ordersTable table').DataTable().ajax.reload();
                }
            });
        });

        $('body').on('click', '.setInstalled', function (){
            let id = $(this).attr('data-id');
            JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=orders', 'POST');
            JSONParser.request('setInstalled', {id:id}, function (data) {
                if (data.success) {
                    $('#ordersTable table').DataTable().ajax.reload();
                }
            });
        })

    });
    {/literal}
</script>

