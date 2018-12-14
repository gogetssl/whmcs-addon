<?php

use \MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule as DB;
use Exception;

add_hook('ClientAreaPage', 1, function ($params)
{
    // uncomment to boost, need test ;)
    //if(!isset($_SESSION['SSLCENTER_WHMCS_SERVICE_TO_ACTIVE'])) {
    //    return;
    //}
    require_once 'Loader.php';
    new main\Loader();
    $activator = new main\eServices\provisioning\Activator();
    $activator->run();

    if (isset($params['templatefile']))
    {
        global $smarty;
        switch ($params['templatefile'])
        {
            case 'clientareaproductdetails':
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
