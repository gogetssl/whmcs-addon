<?php

add_hook('ClientAreaHeadOutput', 1, function($params) {
    $show = false;  
    
    if($params['filename'] === 'configuressl' && $params['loggedin'] == '1' && isset($_REQUEST['action']) && $_REQUEST['action'] === 'generateCsr' ) {     
        ob_clean();
        $GenerateCsr = new MGModule\GGSSLWHMCS\eServices\provisioning\GenerateCSR($params, $_POST);
        echo $GenerateCsr->run();
        die();
    }
    if ($params['templatefile'] === 'clientareacancelrequest') {
        try {
            $service = \WHMCS\Service\Service::findOrFail($params['id']);
            if ($service->product->servertype === 'GGSSLWHMCS') {
                $show = true;
            }
        } catch (Exception $exc) {
            
        }
    } elseif ($params['modulename'] === 'GGSSLWHMCS') {
        $show = true;
    }
    if (!$show) {
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
add_hook('ClientLogin', 1, function($vars) {     
    
    if(isset($_REQUEST['redirectToProductDetails'], $_REQUEST['serviceID']) && $_REQUEST['redirectToProductDetails'] === 'true' && is_numeric($_REQUEST['serviceID'])) {
        $ca = new \WHMCS_ClientArea();
        if($ca->isLoggedIn()) {
            header('Location: clientarea.php?action=productdetails&id=' . $_REQUEST['serviceID']);
            die();
        }        
    }
});

add_hook('InvoicePaid', 1, function($vars) {
    require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'init.php';
    require_once 'Loader.php';
    

    $loader = new \MGModule\GGSSLWHMCS\Loader();
    $invoiceGenerator = new \MGModule\GGSSLWHMCS\eHelpers\Invoice();
    
    $invoiceGenerator->invoicePaid($vars['invoiceid']);
});
