<?php

use \MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule as DB;

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

add_hook('ClientAreaPage', 1, function ($params)
{
    $loaderdir = false;
    if(file_exists(__DIR__.DS.'Loader.php'))
    {
        $loaderdir = __DIR__.DS.'Loader.php';
    } 
    else if(file_exists(getcwd().DS.'modules'.DS.'servers'.DS.'SSLCENTERWHMCS'.DS.'Loader.php'))
    {
        $loaderdir = getcwd().DS.'modules'.DS.'servers'.DS.'SSLCENTERWHMCS'.DS.'Loader.php';
    }
    if($loaderdir === false)
    {
        return;
    }
    
    require_once $loaderdir;
    new main\Loader();
    $activator = new main\eServices\provisioning\Activator();
    $activator->run();

    if (isset($params['templatefile']))
    {
        global $smarty;
        switch ($params['templatefile'])
        {
            case 'configureproduct':
                $product = DB::table('tblproducts')->where('id', $params['productinfo']['pid'])->where('servertype', 'SSLCENTERWHMCS')->first();
                $includedsan = $product->configoption4;
                $includedsanwildcard = $product->configoption8;
                
                $txtincluded = '';
                
                if($includedsan > 0)
                {
                    $txt = sprintf (\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('additionalSingleDomainInfo'), $includedsan);
                    $txtincluded .= '<p>'.$txt.'</p>';
                }
                if($includedsanwildcard > 0)
                {
                    $txt = sprintf (\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('additionalSingleDomainWildcardInfo'), $includedsanwildcard);
                    $txtincluded .= '<p>'.$txt.'</p>';
                }
                $smarty->assign('txtincluded', $txtincluded);
                break;
            case 'clientareaproductdetails':
                $product = DB::table('tblproducts')->where('id', $params['pid'])->where('servertype', 'SSLCENTERWHMCS')->first();
                $includedsan = $product->configoption4;
                $includedsanwildcard = $product->configoption8;
                
                $txtincluded = '';
                
                if($includedsan > 0)
                {
                    $txt = sprintf (\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->absoluteT('additionalSingleDomainInfo'), $includedsan);
                    $txtincluded .= '<p>'.$txt.'</p>';
                }
                if($includedsanwildcard > 0)
                {
                    $txt = sprintf (\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->absoluteT('additionalSingleDomainWildcardInfo'), $includedsanwildcard);
                    $txtincluded .= '<p>'.$txt.'</p>';
                }                
                $smarty->assign('txtincluded', $txtincluded);
                $smarty->assign('customSSLCenterAssetsURL', main\Server::I()->getAssetsURL());
                $smarty->assign('customProductDetailsIcon', true);
                break;
            case 'configuressl-stepone':
                if (isset($_GET['cert']))
                {
                    $r = DB::table('tblsslorders')->where(DB::raw('md5(id)'), '=', $_GET['cert'])->first();
                    if ($r AND $r->module == main\Server::I()->configuration()->systemName)
                    {
                        $smarty->assign('customBackToServiceButton', true);
                        $smarty->assign('customBackToServiceButtonLang', \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('addonCA', 'customBackToServiceButtonLang'));
                    }
                }
                break;
        }
    }
});

