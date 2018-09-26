<?php

add_hook('ClientAreaHeadOutput', 1, function($params)
{
    $show = false;

    if ($params['filename'] === 'configuressl' && $params['loggedin'] == '1' && isset($_REQUEST['action']) && $_REQUEST['action'] === 'generateCsr')
    {
        $GenerateCsr = new MGModule\GGSSLWHMCS\eServices\provisioning\GenerateCSR($params, $_POST);
        echo $GenerateCsr->run();
        die();
    }
    if ($params['templatefile'] === 'clientareacancelrequest')
    {
        try
        {
            $service = \WHMCS\Service\Service::findOrFail($params['id']);
            if ($service->product->servertype === 'GGSSLWHMCS')
            {
                $show = true;
            }
        }
        catch (Exception $exc)
        {
            
        }
    }
    elseif ($params['modulename'] === 'GGSSLWHMCS')
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
    require_once 'Loader.php';

    $loader           = new \MGModule\GGSSLWHMCS\Loader();
    $invoiceGenerator = new \MGModule\GGSSLWHMCS\eHelpers\Invoice();

    $invoiceGenerator->invoicePaid($vars['invoiceid']);
});


/*
 *
 * assign ssl summary stats to clieat area page 
 * 
 */

function displaySSLSummaryStats($vars)
{

    if (isset($vars['filename'], $vars['templatefile']) && $vars['filename'] == 'clientarea' && $vars['templatefile'] == 'clientareahome')
    {
        try
        {
            require_once 'Loader.php';
            new \MGModule\GGSSLWHMCS\Loader();

            GLOBAl $smarty;

            \MGModule\GGSSLWHMCS\Addon::I(true);

            $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();        
            $displaySSLSummary = $apiConf->display_ca_summary; 
            if(!(bool) $displaySSLSummary)
                return;
            
            $sslSummaryIntegrationCode = '';

            $titleLang       = \MGModule\GGSSLWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'title');
            $totalLang       = \MGModule\GGSSLWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'total');
            $unpaidLang      = \MGModule\GGSSLWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'unpaid');
            $processingLang  = \MGModule\GGSSLWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'processing');
            $expiresSoonLang = \MGModule\GGSSLWHMCS\mgLibs\Lang::T('addonCA', 'sslSummary', 'expiresSoon');

            //get ssl statistics
            $sslSummaryStats = new MGModule\GGSSLWHMCS\eHelpers\SSLSummary($_SESSION['uid']);

            $totalOrders       = $sslSummaryStats->getTotalSSLOrders(); 
            $unpaidOrders      = $sslSummaryStats->getUnpaidSSLOrders(); 
            $processingOrders  = $sslSummaryStats->getProcessingSSLOrders();
            $expiresSoonOrders = $sslSummaryStats->getExpiresSoonSSLOrders();
            
            $sslSummaryIntegrationCode .= "            
        <h3 class=\"dsb-title\" align=\"center\">$titleLang</h3>
        <div class=\"dash-stat-box dlb-border clerarfix\">            
            <div class=\"dsb-box\">
                <a href=\"clientarea.php?action=services\">
                    <div><i class=\"fa fa-check icon\"></i><span><b>$totalLang</b><u>$totalOrders</u></span></div>
                </a>
            </div>
            <div class=\"dsb-box\">            
                <a href=\"clientarea.php?action=invoices\">                
                        <div><i class=\"fa fa-credit-card icon\"></i><span><b>$unpaidLang</b><u>$unpaidOrders</u></span></div>                
                </a>
            </div>
            <div class=\"dsb-box\">
                <a href=\"clientarea.php?action=services\">
                    <div><i class=\"fa fa-cogs icon\"></i><span><b>$processingLang</b><u>$processingOrders</u></span></div>               
                </a>
            </div>
            <div class=\"dsb-box\"   style=\"border-right: none;\">
                <a href=\"clientarea.php?action=services\">
                    <div><i class=\"fa fa-hourglass-half  icon\"></i><span><b>$expiresSoonLang</b><u>$expiresSoonOrders</u></span></div>
                <a href=\"clientarea.php?action=services\">       
            </div>
    </div>";

            $smarty->assign('sslSummaryIntegrationCode', $sslSummaryIntegrationCode);
        }
        catch (\Exception $e)
        {
            
        }
    }
}
add_hook('ClientAreaPage', 1, 'displaySSLSummaryStats');

