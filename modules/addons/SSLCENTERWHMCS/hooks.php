<?php
use Illuminate\Database\Capsule\Manager as Capsule;

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

add_hook("ClientAreaPage",1 ,function($vars) {

    global $CONFIG;
    
    if(substr($CONFIG['Version'],0,1) == '8')
    {
        if(isset($_GET['id'])) return true;

        $urldata = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($urldata['query'], $query);

        $serviceid = null;

        foreach($query as $key => $value)
        {
            unset($query[$key]);
            $query[str_replace('amp;', '', $key)] = $value;
        }

        if (strpos($urldata['path'], 'clientsservices.php') !== false) {

            if(isset($query['id']) && !empty($query['id']))
            {
                $serviceid = $query['id'];
            }
            if(isset($query['productselect']) && !empty($query['productselect']))
            {
                $serviceid = $query['productselect'];
            }
            if($serviceid === null)
            {
                $service = Capsule::table('tblhosting')->select(['tblhosting.id as serviceid'])
                         ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                        ->where('tblhosting.userid', $query['userid'])
                        ->where('tblproducts.servertype', 'SSLCENTERWHMCS')
                        ->first();
                $serviceid = $service->serviceid;
            }

            $service = Capsule::table('tblhosting')->where('id', $serviceid)->first();

            if(isset($service->packageid) && !empty($service->packageid))
            {
                $product = Capsule::table('tblproducts')->where('id', $service->packageid)->where('servertype', 'SSLCENTERWHMCS')->first();

                if(isset($product->id))
                {
                    redir('action=productdetails&id='.$serviceid, 'clientarea.php');
                }
            }
        }
    }
});

add_hook('ClientAreaPage', 99999, function($params) { 
    
    if (strpos($_SERVER['SCRIPT_NAME'], 'viewinvoice.php') !== false && !empty($_GET['id']) && $_POST['applycredit'] == true) {
        $invoice = Capsule::table('tblinvoices')->where('id', $_GET['id'])->first();
        if(isset($invoice->status) && $invoice->status == 'Payment Pending')
        {
            $command = 'UpdateInvoice';
            $postData = array(
                'invoiceid' =>  $_GET['id'],
                'status' => 'Unpaid',
            );

            localAPI($command, $postData);

            $command = 'ApplyCredit';
            $postData = array(
                'invoiceid' => $_GET['id'],
                'amount' => $_POST['creditamount'],
            );
            $results = localAPI($command, $postData);

            if(isset($results['invoicepaid']) && $results['invoicepaid'] == 'true')
            {
                run_hook("InvoicePaid", array( "invoiceid" => $_GET['id'] ));
            }

            redir('id='.$_GET['id'], 'viewinvoice.php');
        }
    }
    
    if($params['filename'] == 'viewinvoice' && $params['status'] == 'Payment Pending')
    {
        $invoice = new WHMCS\Invoice($params['invoiceid']);
        $paymentbutton = $invoice->getPaymentLink();

        $userID = $_SESSION['uid'];
        $credits = Capsule::table('tblcredit')->where('clientid', $userID)->get();
        
        $invoiceDB = Capsule::table('tblinvoices')->where('id', $params['invoiceid'])->first();
        
        $amount = 0;
        foreach($credits as $credit)
        {
            $amount = $amount + $credit->amount;
        }
        
        $applyCredit = false;
        if($amount > 0)
        {
            $applyCredit = true;
        }
 
        return [
            'totalcredit' => formatCurrency($amount),
            'creditamount' => $invoiceDB->total,
            'manualapplycredit' => $applyCredit,
            'paymentbutton' => $paymentbutton,
            'statuslocale' => 'Unpaid',
            'status' => 'Unpaid'
        ];
    }
});