add_hook('ClientAreaPageUpgrade', 1, function($vars)
{
    $step = filter_input(INPUT_POST, 'step', FILTER_VALIDATE_INT);

    if ($vars['type'] == 'configoptions' && $step == 2)
    {
        $promodata = validateUpgradePromo($vars['promocode']);

        //calculate the percentage value
        $serviceData = DB::table('tblhosting')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->select('tblhosting.id', 'tblhosting.packageid', 'tblhosting.domain', 'tblhosting.nextduedate', 'tblhosting.billingcycle', 'tblproducts.servertype')
            ->where('tblhosting.userid', '=', $vars['clientsdetails']['userid'])
            ->where('tblhosting.id', '=', $vars['id'])
            ->first();

        if ($serviceData->servertype == 'SSLCENTERWHMCS')
        {
            $nextduedate  = $serviceData->nextduedate;
            $billingcycle = $serviceData->billingcycle;

            $year            = substr($nextduedate, 0, 4);
            $month           = substr($nextduedate, 5, 2);
            $day             = substr($nextduedate, 8, 2);
            $cyclemonths     = getBillingCycleMonths($billingcycle);
            $prevduedate     = date("Y-m-d", mktime(0, 0, 0, $month - $cyclemonths, $day, $year));
            $totaldays       = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
            $todaysdate      = date("Ymd");
            $todaysdate      = strtotime($todaysdate);
            $nextduedatetime = strtotime($nextduedate);
            $days            = round(($nextduedatetime - $todaysdate) / 86400);

            if( $days < 0 ) 
            {
                $days = $totaldays;
            }

            $percentage       = $days / $totaldays;
            $upgrades         = $vars['upgrades'];
            $newUpgrades      = [];
            $subtotal         = 0;
            $configoptions    = getCartConfigOptions($serviceData->packageid, $vars['configoptions'], $serviceData->billingcycle);
            $oldconfigoptions = getCartConfigOptions($serviceData->packageid, "", $billingcycle, $serviceData->id);
            $discount = 0;

            foreach ($upgrades as $upgrade)
            {
                foreach ($configoptions as $key2 => $configoption)
                {
                    if ($configoption['optionname'] == $upgrade['configname'])
                    {
                        $testPrice           = $configoptions[$key2]['selectedrecurring'] - $oldconfigoptions[$key2]['selectedrecurring'];
                        $priceWithPercentage = $testPrice * $percentage;
                        $configid            = $configoption['id'];
                    }
                }

                $newPrice = floatval($priceWithPercentage) / floatval($percentage);
                $subtotal += number_format($newPrice, 2);

                if( $GLOBALS["qualifies"] && 0 < $newPrice && (!count($promodata['configoptions']) || in_array($configid, $promodata['configoptions'])) ) 
                {
                    $itemdiscount = ($promodata["discounttype"] == "Percentage" ? round($newPrice * ($promodata["value"] / 100), 2) : ($newPrice < $promodata["value"] ? $newPrice : $promodata["value"]));
                    $discount += $itemdiscount;
                }

                $upgrade['price'] = formatCurrency($newPrice);
                $newUpgrades[]    = $upgrade;
            }

            $newSubtotal       = formatCurrency($subtotal - $discount);
            $subtotalToCalcTax = $newSubtotal->toNumeric();

            if( $vars['taxenabled'] ) 
            {
                global $CONFIG;

                if( $vars['taxrate'] ) 
                {
                    if( $CONFIG["TaxType"] == "Inclusive" ) 
                    {
                        $inctaxrate         = 1 + $vars['taxrate'] / 100;
                        $tempsubtotal       = $subtotalToCalcTax;
                        $subtotalToCalcTax  = $subtotalToCalcTax / $inctaxrate;
                        $tax                = $tempsubtotal - $subtotalToCalcTax;
                    }
                    else
                    {
                        $tax = $subtotalToCalcTax * $vars['taxrate'] / 100;
                    }
                }

                if( $vars['taxrate2'] ) 
                {
                    $tempsubtotal = $subtotalToCalcTax;
                    if( $CONFIG["TaxL2Compound"] ) 
                    {
                        $tempsubtotal += $tax;
                    }

                    if( $CONFIG["TaxType"] == "Inclusive" ) 
                    {
                        //var_dump($tempsubtotal);
                        $inctaxrate        = 1 + $vars['taxrate'] / 100;
                        $subtotalToCalcTax = $tempsubtotal / $inctaxrate;
                        $tax2              = $tempsubtotal - $subtotalToCalcTax;
                    }
                    else
                    {
                        $tax2 = $tempsubtotal * $vars['taxrate2'] / 100;
                    }
                }

                $tax         = format_as_currency(round($tax, 2));
                $tax2        = format_as_currency(round($tax2, 2));
                $newSubtotal = formatCurrency($subtotalToCalcTax);
            }

            $newTotal = formatCurrency($newSubtotal->toNumeric() + $tax + $tax2);

            return [
                'upgrades' => $newUpgrades,
                'subtotal' => $newSubtotal,
                'total'    => $newTotal,
                'tax'      => formatCurrency($tax),
                'tax2'     => formatCurrency($tax2),
                'discount' => formatCurrency($discount)
            ];
        }
    }
});

