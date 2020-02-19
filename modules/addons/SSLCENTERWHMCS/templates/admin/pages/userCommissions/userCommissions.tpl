
{literal}
    <script>
        var pleaseSelectOnePlaceholder = '{/literal}{$MGLANG->T('modal','pleaseSelecetOnePlaceholder')}{literal}';
        var pleaseSelectProductFirst = '{/literal}{$MGLANG->T('modal','pleaseSelectProductFirst')}{literal}';
        var noDataAvailable = '{/literal}{$MGLANG->T('modal','noDataAvailable')}{literal}';
        var pleaseSelectClientFirst = '{/literal}{$MGLANG->T('modal','selectClientFirstPlaceholder')}{literal}';
        var noClientAvaialblePlaceholder = '{/literal}{$MGLANG->T('modal','noClientAvaialblePlaceholder')}{literal}';
        var noProductAvaialblePlaceholder = '{/literal}{$MGLANG->T('modal','noProductAvaialblePlaceholder')}{literal}';

    </script>
{/literal}
<style>
    #rulesTable 
    td:nth-child(4) span, 
    td:nth-child(5) span, 
    td:nth-child(6) span, 
    td:nth-child(7) span, 
    td:nth-child(8) span,
    td:nth-child(9) span 
    {
        margin-left: 25%;
    }
    table.dataTable .sorting::after, .sorting_asc::after, .sorting_desc::after 
    {
        content: unset !important;
    }
</style>
<div class="box light">
    <div class="row">
        <div class="col-lg-12" id="mg-home-content" >
            <legend>{$MGLANG->T('title')}</legend>
            <div class="row">
                <div class="col-lg-2">
                    <button type="button" class="btn btn-success btn-inverse" id="addUserCommissionRule">{$MGLANG->T('addNewCommissionRule')}</button>  
                </div>                
            </div>
            <div id="rulesTable">
                <table width="100%" class="table table-striped" >
                    <colgroup>
                        <col style="width: 10%"/>
                        <col style="width: 10%"/>
                        <col style="width: 5%"/>
                        <col style="width: 11%"/>
                        <col style="width: 11%"/>
                        <col style="width: 11%"/>
                        <col style="width: 11%"/>
                        <col style="width: 11%"/>
                        <col style="width: 11%"/>
                        <col style="width: 9%"/>
                    </colgroup>
                    <thead>
                    <th>{$MGLANG->T('table', 'client')}</th>                    
                    <th>{$MGLANG->T('table', 'product')}</th>  
                    <th>{$MGLANG->T('table', 'commission')}</th>  
                    <th>{$MGLANG->T('table', 'monthly/onetime')}</th> 
                    <th>{$MGLANG->T('table', 'quarterly')}</th>  
                    <th>{$MGLANG->T('table', 'semiannually')}</th> 
                    <th>{$MGLANG->T('table', 'annually')}</th>  
                    <th>{$MGLANG->T('table', 'biennially')}</th> 
                    <th>{$MGLANG->T('table', 'triennially')}</th> 
                    <th>{$MGLANG->T('table', 'actions')}</th>    
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
                    
<div class="panel panel-default">
    <div class="panel-body">
        <div class="col-lg-12 cronSynchronizationInfo">
            <legend>{$MGLANG->T('integrationCode','header')}</legend>
            <div class="col-lg-11 marginated">
                <span class="text-danger bold">{$MGLANG->T('pleaseNote')}</span>
                <span>{$MGLANG->T('info')}</span>    
                <p>{$MGLANG->T('info1')}</p> 
            </div>
            <input type="text" class="form-control" value="{$templatePath1}" readonly="" style="min-width: 45%; max-width: 636px;"> 
            <br />
            <div class="col-lg-11 marginated">   
                <p>{$MGLANG->T('info2')}</p> 
                <textarea cols="20" style="min-width: 50%; resize: none; height: 65px;" disabled="">
{literal}{if $_pricing}
	{assign var="pricing" value=$_pricing}
{/if}{/literal}</textarea>
            </div>
            <div class="col-lg-11 marginated">  
                <p>{$MGLANG->T('info3')}</p> 
            </div>
            <input type="text" class="form-control" value="{$templatePath2}" readonly="" style="min-width: 45%; max-width: 636px;"> 
            <br />
            <div class="col-lg-11 marginated">   
                <p>{$MGLANG->T('info4')}</p> 
                <textarea cols="20" style="min-width: 50%; resize: none; height: 65px;" disabled="">
{literal}{if $_products}
	{assign var="products" value=$_products}
{/if}{/literal}</textarea>
            </div>

            <br />
        </div>
    </div>