add_hook('ClientAreaHeadOutput', 1, function($params)
{
    $show = false;

    if ($params['filename'] === 'configuressl' && $params['loggedin'] == '1' && isset($_REQUEST['action']) && $_REQUEST['action'] === 'generateCsr')
    {
        $GenerateCsr = new MGModule\SSLCENTERWHMCS\eServices\provisioning\GenerateCSR($params, $_POST);
        echo $GenerateCsr->run();
        die();
    }
    if ($params['templatefile'] === 'clientareacancelrequest')
    {
        try
        {
            $service = \WHMCS\Service\Service::findOrFail($params['id']);
            if ($service->product->servertype === 'SSLCENTERWHMCS')
            {
                $show = true;
            }
        }
        catch (Exception $exc)
        {
            
        }
    }
    elseif ($params['modulename'] === 'SSLCENTERWHMCS')
    {
        $show = true;
    }
    if (!$show)
    {
        return '';
    }


    $url = $_SERVER['PHP_SELF'] . '?action=productdetails&id=' . $_GET['id'];

    return '<script type="text/javascript">
        $(document).ready(function () {
            var information = $("#Primary_Sidebar-Service_Details_Overview-Information"),
                    href = information.attr("href");
            if (typeof href === "string") {
                information.attr("href", "' . $url . '");
                information.removeAttr("data-toggle");
            }
        });
    </script>';
});
add_hook('ClientLogin', 1, function($vars)
{

    if (isset($_REQUEST['redirectToProductDetails'], $_REQUEST['serviceID']) && $_REQUEST['redirectToProductDetails'] === 'true' && is_numeric($_REQUEST['serviceID']))
    {
        $ca = new \WHMCS_ClientArea();
        if ($ca->isLoggedIn())
        {
            header('Location: clientarea.php?action=productdetails&id=' . $_REQUEST['serviceID']);
            die();
        }
    }
});

add_hook('InvoicePaid', 1, function($vars)
{
    require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'init.php';
    require_once __DIR__.DS.'Loader.php';

    new \MGModule\SSLCENTERWHMCS\Loader();
    \MGModule\SSLCENTERWHMCS\Addon::I(true);
    
    $invoiceGenerator = new \MGModule\SSLCENTERWHMCS\eHelpers\Invoice();
    
    $invoiceInfo = $invoiceGenerator->getInvoiceCreatedInfo($vars['invoiceid']);
    if (!empty($invoiceInfo)) {
        $command = 'SendEmail';
        $postData = array(
            'id'          => $invoiceInfo['service_id'],
            'messagename' => \MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::RENEWAL_TEMPLATE_ID
        );
        $adminUserName = \MGModule\SSLCENTERWHMCS\eHelpers\Admin::getAdminUserName();
        $results = localAPI($command, $postData, $adminUserName);
        $resultSuccess = $results['result'] == 'success';
        if (!$resultSuccess)
        {
            \MGModule\SSLCENTERWHMCS\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS Notifier: Error while sending customer notifications (service ' . $invoiceInfo['service_id'] . '): ' . $results['message'], 0);
        }
    }
    
    $apiConf           = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
    if(isset($apiConf->renew_new_order) && $apiConf->renew_new_order == '1')
    {
        if (!empty($invoiceInfo)) {
            modulecallfunction("Renew", $invoiceInfo['service_id']);
        }
        return true;
    }
    $invoiceGenerator->invoicePaid($vars['invoiceid']);
});


/*
 *
 * assign ssl summary stats to clieat area page 
 * 
 */