add_hook('InvoiceCreationPreEmail', 1, function($vars)
{
    //get invoice data
    $command  = 'GetInvoice';
    $postData = ['invoiceid' => $vars['invoiceid']];

    $results = localAPI($command, $postData);

    if ($results['result'] == 'success')
    {
        $invoiceItem          = $results['items']['item'];
        $userID               = $results['userid'];
        $invoiceID            = $results['invoiceid'];
        $newPrices            = [];
        $itemdescriptionArray = [];
        $itemamountArray      = [];
        $itemtaxedArray       = [];

        foreach ($invoiceItem as $item)
        {
            if ($item['type'] == 'Upgrade')
            {
                //check if this is config options update and get the service id
                $itemID    = $item['id'];
                $upgradeID = $item['relid'];

                $upgradeData = DB::table('tblupgrades')
                    ->where('id', '=', $upgradeID)
                    ->first();

                if ($upgradeData->type == 'configoptions')
                {
                    $serviceID   = $upgradeData->relid;
                    $serviceData = DB::table('tblhosting')
                        ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
                        ->select('tblhosting.id', 'tblhosting.orderid', 'tblhosting.packageid', 'tblhosting.domain', 'tblhosting.nextduedate', 'tblhosting.billingcycle', 'tblproducts.servertype')
                        ->where('tblhosting.userid', '=', $userID)
                        ->where('tblhosting.id', '=', $serviceID)
                        ->first();

                    if ($serviceData->servertype == 'SSLCENTERWHMCS')
                    {
                        $isGoGetSSLProduct = true;
                        $upgradesData      = DB::table('tblupgrades')
                            ->select('recurringchange', 'originalvalue')
                            ->where('id', '=', $item['relid'])
                            ->first();

                        $configOptionID = explode('=>', $upgradesData->originalvalue)[0];

                        $newPrice                      = formatCurrency(floatval($upgradesData->recurringchange));
                        $newPrices[$configOptionID]    = $newPrice->toNumeric();
                        $itemdescriptionArray[$itemID] = $item['description'];
                        $itemamountArray[$itemID]      = $newPrice->toNumeric();
                        $itemtaxedArray[$itemID]       = $item['taxed'];
                    }
                }
            }
            elseif($item['type'] == '' && !$item['relid'] && $isGoGetSSLProduct)
            {
                $promoItemID = $item['id'];
                $description = $item['description'];
                $promoTaxed  = $item['taxed'];
                $tmp1        = explode(':', $description);
                $tmp2        = explode('-', $tmp1[1]);
                $promocode   = trim($tmp2[0]);
                
                if ($promocode && $promocode != '' && $promocode != 'The promotion code entered does not exist' && $promoItemID != '' && !empty($newPrices))
                {
                    $promodata    = validateUpgradePromo($promocode);
                    $itemdiscount = 0;

                    foreach ($newPrices as $configid => $price )
                    {
                        if (in_array($configid, $promodata['configoptions']))
                        {
                            $itemdiscount += ($promodata["discounttype"] == "Percentage" ? round($price * ($promodata["value"] / 100), 2) : ($price < $promodata["value"] ? $newPrice : $promodata["value"]));
                        }
                    }

                    $itemdescriptionArray[$promoItemID] = $description;
                    $itemamountArray[$promoItemID]      = -1 * abs($itemdiscount);
                    $itemtaxedArray[$promoItemID]       = $promoTaxed;
                    $newPrices                          = [];
                }
            }
        }

        if (!empty($itemdescriptionArray) && !empty($itemamountArray) && !empty($itemtaxedArray))
        {
            $command2  = 'UpdateInvoice';
            $postData2 = [
                'invoiceid'       => $invoiceID,
                'itemdescription' => $itemdescriptionArray,
                'itemamount'      => $itemamountArray,
                'itemtaxed'       => $itemtaxedArray
            ];

            $results = localAPI($command2, $postData2);
            
            $lastOrder = DB::table('tblorders')->where('userid', $userID)->orderBy('id', 'DESC')->first();
            
            DB::table('tblorders')->where('id', $lastOrder->id)->update(array(
                'amount' => reset($itemamountArray)
            ));

            if (!$results['result'] == 'success')
            {
                logModuleCall(
                    'SSLCENTERWHMCS',
                    $command2,
                    $postData2,
                    $results
                );
            }
        }
    }
    else
    {
        logModuleCall(
            'SSLCENTERWHMCS',
            $command,
            $postData,
            $results
        );
    }
});

add_hook('AdminAreaFooterOutput', 1, function($vars)
{
if ($vars['filename'] == 'clientsservices' && $_GET['userid'] && $_GET['id']) {
    $hosting = DB::table('tblhosting')
    ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
    ->where('tblhosting.id', $_GET['id'])
    ->first();
    if($hosting->servertype != 'SSLCENTERWHMCS') return;
    return <<<JS

<script>
$(function(){
    $('#btnCreate').hide();
    
    var statushtrml = $('select[name="domainstatus"]').parent().html();
    var statustext = $('select[name="domainstatus"]').parent('.fieldarea').prev().text();

    $('#inputDedicatedip').parent('.fieldarea').prev().hide();
    $('#inputDedicatedip').parent('.fieldarea').hide();
    $('#inputUsername').parent('.fieldarea').prev().hide();
    $('#inputUsername').parent('.fieldarea').hide();
    $('#inputPassword').parent('.fieldarea').prev().hide();
    $('#inputPassword').parent('.fieldarea').hide();
    
    
    $('select[name="domainstatus"]').parent('.fieldarea').prev().hide();
    $('select[name="domainstatus"]').parent('.fieldarea').hide();
    $('select[name="domainstatus"]').remove();
    
    $('select[name="server"]').parent('.fieldarea').prev().html(statustext);
    $('select[name="server"]').parent('.fieldarea').html(statushtrml);
});
</script>

JS;
}
});
