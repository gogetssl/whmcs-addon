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