function SSLCENTER_displaySSLSummaryStats($vars)
{
    
    if (isset($vars['filename'], $vars['templatefile']) && $vars['filename'] == 'clientarea' && $vars['templatefile'] == 'clientareahome')
    {
        try
        {
            require_once __DIR__.DS.'Loader.php';
            new \MGModule\SSLCENTERWHMCS\Loader();

            GLOBAl $smarty;

            \MGModule\SSLCENTERWHMCS\Addon::I(true);

            $apiConf           = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
            $displaySSLSummary = $apiConf->display_ca_summary;
            if (!(bool) $displaySSLSummary)
                return;

            $sslSummaryIntegrationCode = '';

            $titleLang       = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'title');
            $totalLang       = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'total');
            $unpaidLang      = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'unpaid');
            $processingLang  = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'processing');
            $expiresSoonLang = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'expiresSoon');
            $viewAll         = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('viewAll');

            //get ssl statistics
            $sslSummaryStats = new MGModule\SSLCENTERWHMCS\eHelpers\SSLSummary($_SESSION['uid']);

            $totalOrders = $sslSummaryStats->getTotalSSLOrdersCount();

            if ((int) $totalOrders == 0)
                return '';

            $unpaidOrders      = $sslSummaryStats->getUnpaidSSLOrdersCount();
            $processingOrders  = $sslSummaryStats->getProcessingSSLOrdersCount();
            $expiresSoonOrders = $sslSummaryStats->getExpiresSoonSSLOrdersCount();

            $sslSummaryIntegrationCode .= "
                <div class=\"col-sm-12\">
                        <div menuitemname=\"SSL Order Summary\" class=\"panel panel-default panel-accent-gold\">
                                <div class=\"panel-heading\">
                                        <h3 class=\"panel-title\">
                                                <div class=\"pull-right\">
                                                        <a class=\"btn btn-default bg-color-gold btn-xs\"
                                                                href=\"index.php?m=SSLCENTERWHMCS&mg-page=Orders&type=total\">
                                                                <i class=\"fas fa-plus\"></i>
                                                                $viewAll
                                                        </a>
                                                </div>
                                                <i class=\"fas fa-lock\"></i>
                                                $titleLang
                                        </h3>
                                </div>
                                <div class=\"list-group\">
                                        <div class=\"dsb-box col-sm-4\">
                                                <a href=\"index.php?m=SSLCENTERWHMCS&mg-page=Orders&type=unpaid\">
                                                        <div><i class=\"fa fa-credit-card icon icon col-sm-12\"></i><span>$unpaidLang<u>$unpaidOrders</u></span></div>
                                                </a>
                                        </div>
                                        <div class=\"dsb-box col-sm-4\">
                                                <a href=\"index.php?m=SSLCENTERWHMCS&mg-page=Orders&type=processing\">
                                                        <div><i class=\"fa fa-cogs icon col-sm-12\"></i><span>$processingLang<u>$processingOrders</u></span></div>
                                                </a>
                                        </div>
                                        <div class=\"dsb-box col-sm-4\">
                                                <a href=\"index.php?m=SSLCENTERWHMCS&mg-page=Orders&type=expires_soon\">
                                                        <div><i class=\"fa fa-hourglass-half icon col-sm-12\"></i><span>$expiresSoonLang<u>$expiresSoonOrders</u></span></div>
                                                </a>
                                        </div>
                                </div>
                        </div>
                </div>";

            
            
            
            $smarty->assign('sslSummaryIntegrationCode', $sslSummaryIntegrationCode);
            
            global $smartyvalues; 
            $smartyvalues['sslSummaryIntegrationCode'] = $sslSummaryIntegrationCode;
        }
        catch (\Exception $e)
        {
            
        }
    }
}
add_hook('ClientAreaPage', 1, 'SSLCENTER_displaySSLSummaryStats');
add_hook('ClientAreaHeadOutput', 999999999999, 'SSLCENTER_displaySSLSummaryStats');

function SSLCENTER_loadSSLSummaryCSSStyle($vars)
{
    if (isset($vars['filename'], $vars['templatefile']) && $vars['filename'] == 'clientarea' && $vars['templatefile'] == 'clientareahome')
    {
        return <<<HTML
    <link href="./modules/addons/SSLCENTERWHMCS/templates/clientarea/default/assets/css/sslSummary.css" rel="stylesheet" type="text/css" />
HTML;
    }
}
add_hook('ClientAreaHeadOutput', 1, 'SSLCENTER_loadSSLSummaryCSSStyle');

