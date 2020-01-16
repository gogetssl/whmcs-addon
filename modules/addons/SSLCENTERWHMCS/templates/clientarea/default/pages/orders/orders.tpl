<input type="hidden" id="sslOrderType" value="{$orderType}" />
<h4>{$pageTitle}</h4>
<div class="box light">
    <div class="row">
        <div class="col-lg-12" id="mg-categories-content" >
            <table class="table table-striped dataTable no-footer" id="mg-data-list" >
                <thead>
                    <tr>
                        <th>{$MGLANG->T('sslSummaryOrdersPage','Product/Service')}</th>
                        <th>{$MGLANG->T('sslSummaryOrdersPage','Pricing')}</th>
                        <th>{$MGLANG->T('sslSummaryOrdersPage','Next Due Date')}</th>
                        <th>{$MGLANG->T('sslSummaryOrdersPage','Status')}</th> 
                    </tr>
                </thead>
                <tbody>
                </tbody> 
            </table>
        </div>
    </div>
</div>
{literal}
    <script type="text/javascript">        
        var zeroRecordsLang = '{/literal}{$MGLANG->absoluteT('Nothing to display')}{literal}';
        var searchLang = '{/literal}{$MGLANG->absoluteT('Search')}{literal}';
        var previousLang = '{/literal}{$MGLANG->absoluteT('Previous')}{literal}';
        var nextLang = '{/literal}{$MGLANG->absoluteT('Next')}{literal}';
    </script>
    <script type="text/javascript" src="{/literal}{$assetsURL}{literal}/js/pages/orders.js"></script>

{/literal}
<link rel="stylesheet" type="text/css" href="{$assetsURL}/css/pages/orders.css" /> 
