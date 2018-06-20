<?php

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

#MGLICENSE_FUNCTIONS#

function GGSSLWHMCS_config(){
    require_once 'Loader.php';
    new \MGModule\GGSSLWHMCS\Loader();
    return MGModule\GGSSLWHMCS\Addon::config();
}

function GGSSLWHMCS_activate(){
    require_once 'Loader.php';
    new \MGModule\GGSSLWHMCS\Loader();
    return MGModule\GGSSLWHMCS\Addon::activate();
}

function GGSSLWHMCS_deactivate(){
    require_once 'Loader.php';
    new \MGModule\GGSSLWHMCS\Loader();
    return MGModule\GGSSLWHMCS\Addon::deactivate();
}

function GGSSLWHMCS_upgrade($vars){
    require_once 'Loader.php';
    new \MGModule\GGSSLWHMCS\Loader();
    return MGModule\GGSSLWHMCS\Addon::upgrade($vars);
}

function GGSSLWHMCS_output($params){
    require_once 'Loader.php';
    new \MGModule\GGSSLWHMCS\Loader();
    #MGLICENSE_CHECK_ECHO_AND_RETURN_MESSAGE#
    MGModule\GGSSLWHMCS\Addon::I(FALSE,$params);
    
    if(!empty($_REQUEST['json']))
    {
        ob_clean();
        header('Content-Type: text/plain');
        echo MGModule\GGSSLWHMCS\Addon::getJSONAdminPage($_REQUEST);
        die();
    }
    
    if(!empty($_REQUEST['customPage']))
    {
        ob_clean();
        echo MGModule\GGSSLWHMCS\Addon::getHTMLAdminCustomPage($_REQUEST);
        die();
    }

    echo MGModule\GGSSLWHMCS\Addon::getHTMLAdminPage($_REQUEST);
}


function GGSSLWHMCS_clientarea(){
    require_once 'Loader.php';
    new \MGModule\GGSSLWHMCS\Loader();
    
    #MGLICENSE_CHECK_ECHO_AND_RETURN_MESSAGE#
    
    if(!empty($_REQUEST['json']))
    {
        ob_clean();
        header('Content-Type: text/plain');
        echo MGModule\GGSSLWHMCS\Addon::getJSONClientAreaPage($_REQUEST);
        die();
    }
    
    return MGModule\GGSSLWHMCS\Addon::getHTMLClientAreaPage($_REQUEST);
}