function SSLCENTER_displaySSLSummaryInSidebar($secondarySidebar)
{
    GLOBAL $smarty;
    
    try
    {
        require_once __DIR__.DS.'Loader.php';
        new \MGModule\SSLCENTERWHMCS\Loader();

        \MGModule\SSLCENTERWHMCS\Addon::I(true);

        $apiConf           = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        
        if(!isset($apiConf->sidebar_templates) || empty($apiConf->sidebar_templates))
        {
            if (in_array($smarty->tpl_vars['templatefile']->value, array('clientareahome')) || !isset($_SESSION['uid']))
            {
                return;
            }
        }
        else
        {
            if (!in_array($smarty->tpl_vars['templatefile']->value, explode(',', $apiConf->sidebar_templates)) || !isset($_SESSION['uid']))
            {
                return;
            }
        }
        
        $displaySSLSummary = $apiConf->display_ca_summary;
        if (!(bool) $displaySSLSummary)
            return;

        //get ssl statistics
        $sslSummaryStats = new MGModule\SSLCENTERWHMCS\eHelpers\SSLSummary($_SESSION['uid']);

        $totalOrders       = $sslSummaryStats->getTotalSSLOrdersCount();
        if ((int) $totalOrders == 0)
            return '';
        $unpaidOrders      = $sslSummaryStats->getUnpaidSSLOrdersCount();
        $processingOrders  = $sslSummaryStats->getProcessingSSLOrdersCount();
        $expiresSoonOrders = $sslSummaryStats->getExpiresSoonSSLOrdersCount();

        /** @var \WHMCS\View\Menu\Item $secondarySidebar */
        $newMenu = $secondarySidebar->addChild(
                'uniqueMenuSLLSummaryName', array(
            'name'  => 'Home',
            'label' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->absoluteT('addonCA', 'sslSummary', 'title'),
            'uri'   => '',
            'order' => 99,
            'icon'  => '',
                )
        );
        $newMenu->addChild(
                'uniqueSubMenuSLLSummaryTotal', array(
            'name'  => 'totalOrders',
            'label' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->absoluteT('addonCA', 'sslSummary', 'total'),
            'uri'   => 'index.php?m=SSLCENTERWHMCS&mg-page=Orders&type=total',
            'order' => 10,
            'badge' => $totalOrders,
                )
        );
        $newMenu->addChild(
                'uniqueSubMenuSLLSummaryUnpaid', array(
            'name'  => 'unpaidOrders',
            'label' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->absoluteT('addonCA', 'sslSummary', 'unpaid'),
            'uri'   => 'index.php?m=SSLCENTERWHMCS&mg-page=Orders&type=unpaid',
            'order' => 11,
            'badge' => $unpaidOrders,
                )
        );
        $newMenu->addChild(
                'uniqueSubMenuSLLSummaryProcessing', array(
            'name'  => 'processingOrders',
            'label' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->absoluteT('addonCA', 'sslSummary', 'processing'),
            'uri'   => 'index.php?m=SSLCENTERWHMCS&mg-page=Orders&type=processing',
            'order' => 12,
            'badge' => $processingOrders,
                )
        );
        $newMenu->addChild(
                'uniqueSubMenuSLLSummaryExpires', array(
            'name'  => 'expiresSoonOrders',
            'label' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::absoluteT('addonCA', 'sslSummary', 'expiresSoon'),
            'uri'   => 'index.php?m=SSLCENTERWHMCS&mg-page=Orders&type=expires_soon',
            'order' => 13,
            'badge' => $expiresSoonOrders,
                )
        );
    }
    catch (\Exception $e)
    {
        
    }
}
add_hook('ClientAreaSecondarySidebar', 1, 'SSLCENTER_displaySSLSummaryInSidebar');