function loadSSLSummaryCSSStyle($vars)
{
    if (isset($vars['filename'], $vars['templatefile']) && $vars['filename'] == 'clientarea' && $vars['templatefile'] == 'clientareahome')
    {
        return <<<HTML
    <link href="./modules/addons/GGSSLWHMCS/templates/clientarea/default/assets/css/sslSummary.css" rel="stylesheet" type="text/css" />
HTML;
    }
}
add_hook('ClientAreaHeadOutput', 1, 'loadSSLSummaryCSSStyle');

function displaySSLSummaryInSidebar($secondarySidebar)
{
    
    
    GLOBAL $smarty;
    
    if (in_array($smarty->tpl_vars['templatefile']->value, array('clientareahome','clientareacancelrequest', '/modules/servers/GGSSLWHMCS/main.tpl')) || !isset($_SESSION['uid']))
        return;
    
    try
    {
        require_once 'Loader.php';
        new \MGModule\GGSSLWHMCS\Loader();

        \MGModule\GGSSLWHMCS\Addon::I(true);
        
        $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();        
        $displaySSLSummary = $apiConf->display_ca_summary; 
        if(!(bool) $displaySSLSummary)
            return;
   
        //get ssl statistics
        $sslSummaryStats = new MGModule\GGSSLWHMCS\eHelpers\SSLSummary($_SESSION['uid']);

        $totalOrders       = $sslSummaryStats->getTotalSSLOrders();
        $unpaidOrders      = $sslSummaryStats->getUnpaidSSLOrders();
        $processingOrders  = $sslSummaryStats->getProcessingSSLOrders();
        $expiresSoonOrders = $sslSummaryStats->getExpiresSoonSSLOrders();

        /** @var \WHMCS\View\Menu\Item $secondarySidebar */
        $newMenu = $secondarySidebar->addChild(
                'uniqueMenuSLLSummaryName', array(
            'name'  => 'Home',
            'label' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('sslSummarySidebarTitle'),
            'uri'   => '',
            'order' => 99,
            'icon'  => '',
                )
        );
        $newMenu->addChild(
                'uniqueSubMenuSLLSummaryTotal', array(
            'name'  => 'totalOrders',
            'label' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('sslSummarySidebarTotal'),
            'uri'   => 'clientarea.php?action=services',
            'order' => 10,
            'badge' => $totalOrders,
                )
        );
        $newMenu->addChild(
                'uniqueSubMenuSLLSummaryUnpaid', array(
            'name'  => 'unpaidOrders',
            'label' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('sslSummarySidebarUnpaid'),
            'uri'   => 'clientarea.php?action=services',
            'order' => 11,
            'badge' => $unpaidOrders,
                )
        );
        $newMenu->addChild(
                'uniqueSubMenuSLLSummaryProcessing', array(
            'name'  => 'processingOrders',
            'label' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('sslSummarySidebarProcessing'),
            'uri'   => 'clientarea.php?action=services',
            'order' => 12,
            'badge' => $processingOrders,
                )
        );
        $newMenu->addChild(
                'uniqueSubMenuSLLSummaryExpires', array(
            'name'  => 'expiresSoonOrders',
            'label' => \MGModule\GGSSLWHMCS\mgLibs\Lang::T('sslSummarySidebarExpiresSoon'),
            'uri'   => 'clientarea.php?action=services',
            'order' => 13,
            'badge' => $expiresSoonOrders,
                )
        );
    }
    catch (\Exception $e)
    {
    }
}
add_hook('ClientAreaSecondarySidebar', 1, 'displaySSLSummaryInSidebar');
