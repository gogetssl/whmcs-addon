jQuery(document).ready(function () {
    var mgDataTable;

    mgDataTable = $('#mg-data-list').dataTable({
        processing: false,
        searching: true,
        autoWidth: false,
        "serverSide": false,
        "order": [[0, "desc"]],
        ajax: function (data, callback, settings) {
            var filter = {

            };
            JSONParser.request(
                    'list'
                    , {
                        filter: filter
                        , limit: data.length
                        , offset: data.start
                        , order: data.order
                        , search: data.search
                        , type: $('#sslOrderType').val()
                    }
            , function (data) {
                callback(data);
            }
            );
        },
        'columns': [
            , null
                    , null
                    , null

        ],
        'aoColumnDefs': [{
                'bSortable': false,
                'aTargets': ['nosort']
            }],
        language: {
            "zeroRecords": zeroRecordsLang,
            "infoEmpty": "",
            "search": searchLang,
            "paginate": {
                "previous": previousLang
                , "next": nextLang
            }
        }
    });
    
    $('#mg-data-list').on('click', ' tr', function(){
        $(this).find('form[name="redirectToService"]').submit(); 
    });
});