//unable downgrade certificate sans if active
function SSLCENTER_unableDowngradeConfigOption($vars)
{
    if (isset($vars['filename'], $vars['templatefile'], $_REQUEST['type']) && $vars['filename'] == 'upgrade' && $_REQUEST['type'] == 'configoptions')
    {
        if (isset($_SESSION['SSLCENTER_configOpsCustomValidateError']) && $_SESSION['SSLCENTER_configOpsCustomValidateError'] != '')
        {
            //diplay downgrade error message
            global $smarty;
            $error                                          = $_SESSION['SSLCENTER_configOpsCustomValidateError'];
            $_SESSION['SSLCENTER_configOpsCustomValidateError'] = '';
            unset($_SESSION['SSLCENTER_configOpsCustomValidateError']);

            $smarty->assign("errormessage", $error);
        }

        if (!isset($_REQUEST['step']) || $_REQUEST['step'] != '2')
            return;

        $serviceID = NULL;
        if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
            $serviceID = $_REQUEST['id'];

        if ($serviceID === NULL)
            return;

        $ssl        = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $ssl->getByServiceId($serviceID);
        //check if service id sslcenter product
        if (is_null($sslService) && $sslService->module != 'SSLCENTERWHMCS')
            return;

        try
        {
            $orderStatus = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
        }
        catch (MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterApiException $e)
        {
            return;
        }
        //get config option id related to sans_count and current value
        $CORepo = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\configOptions\Repository($serviceID);
        if (isset($CORepo->{MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions::OPTION_SANS_COUNT}))
        {
            $sanCountConfigOptionValue = $CORepo->{MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions::OPTION_SANS_COUNT};
            $sanCountConfigOptionID    = $CORepo->getID(MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions::OPTION_SANS_COUNT);
        }
        //array(COID => array('minQuantity' => int, 'maxQuantity' => int))
        $configOptionscustomMinMaxQuantities = array(
            $sanCountConfigOptionID => array(
                'min' => $sanCountConfigOptionValue,
                'max' => null
            )
        );
        $whmcs                               = WHMCS\Application::getInstance();
        $configoption                        = $whmcs->get_req_var("configoption");
        $configOptionsService                = new MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions();
        $configOpsReturn                     = $configOptionsService->validateAndSanitizeQuantityConfigOptions($configoption, $configOptionscustomMinMaxQuantities);

        if ($orderStatus['status'] == 'active' AND $configOpsReturn)
        {
            $_SESSION['SSLCENTER_configOpsCustomValidateError'] = $configOpsReturn;
            redir('type=configoptions&id=' . $serviceID);
        }
    }
}
add_hook('ClientAreaPageUpgrade', 1, 'SSLCENTER_unableDowngradeConfigOption');

function SSLCENTER_overideProductPricingBasedOnCommission($vars)
{
    
    require_once __DIR__.DS.'Loader.php';
    new \MGModule\SSLCENTERWHMCS\Loader();
    MGModule\SSLCENTERWHMCS\Addon::I(true);

    $return       = [];
    //load module products
    $products     = array();
    $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();

    if(isset($_SESSION['uid']) && !empty($_SESSION['uid']))
    {
        $clientCurrency = getCurrency($_SESSION['uid']);
    }
    else
    {
        $currency = Capsule::table('tblcurrencies')->where('default', '1')->first();
        $clientCurrency['id'] = isset($_SESSION['currency']) && !empty($_SESSION['currency']) ? $_SESSION['currency'] : $currency->id; 
    }
    //get sslcenter all products
    foreach ($productModel->getModuleProducts() as $product)
    {
        
        if($product->servertype != 'SSLCENTERWHMCS')
        {
            continue;
        }
        
        if ($product->id == $vars['pid'])
        {
            $commission = MGModule\SSLCENTERWHMCS\eHelpers\Commission::getCommissionValue($vars);
            
            foreach ($product->pricing as $pricing)
            {
                if ($pricing->currency == $clientCurrency['id'])
                {
                    $priceField           = $vars['proddata']['billingcycle'];
                    if($priceField == 'onetime')
                    {
                        $priceField = 'monthly';
                    }

                    $return = ['recurring' => (float) $pricing->{$priceField} + (float) $pricing->{$priceField} * (float) $commission,];
                }
            }
        }
    }

    return $return;
}

