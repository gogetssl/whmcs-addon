<?php

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

#MGLICENSE_FUNCTIONS#

function SSLCENTERWHMCS_config(){
    require_once 'Loader.php';
    new \MGModule\SSLCENTERWHMCS\Loader();
    return MGModule\SSLCENTERWHMCS\Addon::config();
}

function SSLCENTERWHMCS_activate(){
    require_once 'Loader.php';
    new \MGModule\SSLCENTERWHMCS\Loader();
    return MGModule\SSLCENTERWHMCS\Addon::activate();
}

function SSLCENTERWHMCS_deactivate(){
    require_once 'Loader.php';
    new \MGModule\SSLCENTERWHMCS\Loader();
    return MGModule\SSLCENTERWHMCS\Addon::deactivate();
}

function SSLCENTERWHMCS_upgrade($vars){
    require_once 'Loader.php';
    new \MGModule\SSLCENTERWHMCS\Loader();
    return MGModule\SSLCENTERWHMCS\Addon::upgrade($vars);
}

function SSLCENTERWHMCS_output($params){
    require_once 'Loader.php';
    new \MGModule\SSLCENTERWHMCS\Loader();
    #MGLICENSE_CHECK_ECHO_AND_RETURN_MESSAGE#
    MGModule\SSLCENTERWHMCS\Addon::I(FALSE,$params);
    
    if(!empty($_REQUEST['json']))
    {
        ob_clean();
        header('Content-Type: text/plain');
        echo MGModule\SSLCENTERWHMCS\Addon::getJSONAdminPage($_REQUEST);
        die();
    }
    
    if(!empty($_REQUEST['customPage']))
    {
        ob_clean();
        echo MGModule\SSLCENTERWHMCS\Addon::getHTMLAdminCustomPage($_REQUEST);
        die();
    }

    echo MGModule\SSLCENTERWHMCS\Addon::getHTMLAdminPage($_REQUEST);
}


function SSLCENTERWHMCS_clientarea(){
    require_once 'Loader.php';
    new \MGModule\SSLCENTERWHMCS\Loader();
    
    #MGLICENSE_CHECK_ECHO_AND_RETURN_MESSAGE#
    
    if(!empty($_REQUEST['json']))
    {
        ob_clean();
        header('Content-Type: text/plain');
        echo MGModule\SSLCENTERWHMCS\Addon::getJSONClientAreaPage($_REQUEST);
        die();
    }
    
    return MGModule\SSLCENTERWHMCS\Addon::getHTMLClientAreaPage($_REQUEST);
}
