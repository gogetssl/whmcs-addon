{if $formError}
    <div class="col-lg-12">
        <div class="note note-danger">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
            <p><strong>{$formError}</strong></p>
        </div>
    </div>
{/if} 

<form action="" method="post" class="form-horizontal margin-bottom-15" style="display: none;" id="createConfOptionsForm">
    <input type="hidden" name="createConfOptions" value="yes">
    <input id="createConfOptionsFormId" type="hidden" name="productId" value="">
    <input id="createConfOptionsFormName" type="hidden" name="productName" value="">
</form>

{if $products_count}
    <div class="panel panel-default">
        <div class="panel-body">
            {foreach from=$products item=product}
                <form action="" method="post" class="form-horizontal margin-bottom-15">
                    <table class="table table-condensed" id="product_configuration">
                        <tr class="product-container" data-product="{$product->id}">
                        <input type="hidden" name="product[{$product->id}][id]" value="{$product->id}"/>
                        <td>
                            <div class="form-group">
                                <label class="control-label col-sm-2">{$MGLANG->T('goGetSSLProduct')}</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" value="{$product->apiConfig->name}" disabled>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">{$MGLANG->T('productName')}</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="product[{$product->id}][name]" value="{$product->name}">
                                </div>
                            </div>

                            {if $product->apiConfig->isSanEnabled}    
                                <div class="form-group">
                                    <label class="control-label col-sm-2">{$MGLANG->T('configurableOptions')}</label>
                                    <div class="col-sm-10">
                                        {if $product->confOption}
                                            <a href="#" onclick="manageconfigoptions('{$product->confOption->id}');return false;" class="btn btn-success"/>{$MGLANG->T('editPrices')}</a>
                                        {else}
                                            <a class="btn btn-success mg-js-create-oprions" data-id="{$product->id}" data-name="{$product->name}"/>{$MGLANG->T('createConfOptions')}</a>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                            <div class="form-group">
                                <label class="control-label col-sm-2">{$MGLANG->T('autoSetup')}</label>
                                <div class="col-sm-10">
                                    <select name="product[{$product->id}][autosetup]" class="form-control">
                                        <option value="order" {if $product->autosetup == 'order'}selected=""{/if}>{$MGLANG->T('autoSetupOrder')}</option>  
                                        <option value="payment" {if $product->autosetup == 'payment'}selected=""{/if}>{$MGLANG->T('autoSetupPayment')}</option> 
                                        <option value="on" {if $product->autosetup == 'on'}selected=""{/if}>{$MGLANG->T('autoSetupOn')}</option> 
                                        <option value="" {if $product->autosetup == ''}selected=""{/if}>{$MGLANG->T('autoSetupOff')}</option> 
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2">{$MGLANG->T('months')}</label>
                                <div class="col-sm-10">
                                    <div  class="maxMonths {if $product->paytype == 'onetime'}hidden{/if}">{$product->apiConfig->peroids}</div>
                                    <input {if $product->paytype == 'onetime'}class="hidden" disabled=""{/if} type="hidden" name="product[{$product->id}][configoption2]" value="{$product->apiConfig->peroids}"></input>
                                    <select name="product[{$product->id}][configoption2]" class="form-control {if $product->paytype != 'onetime'}hidden{/if}" {if $product->paytype != 'onetime'}disabled=""{/if}>
                                        {foreach from=$product->apiConfig->availablePeriods item=peroid}
                                            <option {if $product->configoption2 == $peroid}selected{/if} value="{$peroid}">{$peroid}</option>
                                        {/foreach}
                                    </select>                                    
                                </div>
                            </div>

                            {if $product->apiConfig->isSanEnabled}
                                <div class="form-group">
                                    <label class="control-label col-sm-2">{$MGLANG->T('enableSans')}</label>
                                    <div class="col-sm-10">
                                        <input type="checkbox" class="" name="product[{$product->id}][configoption3]" value="on" style="margin-top: 10px;" {if $product->configoption3 === 'on'} checked {/if}{if !$product->apiConfig->isSanEnabled} disabled {/if}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-2">{$MGLANG->T('includedSans')}</label>
                                    <div class="col-sm-10">
                                        <input type="number" class="form-control" name="product[{$product->id}][configoption4]" value="{$product->configoption4}" {if !$product->apiConfig->isSanEnabled} disabled {/if}>
                                    </div>
                                </div>  
                            {/if}

                            <div class="form-group">
                                <label class="control-label col-sm-2">{$MGLANG->T('status')}</label>
                                <div class="col-sm-10">
                                    <div class="buttons-container">
                                        {if $product->hidden eq 0}
                                            <button type="button" data-product-id="{$product->id}" class="btn btn-danger disable-product">{$MGLANG->T('statusDisable')}</button>
                                        {else} 
                                            <button type="button" data-product-id="{$product->id}" class="btn btn-success enable-product">{$MGLANG->T('statusEnable')}</button>
                                        {/if}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">{$MGLANG->T('paymentType')}</label>
                                <div class="col-sm-10">
                                    <select name="product[{$product->id}][paytype]" class="form-control mg-js-pricing-select" data-id="{$product->id}">
                                        <option {if $product->paytype == 'free'}selected{/if} value="free">{$MGLANG->T('paymentTypeFree')}</option>
                                        <option {if $product->paytype == 'recurring'}selected{/if} value="recurring">{$MGLANG->T('paymentTypeRecurring')}</option>
                                        <option {if $product->paytype == 'onetime'}selected{/if} value="onetime">{$MGLANG->T('paymentTypeOneTime')}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group" id="mg-js-pricing-group-{$product->id}" {if $product->paytype == 'free'}style="display: none;"{/if}>
                                <label class="control-label col-sm-2">{$MGLANG->T('pricing')}</label>
                                <div class="col-sm-10">
                                    
                                    <div class="product_prices">
                                        <table class="table">
                                            <tbody>
                                                <tr style="text-align:center;font-weight:bold">
                                                    <td></td>
                                                    <td></td>
                                                    <td class="prod-pricing-monthly-onetime">{$MGLANG->T('pricingMonthly')}</td>
                                                    {if in_array('3',$product->apiConfig->availablePeriods)}<td style="display: table-cell;" class="prod-pricing-recurring">{$MGLANG->T('pricingQuarterly')}</td>{/if}
                                                    {if in_array('6',$product->apiConfig->availablePeriods)}<td style="display: table-cell;" class="prod-pricing-recurring">{$MGLANG->T('pricingSemiAnnually')}</td>{/if}
                                                    {if in_array('12',$product->apiConfig->availablePeriods)}<td style="display: table-cell;" class="prod-pricing-recurring">{$MGLANG->T('pricingAnnually')}</td>{/if}
                                                    {if in_array('24',$product->apiConfig->availablePeriods)}<td style="display: table-cell;" class="prod-pricing-recurring">{$MGLANG->T('pricingBiennially')}</td>{/if}
                                                    {if in_array('36',$product->apiConfig->availablePeriods)}<td style="display: table-cell;" class="prod-pricing-recurring">{$MGLANG->T('pricingTriennially')}</td>{/if}
                                                </tr>
                                                {foreach from=$product->pricing item=pricing}
                                                    <tr style="text-align:center" bgcolor="#ffffff" currency="{$pricing->code}">
                                                        <td rowspan="3" bgcolor="#efefef"><b>{$pricing->code}</b></td>
                                                        <td>{$MGLANG->T('pricingSetupFee')}</td>
                                                        <td class="prod-pricing-monthly-onetime">
                                                            <input name="currency[{$pricing->pricing_id}][msetupfee]" id="setup_{$pricing->code}_monthly_{$pricing->pricing_id}" value="{$pricing->msetupfee}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                        </td>                                                      
                                                        {if in_array('3',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input name="currency[{$pricing->pricing_id}][qsetupfee]" id="setup_{$pricing->code}_quarterly_{$pricing->pricing_id}" value="{$pricing->qsetupfee}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                            </td> 
                                                        {/if} 
                                                        {if in_array('6',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input name="currency[{$pricing->pricing_id}][ssetupfee]" id="setup_{$pricing->code}_semiannually_{$pricing->pricing_id}" value="{$pricing->ssetupfee}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                            </td>
                                                        {/if} 
                                                        {if in_array('12',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input name="currency[{$pricing->pricing_id}][asetupfee]" id="setup_{$pricing->code}_annually_{$pricing->pricing_id}" value="{$pricing->asetupfee}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                            </td>
                                                        {/if} 
                                                        {if in_array('24',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input name="currency[{$pricing->pricing_id}][bsetupfee]" id="setup_{$pricing->code}_biennially_{$pricing->pricing_id}" value="{$pricing->bsetupfee}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                            </td>
                                                        {/if} 
                                                        {if in_array('36',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input name="currency[{$pricing->pricing_id}][tsetupfee]" id="setup_{$pricing->code}_triennially_{$pricing->pricing_id}" value="{$pricing->tsetupfee}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                            </td>
                                                        {/if} 
                                                    </tr>
                                                    <tr style="text-align:center" bgcolor="#ffffff" currency="{$pricing->code}">
                                                        <td>{$MGLANG->T('pricingPrice')}</td>
                                                        <td class="prod-pricing-monthly-onetime">
                                                            <input name="currency[{$pricing->pricing_id}][monthly]" id="pricing_{$pricing->code}_monthly_{$pricing->pricing_id}" size="10" value="{$pricing->monthly}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                        </td>
                                                        {if in_array('3',$product->apiConfig->availablePeriods)}
                                                        <td style="display: table-cell;" class="prod-pricing-recurring">
                                                            <input name="currency[{$pricing->pricing_id}][quarterly]" id="pricing_{$pricing->code}_quarterly_{$pricing->pricing_id}" size="10" value="{$pricing->quarterly}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                        </td>
                                                        {/if}
                                                        {if in_array('6',$product->apiConfig->availablePeriods)}
                                                        <td style="display: table-cell;" class="prod-pricing-recurring">
                                                            <input name="currency[{$pricing->pricing_id}][semiannually]" id="pricing_{$pricing->code}_semiannually_{$pricing->pricing_id}" size="10" value="{$pricing->semiannually}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                        </td>
                                                        {/if}
                                                        {if in_array('12',$product->apiConfig->availablePeriods)}
                                                        <td style="display: table-cell;" class="prod-pricing-recurring">
                                                            <input name="currency[{$pricing->pricing_id}][annually]" id="pricing_{$pricing->code}_annually_{$pricing->pricing_id}" size="10" value="{$pricing->annually}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                        </td>
                                                        {/if} 
                                                        {if in_array('24',$product->apiConfig->availablePeriods)}
                                                        <td style="display: table-cell;" class="prod-pricing-recurring">
                                                            <input name="currency[{$pricing->pricing_id}][biennially]" id="pricing_{$pricing->code}_biennially_{$pricing->pricing_id}" size="10" value="{$pricing->biennially}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                        </td>
                                                        {/if} 
                                                        {if in_array('36',$product->apiConfig->availablePeriods)}
                                                        <td style="display: table-cell;" class="prod-pricing-recurring">
                                                            <input name="currency[{$pricing->pricing_id}][triennially]" id="pricing_{$pricing->code}_triennially_{$pricing->pricing_id}" size="10" value="{$pricing->triennially}" style="" class="form-control input-inline input-100 text-center" type="text">
                                                        </td>
                                                        {/if}
                                                    </tr>
                                                    <tr style="text-align:center" bgcolor="#ffffff">
                                                        <td>{$MGLANG->T('pricingEnable')}</td>
                                                        <td class="prod-pricing-monthly-onetime">
                                                            <input class="pricingtgl" currency="{$pricing->code}" data-pricing-id="{$pricing->pricing_id}" cycle="monthly" type="checkbox" {if $pricing->monthly gte 0} checked="checked" {/if}>
                                                        </td>
                                                        {if in_array('3',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input data-pricing-id="{$pricing->pricing_id}" class="pricingtgl" currency="{$pricing->code}" cycle="quarterly" {if $pricing->quarterly gte 0} checked="checked" {/if} type="checkbox">
                                                            </td>
                                                        {/if}
                                                        {if in_array('6',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input class="pricingtgl" currency="{$pricing->code}" {if $pricing->semiannually gte 0} checked="checked" {/if} data-pricing-id="{$pricing->pricing_id}" cycle="semiannually" type="checkbox">
                                                            </td>
                                                        {/if}
                                                        {if in_array('12',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input class="pricingtgl" currency="{$pricing->code}" data-pricing-id="{$pricing->pricing_id}" cycle="annually" {if $pricing->annually gte 0} checked="checked" {/if} type="checkbox">
                                                            </td>
                                                        {/if}
                                                        {if in_array('24',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input class="pricingtgl" data-pricing-id="{$pricing->pricing_id}" currency="{$pricing->code}" {if $pricing->biennially gte 0} checked="checked" {/if} cycle="biennially" type="checkbox">
                                                            </td>
                                                        {/if}
                                                        {if in_array('36',$product->apiConfig->availablePeriods)}
                                                            <td style="display: table-cell;" class="prod-pricing-recurring">
                                                                <input class="pricingtgl" data-pricing-id="{$pricing->pricing_id}" currency="{$pricing->code}" cycle="triennially" {if $pricing->triennially gte 0} checked="checked" {/if} type="checkbox">
                                                            </td>
                                                        {/if}
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </td>
                        </tr>
                    </table>
                    <input type="submit" name="saveProduct" class="btn btn-success" value="{$MGLANG->T('save')}" />
                </form>
            {/foreach}
        </div>
    </div>
    <script>
        {literal}
            function manageconfigoptions(id) {
               window.open('configproductoptions.php?manageoptions=true&cid='+id,'configoptions','width=900,height=500,scrollbars=yes');
            }
            $(document).ready(function () {
                
                $('.mg-js-create-oprions').on('click', function() {
                    $('#createConfOptionsFormId').val( $(this).data('id'));
                    $('#createConfOptionsFormName').val( $(this).data('name'));
                    $('#createConfOptionsForm').submit();
                });

                jQuery('.buttons-container').on('click', '.disable-product', function () {
                    var productId = $(this).data('product-id');
                    button = $(this);
                    JSONParser.request('disableProduct', {productId: productId}, function (data) {
                        if (data.success) {
                            switchButtons('enToDis', button, productId);
                        } else {
                            switchButtons('disToEn', button, productId);
                        }
                    }, false);
                });


                jQuery('.buttons-container').on('click', '.enable-product', function () {
                    var productId = $(this).data('product-id');
                    button = $(this);
                    JSONParser.request('enableProduct', {productId: productId}, function (data) {
                        if (data.success) {
                            switchButtons('disToEn', button, productId);
                        } else {
                            switchButtons('enToDis', button, productId);
                        }
                    }, false);
                });


                function switchButtons(type, container, productId) {
                    if (type == 'enToDis') {
                        container.parent().html('<button type="button" data-product-id="' + productId + '" class="btn btn-danger disable-product">{/literal}{$MGLANG->T('statusDisable')}{literal}</button>');
                    } else {
                        container.parent().html('<button type="button" data-product-id="' + productId + '" class="btn btn-success enable-product">{/literal}{$MGLANG->T('statusEnable')}{literal}</button>');
                    }
                }

                function initFields() {
                    $(".pricingtgl").each(function (no, item) {

                        if ($(item).is(":checked")) {

                        } else {

                            var cycle = $(item).attr("cycle");
                            var currency = $(item).attr("currency");
                            var pricingId = $(item).data('pricing-id');

                            $("#pricing_" + currency + "_" + cycle + "_" + pricingId).val("-1.00").hide();
                            $("#setup_" + currency + "_" + cycle + "_" + pricingId).hide();
                        }
                    });
                }
                initFields();

                function disablePrices(element) {
                    element.attr("disabled", true);
                    element.addClass('disabled');
                    element.prop('checked', false);
                }

                function enablePrices(element) {
                    element.removeAttr('disabled')
                    element.removeClass('disabled');
                }
                                
                function showOneTime(element, type) {    
                    //element.find('.prod-pricing-recurring').css('display', 'none');   
                    element.find("input[cycle='monthly']").removeClass('disabled');
                    element.find("input[cycle='monthly']").removeAttr('disabled');
                    
                    if(element.find("input[cycle='monthly']").hasClass('monthly')) {   
                        if(type !== 'free') {
                            $(element).find("input[cycle='monthly']").prop('checked', false);
                        }
                        element.find("input[cycle='monthly']").removeClass('monthly');
                    }                    
                    element.find("input[cycle='monthly']").addClass('onetime');
                }
                
                function hideOneTime(element, type) {      
                    //element.find('.prod-pricing-recurring').removeAttr('style'); 
                    /*if(parseFloat(element.find('.prod-pricing-monthly-onetime').find('input')[1].value) < 0) {                        
                        element.find("input[cycle='monthly']").prop('checked', false);
                    } */  
                    if(element.find("input[cycle='monthly']").hasClass('onetime') || type === null) {                        
                        $(element).find("input[cycle='monthly']").prop('checked', false);                                           
                        element.find("input[cycle='monthly']").removeClass('onetime');
                    }  
                    
                    element.find("input[cycle='monthly']").addClass('monthly');
                }

                function setAsOneTime(select, type = null) {
                    var pc = select.closest('.product-container');                    
                    //disablePrices(pc.find("input[cycle='monthly']"));
                    showOneTime(pc, type);
                    disablePrices(pc.find("input[cycle='quarterly']"));
                    disablePrices(pc.find("input[cycle='semiannually']"));
                    disablePrices(pc.find("input[cycle='annually']"));
                    disablePrices(pc.find("input[cycle='biennially']"));
                    disablePrices(pc.find("input[cycle='triennially']"));
                    initFields();
                }

                function setAsNonOneTime(select, type = null) {
                    var pc = select.closest('.product-container');                
                    //enablePrices(pc.find("input[cycle='monthly']"));                    
                    hideOneTime(pc, type);    
                    enablePrices(pc.find("input[cycle='quarterly']"));
                    enablePrices(pc.find("input[cycle='semiannually']"));
                    enablePrices(pc.find("input[cycle='annually']"));
                    enablePrices(pc.find("input[cycle='biennially']"));
                    enablePrices(pc.find("input[cycle='semiannually']"));
                    enablePrices(pc.find("input[cycle='triennially']"));
                    initFields();
                }


                $(".pricingtgl").click(function () {
                    var cycle = $(this).attr("cycle");
                    var currency = $(this).attr("currency");
                    var pricingId = $(this).data('pricing-id');

                    console.log($(this).is(":checked"));
                    console.log("#pricing_" + currency + "_" + cycle + "_" + pricingId);

                    if ($(this).is(":checked")) {
                        $("#pricing_" + currency + "_" + cycle + "_" + pricingId).val("0.00").show();
                        $("#setup_" + currency + "_" + cycle + "_" + pricingId).show();
                    } else {
                        $("#pricing_" + currency + "_" + cycle + "_" + pricingId).val("-1.00").hide();
                        $("#setup_" + currency + "_" + cycle + "_" + pricingId).hide();
                    }
                });

                function showHidePricing(select) {
                    var productId = select.data('id');                    
                    var type = select.val();
                    if (type === 'free') {
                        setAsNonOneTime(select);
                        $('#mg-js-pricing-group-' + productId).hide();
                    } else if (type === 'onetime') {
                        setAsOneTime(select, type);
                        $('#mg-js-pricing-group-' + productId).show();
                    } else {
                        setAsNonOneTime(select, type);
                        $('#mg-js-pricing-group-' + productId).show();
                    }
                }
                
                function showHidePeriodSelection(select) {
                    var productId = select.data('id');                    
                    var type = select.val();
                    if (type === 'onetime') {  
                       $(select).parents('td').find('.maxMonths').addClass('hidden');
                        $('input[name="product[' + productId + '][configoption2]"]').addClass('hidden').prop('disabled', true);
                        $('select[name="product[' + productId + '][configoption2]"]').removeClass('hidden').prop('disabled', false);
                    } else {
                        $(select).parents('td').find('.maxMonths').removeClass('hidden');
                        $('input[name="product[' + productId + '][configoption2]"]').removeClass('hidden').prop('disabled', false);
                        $('select[name="product[' + productId + '][configoption2]"]').addClass('hidden').prop('disabled', true);
                    }
                }

                $('.mg-js-pricing-select').each(function () {
                    showHidePricing($(this));
                });

                $('.mg-js-pricing-select').on('change', function () {                    
                    showHidePricing($(this), true);
                    showHidePeriodSelection($(this));
                });
            });
        {/literal}
    </script>
{else}
    <div class="alert alert-info">
        No products found - to add go to `Products Creator` page.
    </div>
{/if}