add_hook('OrderProductPricingOverride', 1, 'SSLCENTER_overideProductPricingBasedOnCommission');

function SSLCENTER_overideDisaplayedProductPricingBasedOnCommission($vars)
{ 
    global $smarty;
    global $smartyvalues; 
    require_once __DIR__.DS.'Loader.php';
    
    new \MGModule\SSLCENTERWHMCS\Loader();
    MGModule\SSLCENTERWHMCS\Addon::I(true);
    if($vars['filename'] == 'cart' || $vars['filename'] == 'index')
    {
    
        switch ($smarty->tpl_vars['templatefile']->value)
        {
            case 'products':
                $products = $smarty->tpl_vars['products']->value;     
                foreach($products as $key => &$product)
                {
                    $productSSLCenter = Capsule::table('tblproducts')
                            ->where('id', $product['pid'])
                            ->where('servertype', 'SSLCENTERWHMCS')
                            ->first();
                    
                    if(isset($productSSLCenter->id) && !empty($productSSLCenter->id))
                    {
                    
                        $pid = $product['pid'];

                        $commission = MGModule\SSLCENTERWHMCS\eHelpers\Commission::getCommissionValue(array('pid' => $pid));            
                        $products[$key]['pricing'] = MGModule\SSLCENTERWHMCS\eHelpers\Whmcs::getPricingInfo($pid, $commission);
                    
                    }
                }

                $smartyvalues['products'] = $products;
                $smarty->assign('products', $products);
                break;
            case 'configureproduct':

                $pid = $smarty->tpl_vars['productinfo']->value['pid'];

                $productSSLCenter = Capsule::table('tblproducts')
                            ->where('id', $pid)
                            ->where('servertype', 'SSLCENTERWHMCS')
                            ->first();
                    
                if(isset($productSSLCenter->id) && !empty($productSSLCenter->id))
                {

                    $commission = MGModule\SSLCENTERWHMCS\eHelpers\Commission::getCommissionValue(array('pid' => $pid));
                    $pricing = MGModule\SSLCENTERWHMCS\eHelpers\Whmcs::getPricingInfo($pid, $commission);

                    $smartyvalues['pricing'] = $pricing;
                    $smarty->assign('pricing', $pricing);
                }
                break;
            default:
                break;
        } 
    
    }
    
}
add_hook('ClientAreaHeadOutput', 999999999999, 'SSLCENTER_overideDisaplayedProductPricingBasedOnCommission');

add_hook('InvoiceCreation', 1, function($vars) {
    
    $invoiceid = $vars['invoiceid'];
    
    $items = Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceid)->where('type', 'Upgrade')->get();
    
    foreach ($items as $item)
    {
        $description = $item->description;
        
        $upgradeid = $item->relid;
        $upgrade = Capsule::table('tblupgrades')->where('id', $upgradeid)->first();
        
        $serviceid = $upgrade->relid;
        $service = Capsule::table('tblhosting')->where('id', $serviceid)->first();
        
        $productid = $service->packageid;
        $product = Capsule::table('tblproducts')->where('id', $productid)->where('paytype', 'onetime')->where('servertype', 'SSLCENTERWHMCS')->first();
        
        if(isset($product->configoption7) && !empty($product->configoption7))
        {
            
            if (strpos($description, '00/00/0000') !== false) {
                
                $description = str_replace('- 00/00/0000', '', $description);
                $length = strlen($description);
                $description = substr($description, 0, $length-13);
                
                Capsule::table('tblinvoiceitems')->where('id', $item->id)->update(array('description' => trim($description)));
                                
            }
            
        }
        
        
        
    }
    
});