</div>
<!--   Add User Commission Rule Modal -->
<form data-toggle="validator" role="form" id="MGAddCommissionForm">
    <div class="modal fade bs-example-modal-lg" id="MGAddCommission" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">{$MGLANG->T('modal', 'addCommissionRule')} <strong></strong></h4>
                </div>
                <div class="modal-loader" style="display:none;"></div>
                <div class="modal-body">
                    <div class="modal-alerts">
                        <div style="display:none;" data-prototype="error" id="errorModal">
                            <div class="note note-danger">
                                <strong></strong>
                                <a style="display:none;" class="errorID" href=""></a>
                            </div>
                        </div>
                        <div style="display:none;" data-prototype="success" id="successModal">
                            <div class="note note-success">
                                <strong></strong>
                            </div>
                        </div>
                    </div> 
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-client" class="col-sm-2 control-label">{$MGLANG->T('modal','client')}</label>
                            <div class="col-sm-9">       
                                <select name="client" class="form-control" id="mg-access-client" placeholder="" required="">
                                    {if $clients|@count == 0}
                                        <option value="" disabled selected>{$MGLANG->T('modal','noClientAvailable')}</option>
                                    {else}
                                        <option value="" disabled selected>{$MGLANG->T('modal','pleaseSelecetOnePlaceholder')}</option>
                                        {foreach from=$clients item=client}
                                            <option value="{$client.id}">#{$client.id} {$client.name}</option>
                                        {/foreach} 
                                    {/if}
                                </select>                                          
                            </div>
                        </div>
                    </div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-product" class="col-sm-2 control-label">{$MGLANG->T('modal','product')}</label>
                            <div class="col-sm-9">       
                                <select name="product" class="form-control" id="mg-access-product" placeholder="" required=""> 
                                    <option value='' disabled="" selected="">{$MGLANG->T('modal','selectClientFirstPlaceholder')}</option>
                                </select>                                          
                            </div>
                        </div>
                    </div>   
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-product" class="col-sm-2 control-label">{$MGLANG->T('modal','productPrice')}</label>
                            <div class="col-sm-9">       
                                <table id="product_price" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{$MGLANG->T('modal', 'monthly/onetime')}</th>
                                            <th>{$MGLANG->T('modal', 'quarterly')}</th>
                                            <th>{$MGLANG->T('modal', 'semiannually')}</th>
                                            <th>{$MGLANG->T('modal', 'annually')}</th>
                                            <th>{$MGLANG->T('modal', 'biennially')}</th>
                                            <th>{$MGLANG->T('modal', 'triennially')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" align="center">{$MGLANG->T('modal','pleaseSelectProductFirst')}</td>
                                        </tr>                                                                           
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-commission" class="col-sm-2 control-label">{$MGLANG->T('modal','commission')}</label>
                            <div class="col-sm-9">                                           
                                <input name="commission" value="" class="form-control" id="mg-access-commission" placeholder=""  type="text" required="" pattern="\d*">                                            
                            </div>
                        </div>
                    </div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-product" class="col-sm-2 control-label">{$MGLANG->T('modal','productPriceWithCommission')}</label>
                            <div class="col-sm-9">       
                                <table id="product_price_with_commission" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{$MGLANG->T('modal', 'monthly/onetime')}</th>
                                            <th>{$MGLANG->T('modal', 'quarterly')}</th>
                                            <th>{$MGLANG->T('modal', 'semiannually')}</th>
                                            <th>{$MGLANG->T('modal', 'annually')}</th>
                                            <th>{$MGLANG->T('modal', 'biennially')}</th>
                                            <th>{$MGLANG->T('modal', 'triennially')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" align="center">{$MGLANG->T('modal','pleaseSelectProductFirst')}</td>
                                        </tr>                                                                           
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>                            
                <div class="modal-footer" style="margin-top:  5px;">
                    <button type="button" class="btn btn-success btn-inverse" id="addNewUserCommissionRule">{$MGLANG->T('modal','add')}</button>
                    <button type="button" class="btn btn-default btn-inverse" data-dismiss="modal">{$MGLANG->T('modal','close')}</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!--   Edit User Commission Rule Modal -->
<form data-toggle="validator" role="form" id="MGEditCommissionForm">
    <div class="modal fade bs-example-modal-lg" id="MGEditCommission" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">{$MGLANG->T('modal', 'editCommissionRule')} <strong></strong></h4>
                </div>
                <div class="modal-loader" style="display:none;"></div>
                <div class="modal-body">
                    <div class="modal-alerts">
                        <div style="display:none;" data-prototype="error" id="errorModal">
                            <div class="note note-danger">
                                <strong></strong>
                                <a style="display:none;" class="errorID" href=""></a>
                            </div>
                        </div>
                        <div style="display:none;" data-prototype="success" id="successModal">
                            <div class="note note-success">
                                <strong></strong>
                            </div>
                        </div>
                    </div> 
                    <input type="hidden" value="" name="rule_id" />
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-client" class="col-sm-2 control-label">{$MGLANG->T('modal','client')}</label>
                            <div class="col-sm-9">       
                                <select name="client" class="form-control" id="mg-access-client" placeholder="" required="" readonly="">                                    
                                    <option value="" disabled selected>{$MGLANG->T('modal','noClientAvailable')}</option>                                    
                                </select>                                          
                            </div>
                        </div>
                    </div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-product" class="col-sm-2 control-label">{$MGLANG->T('modal','product')}</label>
                            <div class="col-sm-9">       
                                <select name="product" class="form-control" id="mg-access-product" placeholder="" required="" readonly=""> 
                                    <option value="" disabled selected>{$MGLANG->T('modal','noProductAvailable')}</option>  
                                </select>                                          
                            </div>
                        </div>
                    </div>   
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-product" class="col-sm-2 control-label">{$MGLANG->T('modal','productPrice')}</label>
                            <div class="col-sm-9">       
                                <table id="product_price" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{$MGLANG->T('modal', 'monthly/onetime')}</th>
                                            <th>{$MGLANG->T('modal', 'quarterly')}</th>
                                            <th>{$MGLANG->T('modal', 'semiannually')}</th>
                                            <th>{$MGLANG->T('modal', 'annually')}</th>
                                            <th>{$MGLANG->T('modal', 'biennially')}</th>
                                            <th>{$MGLANG->T('modal', 'triennially')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" align="center">{$MGLANG->T('modal','pleaseSelectProductFirst')}</td>
                                        </tr>                                                                           
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-commission" class="col-sm-2 control-label">{$MGLANG->T('modal','commission')}</label>
                            <div class="col-sm-9">                                           
                                <input name="commission" value="" class="form-control" id="mg-access-commission" placeholder=""  type="text" required="" pattern="\d*">                                            
                            </div>
                        </div>
                    </div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="mg-access-product" class="col-sm-2 control-label">{$MGLANG->T('modal','productPriceWithCommission')}</label>
                            <div class="col-sm-9">       
                                <table id="product_price_with_commission" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{$MGLANG->T('modal', 'monthly/onetime')}</th>
                                            <th>{$MGLANG->T('modal', 'quarterly')}</th>
                                            <th>{$MGLANG->T('modal', 'semiannually')}</th>
                                            <th>{$MGLANG->T('modal', 'annually')}</th>
                                            <th>{$MGLANG->T('modal', 'biennially')}</th>
                                            <th>{$MGLANG->T('modal', 'triennially')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" align="center">{$MGLANG->T('modal','pleaseSelectProductFirst')}</td>
                                        </tr>                                                                           
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>                            
                <div class="modal-footer" style="margin-top:  5px;">
                    <button type="button" class="btn btn-primary btn-inverse" id="updateUserCommissionRule">{$MGLANG->T('modal','edit')}</button>
                    <button type="button" class="btn btn-default btn-inverse" data-dismiss="modal">{$MGLANG->T('modal','close')}</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- Remove Rule Modal  -->
<div class="modal fade bs-example-modal-lg" id="MGRuleRemove" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">{$MGLANG->T('modal','removeRule')}</h4>
            </div>
            <div class="modal-body">
                <input type='hidden' name='rule_id'/>
                <h4 class="text-center">{$MGLANG->T('modal','removeRuleInfo')} <b id="MGremoveInformation"></b></h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-inverse" id="removeRuleButton" data-dismiss="modal">{$MGLANG->T('modal','remove')}</button>
                <button type="button" class="btn btn-default btn-inverse" data-dismiss="modal">{$MGLANG->T('modal','close')}</button>
            </div>
        </div>
    </div>
</div>


<script>
    {literal}
            function removeErrorStyle(modal)
            {
                $(modal).find('.form-group').removeClass('has-error');
                cleanModalMessage(modal);
            }
            function cleanModalMessage(modal) {
                $(modal).find('#successModal').attr('style', 'display:none;');
                $(modal).find('#errorModal').attr('style', 'display:none;');
            }
            function openModal(modal) {
                //clear fields
                $(modal).find('input,select').val('');
                //remove error styling
                removeErrorStyle($(modal));
                //open modal
                $(modal).modal();
            }
            function errorModal(info, modal) {
                cleanModalMessage(modal);
                $(modal).find('#errorModal').removeAttr('style');
                $(modal).find('#errorModal').find('strong').text(info);
            }
            function loadAvailalbleProducts(select)
            {
                var client_id = select.val();
                var modal = select.parents('.modal');

                JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=userCommissions', 'POST');
                JSONParser.request('loadAvailableProducts', {client_id: client_id}, function (data) {
                    if (data.products) {
                        var options = '<option disabled="" selected="" value="">' + pleaseSelectOnePlaceholder + '</option>';
                        data.products.forEach(function (product) {
                            options += '<option value="' + product.id + '">#' + product.id + ' ' + product.name + '</option>'
                        });

                        $('select[name="product"]').html(options);
                    } else {
                        errorModal(data.error, modal);
                    }
                });
            }

            function calculateNewPrice(price, multiplier)
            {
                if (price === '-')
                    return price;

                price = parseFloat(price);
                multiplier = parseFloat(multiplier) / 100;

                return (price + price * multiplier).toFixed(2);
            }

            function loadProductPricing(select)
            {
                var product_id = select.val();
                var modal = select.parents('.modal');
                var commission = parseFloat($(modal).find('input[name="commission"]').val());

                if (isNaN(commission))
                    commission = 0;

                JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=userCommissions', 'POST');
                JSONParser.request('loadProductPricing', {product_id: product_id}, function (data) {
                    if (data.pricings) {
                        var rows = generatePricingstableRows(data, commission);

                        $(modal).find('#product_price tbody').html(rows['rowsPriceHtml']);
                        $(modal).find('#product_price_with_commission tbody').html(rows['rowsPricWithCommissionHtml']);
                    } else {
                        errorModal(data.error, modal);
                    }
                });
            }

            function recalculateProductPriceWithCommission(input)
            {

                var modal = input.parents('.modal');
                var commission = parseFloat(input.val());
                var product_id = modal.find('select[name="product"]').val();

                if (product_id == null || product_id == '')
                    return false;
                if (isNaN(commission))
                    commission = 0;

                JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=userCommissions', 'POST');
                JSONParser.request('loadProductPricing', {product_id: product_id}, function (data) {
                    if (data.pricings) {
                        var rowsPricWithCommissionHtml = generatePricingstableRows(data, commission)['rowsPricWithCommissionHtml'];

                        $(modal).find('#product_price_with_commission tbody').html(rowsPricWithCommissionHtml);
                    } else {
                        errorModal(data.error, modal);
                    }
                });


            }

            function addNewCommissionRule(input)
            {
                var modal = input.parents('.modal');

                var client_id = $(modal).find('select[name="client"]').val();
                var product_id = $(modal).find('select[name="product"]').val();
                var commission = $(modal).find('input[name="commission"]').val();

                if(commission == '' || product_id == '' || product_id == null || client_id == '' || commission == '0')
                {
                    if(product_id == null)
                    {
                        $(modal).find('select[name="product"]').parents('.form-group').addClass('has-error');
                    }
                    if(commission == '0')
                    {
                        $(modal).find('input[name="commission"]').parents('.form-group').addClass('has-error');
                    }
                    return false;
                }
                
                JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=userCommissions', 'POST');
                JSONParser.request('addNewCommissionRule', {client_id: client_id, product_id: product_id, commission: commission}, function (data) {
                    if (data.success) {
                        $('#rulesTable table').DataTable().ajax.reload();
                        $(modal).modal('hide');
                    } else {
                        errorModal(data.error, modal);
                    }
                });
            }

            function generatePricingstableRows(data, commission)
            {
                var rows = new Array();
                rows['rowsPriceHtml'] = '';
                rows['rowsPricWithCommissionHtml'] = '';

                if (data.pricings.length > 0)
                {
                    data.pricings.forEach(function (pricing) {
                        rows['rowsPriceHtml'] += '<tr><td align="center">' + pricing.code + '</td><td align="center">' + pricing.monthly + '</td><td align="center">' + pricing.quarterly + '</td><td align="center">' + pricing.semiannually + '</td><td align="center">' + pricing.annually + '</td><td align="center">' + pricing.biennially + '</td><td align="center">' + pricing.triennially + '</td></tr>'
                        rows['rowsPricWithCommissionHtml'] += '<tr><td align="center">' + pricing.code + '</td><td align="center">' + calculateNewPrice(pricing.monthly, commission) + '</td><td align="center">' + calculateNewPrice(pricing.quarterly, commission) + '</td><td align="center">' + calculateNewPrice(pricing.semiannually, commission) + '</td><td align="center">' + calculateNewPrice(pricing.annually, commission) + '</td><td align="center">' + calculateNewPrice(pricing.biennially, commission) + '</td><td align="center">' + calculateNewPrice(pricing.triennially, commission) + '</td></tr>'
                    });
                } else
                {
                    rows['rowsPriceHtml'] += rows['rowsPricWithCommissionHtml'] += '<tr><td colspan="8" align="center">' + noDataAvailable + '</td></tr>'
                }

                return rows;
            }

            function removeCommissionRule(button)
            {
                var modal = button.parents('.modal');

                var rule_id = modal.find('input[name="rule_id"]').val();

                JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=userCommissions', 'POST');
                JSONParser.request('removeCommissionRule', {rule_id: rule_id}, function (data) {
                    if (data.success) {
                        $('#rulesTable table').DataTable().ajax.reload();
                        $(modal).modal('hide');
                    } else {
                        errorModal(data.error, modal);
                    }
                });
            }

            function updateCommissionRule(button)
            {
                var modal = button.parents('.modal');

                var rule_id = modal.find('input[name="rule_id"]').val();
                var commission = modal.find('input[name="commission"]').val();

                if(commission == '' || commission == '0')
                {
                    $(modal).find('input[name="commission"]').parents('.form-group').addClass('has-error');
                    return false;
                }
                
                JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=userCommissions', 'POST');
                JSONParser.request('updateCommissionRule', {rule_id: rule_id, commission: commission}, function (data) {
                    if (data.success) {
                        $('#rulesTable table').DataTable().ajax.reload();
                        $(modal).modal('hide');
                    } else {
                        errorModal(data.error, modal);
                    }
                });
            }

            function initDatatable()
            {
                $('#rulesTable table').DataTable({
                    "destroy": true,
                    "responsive": true,
                    "lengthChange": false,
                    "searching": true,
                    "processing": false,
                    "order": [[0, "asc"]],
                    "bInfo": false,
                    ajax: function (data, callback, settings) {
                        var filter = {
                            //    serverID: $('#pm-filters-server').val(),
                        };
                        JSONParser.request(
                                'getCommissionRules'
                                , {
                                    json: true,
                                    'mg-page': 'userCommissions'
                                }
                        , function (data) {
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
                        {'sType': 'natural', "bVisible": true, 'bSortable': false, "responsivePriority": 7},
                        {'sType': 'natural', "bVisible": true, 'bSortable': false, "responsivePriority": 8},
                        {'sType': 'natural', "bVisible": true, 'bSortable': false, "responsivePriority": 9},
                        {'sType': 'natural', 'bVisible': true, 'bSortable': false, 'bSearchable': false, "responsivePriority": 0},
                    ]
                });
            }
            $(document).ready(function () {

                initDatatable();

                //Add New User Commission Rule Modal
                $(document).on('click', '#addUserCommissionRule', function (e) {
                    var modal = $("#MGAddCommission");
                    openModal(modal)
                    var optionHTML = '<option value="" selected="" disabled="">' + pleaseSelectClientFirst + '</option>'
                    $(modal).find('select[name="product"]').html(optionHTML)
                    var rowsPriceHtml = '<tr><td colspan="8" align="center">' + pleaseSelectProductFirst + '</td></tr>'
                    $(modal).find('#product_price_with_commission tbody, #product_price tbody').html(rowsPriceHtml);
                    //set default fields values
                    //$(modal).find('#file_label').text(noFileSelectedPlaceholder)

                });
                $(document).on('change', "select[name='client']", function () {
                    loadAvailalbleProducts($(this))
                });

                $(document).on('change', "select[name='product']", function () {
                    loadProductPricing($(this))
                });

                $(document).on('keyup', "input[name='commission']", function () {
                    recalculateProductPriceWithCommission($(this))
                });

                $(document).on('click', "#addNewUserCommissionRule", function () {
                    addNewCommissionRule($(this))
                });

                $(document).on('click', '.deleteItem', function () {
                    var modal = $("#MGRuleRemove");
                    openModal(modal);
                    $(modal).find('input[name="rule_id"]').val($(this).data('id'));
                });

                $(document).on('click', '#removeRuleButton', function () {
                    removeCommissionRule($(this));
                });

                $(document).on('click', '.editItem', function () {
                    var modal = $("#MGEditCommission");

                    removeErrorStyle($(modal));
                    var rule_id = $(this).data('id');

                    modal.find('input[name="rule_id"]').val($(this).data('id'));

                    JSONParser.create('addonmodules.php?module=SSLCENTERWHMCS&json=1&mg-page=userCommissions', 'POST');
                    JSONParser.request('getSingleCommissionRule', {rule_id: rule_id}, function (data) {
                        if (data) {

                            var clientOptionHTML = '<option value="" selected="" dsiabled="">#' + data.client.id + ' ' + data.client.name + '</option>';
                            modal.find('select[name="client"]').html(clientOptionHTML);

                            var productOptionHTML = '<option value="' + data.product.id + '" selected="" dsiabled="">#' + data.product.id + ' ' + data.product.name + '</option>';
                            modal.find('select[name="product"]').html(productOptionHTML);

                            modal.find('input[name="commission"]').val(data.commission);

                            var pricingRows = generatePricingstableRows(data, data.commission);

                            $(modal).find('#product_price tbody').html(pricingRows['rowsPriceHtml']);
                            $(modal).find('#product_price_with_commission tbody').html(pricingRows['rowsPricWithCommissionHtml']);

                            modal.modal();
                        } else {
                            errorModal(data.error, modal);
                        }
                    });
                });

                //updateUserCommissionRule
                $(document).on('click', '#updateUserCommissionRule', function () {
                    updateCommissionRule($(this));
                });
            });
    {/literal}
</script>

