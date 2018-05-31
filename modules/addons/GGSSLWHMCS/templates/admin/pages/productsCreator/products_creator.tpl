{if $formError}
    <div class="col-lg-12">
        <div class="note note-danger">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
            <p><strong>{$formError}</strong></p>
        </div>
    </div>
    <div class="clearfix"></div>
{/if}  

<div class="panel panel-default">
    <div class="panel-heading">{$MGLANG->T('singleProductCreator')}</div>
    <div class="panel-body">

        <form action="" method="post" class="form-horizontal">

            <div class="form-group">
                <label class="control-label col-sm-2">{$MGLANG->T('goGetSSLProduct')}</label>
                <div class="col-sm-10">
                    <select name="configoption1" class="form-control" id="api_product">
                        {foreach from=$apiProducts item=product}
                            <option data-peroids="{','|implode:$product->getPeriods()}" data-is_multidomain="{$product->san_enabled}" value="{$product->id}">{$product->product}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-sm-2">{$MGLANG->T('productName')}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="name" value="" required/>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">{$MGLANG->T('productGroup')}</label>
                <div class="col-sm-10">
                    <select name="gid" class="form-control">
                        {foreach from=$productGroups item=productGroup}
                            <option value="{$productGroup->id}">{$productGroup->name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">{$MGLANG->T('months')}</label>
                <div class="col-sm-10">
                    <select name="configoption2" class="form-control" id="months">

                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">{$MGLANG->T('enableSans')}</label>
                <div class="col-sm-10">
                    <input type="checkbox" class="" name="configoption3" value="on" style="margin-top: 10px;" id="enable_sans">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">{$MGLANG->T('includedSans')}</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="configoption4" value="on"  id="included_sans">
                </div>
            </div>  

            <div class="form-group">
                <label class="control-label col-sm-2">{$MGLANG->T('pricing')}</label>
                <div class="col-sm-10">
                    <div class="product_prices">
                        <table class="table">
                            <tbody>
                                <tr style="text-align:center;font-weight:bold">
                                    <td></td>
                                    <td></td>
                                    <td class="pricing_1">{$MGLANG->T('pricingMonthly')}</td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_3">{$MGLANG->T('pricingQuarterly')}</td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_6">{$MGLANG->T('pricingSemiAnnually')}</td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_12">{$MGLANG->T('pricingAnnually')}</td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_24">{$MGLANG->T('pricingBiennially')}</td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_36">{$MGLANG->T('pricingTriennially')}</td>
                                </tr>
                                {foreach from=$currencies item=currency}
                                <input type="hidden" name="currency[{$currency->id}][currency]" value="{$currency->id}" required/>
                                <tr style="text-align:center" bgcolor="#ffffff" currency="{$currency->code}">
                                    <td rowspan="3" bgcolor="#efefef"><b>{$currency->code}</b></td>
                                    <td>{$MGLANG->T('pricingSetupFee')}</td>
                                    <td class="pricing_1" align="center">
                                        <input name="currency[{$currency->id}][msetupfee]" id="setup_{$currency->code}_monthly" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_3" align="center">
                                        <input name="currency[{$currency->id}][qsetupfee]" id="setup_{$currency->code}_quarterly" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_6" align="center">
                                        <input name="currency[{$currency->id}][ssetupfee]" id="setup_{$currency->code}_semiannually" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_12" align="center">
                                        <input name="currency[{$currency->id}][asetupfee]" id="setup_{$currency->code}_annually" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_24" align="center">
                                        <input name="currency[{$currency->id}][bsetupfee]" id="setup_{$currency->code}_biennially" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_36" align="center">
                                        <input name="currency[{$currency->id}][tsetupfee]" id="setup_{$currency->code}_triennially" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                </tr>
                                <tr style="text-align:center" bgcolor="#ffffff" currency="{$currency->code}">
                                    <td>{$MGLANG->T('pricingPrice')}</td>
                                    <td class="pricing_1" align="center">
                                        <input name="currency[{$currency->id}][monthly]" id="pricing_{$currency->code}_monthly" size="10" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_3" align="center">
                                        <input name="currency[{$currency->id}][quarterly]" id="pricing_{$currency->code}_quarterly" size="10" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_6" align="center">
                                        <input name="currency[{$currency->id}][semiannually]" id="pricing_{$currency->code}_semiannually" size="10" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_12" align="center">
                                        <input name="currency[{$currency->id}][annually]" id="pricing_{$currency->code}_annually" size="10" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_24" align="center">
                                        <input name="currency[{$currency->id}][biennially]" id="pricing_{$currency->code}_biennially" size="10" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_36" align="center">
                                        <input name="currency[{$currency->id}][triennially]" id="pricing_{$currency->code}_triennially" size="10" value="0.00" style="" class="form-control input-inline input-100 text-center" type="text">
                                    </td>
                                </tr>
                                <tr style="text-align:center" bgcolor="#ffffff">
                                    <td>{$MGLANG->T('pricingEnable')}</td>
                                    <td class="pricing_1">
                                        <input class="pricingtgl" currency="{$currency->code}" data-pricing-id="{$currency->id}" cycle="monthly" type="checkbox">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_3">
                                        <input class="pricingtgl" currency="{$currency->code}" cycle="quarterly" type="checkbox">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_6">
                                        <input class="pricingtgl" currency="{$currency->code}" data-pricing-id="{$currency->pricing_id}" cycle="semiannually" type="checkbox">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_12">
                                        <input class="pricingtgl" currency="{$currency->code}" cycle="annually" type="checkbox">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_24">
                                        <input class="pricingtgl" currency="{$currency->code}" cycle="biennially" type="checkbox">
                                    </td>
                                    <td style="display: table-cell;" class="prod-pricing-recurring pricing_36">
                                        <input class="pricingtgl" currency="{$currency->code}" cycle="triennially" type="checkbox">
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <input type="submit" name="createSingle" class="btn btn-success" value="{$MGLANG->T('saveSingle')}" />

        </form>

    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">{$MGLANG->T('multipleProductCreator')}</div>
    <div class="panel-body">
        <form action="" method="post" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-sm-2">{$MGLANG->T('productGroup')}</label>
                <div class="col-sm-10">
                    <select name="gid" class="form-control">
                        {foreach from=$productGroups item=productGroup}
                            <option value="{$productGroup->id}">{$productGroup->name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <input type="submit" name="createMass" class="btn btn-success" value="{$MGLANG->T('saveMultiple')}" />  
        </form>
    </div>
</div>

<script>
    {literal}
        $(document).ready(function () {

            $(".pricingtgl").each(function (no, item) {
                if ($(item).is(":checked")) {

                } else {
                    var cycle = $(item).attr("cycle");
                    var currency = $(item).attr("currency");

                    $("#pricing_" + currency + "_" + cycle).val("-1.00").hide();
                    $("#setup_" + currency + "_" + cycle).hide();
                }
            });


            $(".pricingtgl").click(function () {
                var cycle = $(this).attr("cycle");
                var currency = $(this).attr("currency");
                var pricingId = $(this).data('pricing-id');

                console.log($(this).is(":checked"));
                console.log("#pricing_" + currency + "_" + cycle + "_" + pricingId);

                if ($(this).is(":checked")) {
                    $("#pricing_" + currency + "_" + cycle).val("0.00").show();
                    $("#setup_" + currency + "_" + cycle).show();
                } else {
                    $("#pricing_" + currency + "_" + cycle).val("-1.00").hide();
                    $("#setup_" + currency + "_" + cycle).hide();
                }
            });

        });

        $(document).ready(function () {

            var enableSansInput = $('#enable_sans'),
                    sansCountInput = $('#included_sans'),
                    apiProductInput = $('#api_product'),
                    monthsInput = $('#months');

            function configureSansCountInput(element) {
                if (element.is(":checked")) {
                    sansCountInput.val(0).attr('disabled', false);
                } else {
                    sansCountInput.val(0).attr('disabled', true);
                }
            }

            function configureSansEnableInput(element) {
                if (element.find(":selected").data('is_multidomain') === 0) {
                    enableSansInput.attr('checked', false).attr('disabled', true).trigger('change');
                } else {
                    enableSansInput.attr('checked', true).attr('disabled', false).trigger('change');
                }
            }

            function getOptionInputHtml(conf) {
                return '<option value="' + conf.value + '">' + conf.name + '</option>';
            }

            function buildMonthsInput(element) {
                var peroids = element.find(":selected").data('peroids'), options = '';

                if (typeof peroids === 'undefined') {
                    return;
                }

                if (typeof peroids === 'number') {
                    peroids = peroids.toString();
                }

                peroids = peroids.split(',');                
                for (var i = 0; i < peroids.length; i++) {
                    options = options + getOptionInputHtml({value: peroids[i], name: peroids[i]});
                }
                monthsInput.html(options);                
                changePrincingOptions(peroids)
                
            }
            function changePrincingOptions(peroids){
                var AllPeriodsInMonths = ["1", "3" , "6", "12", "24", "36"];
                for (var i = 0; i < AllPeriodsInMonths.length; i++) {
                    if(jQuery.inArray( AllPeriodsInMonths[i], peroids ) == -1) {
                        $('.pricing_' + AllPeriodsInMonths[i]).hide();
                    } else {
                         $('.pricing_' + AllPeriodsInMonths[i]).show();
                    }
                }
            }

            buildMonthsInput(apiProductInput);
            configureSansEnableInput(apiProductInput);
            configureSansCountInput(enableSansInput);

            enableSansInput.on('change', function () {
                configureSansCountInput($(this));
            });

            apiProductInput.on('change', function () {
                configureSansEnableInput($(this));
                buildMonthsInput($(this));
            });
            $('#api_product').bind('change', function(){
                var options = $('#months option');
                var periods = $.map(options ,function(option) {
                    return option.value;
                });
                
                changePrincingOptions(periods)
            })
        });
    {/literal}
</script>
