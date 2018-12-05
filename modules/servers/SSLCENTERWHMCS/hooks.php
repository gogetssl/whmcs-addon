<?php

use \MGModule\SSLCENTERWHMCS as main;
use Exception;

add_hook('ClientAreaPage', 1, function ($params) {
    // uncomment to boost, need test ;)
    //if(!isset($_SESSION['SSLCENTER_WHMCS_SERVICE_TO_ACTIVE'])) {
    //    return;
    //}
    require_once 'Loader.php';
    new main\Loader();
    $activator = new main\eServices\provisioning\Activator();
    $activator->run();
});
