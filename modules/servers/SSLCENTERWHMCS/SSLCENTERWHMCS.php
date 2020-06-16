<?php

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

require_once __DIR__.DS.'Loader.php';
new \MGModule\SSLCENTERWHMCS\Loader();
MGModule\SSLCENTERWHMCS\Server::I();

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function SSLCENTERWHMCS_MetaData() {
    return array(
        'DisplayName' => 'SSLCENTER WHMCS',
        'APIVersion' => '2.9',
    );
}

function SSLCENTERWHMCS_ConfigOptions() {
    $configOptions = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions();
    return $configOptions->run();
}

function SSLCENTERWHMCS_CreateAccount($params) {
    $createAccount = new MGModule\SSLCENTERWHMCS\eServices\provisioning\CreateAccount($params);
    return $createAccount->run();
}

function SSLCENTERWHMCS_SuspendAccount($params) {
    $suspendAccount = new MGModule\SSLCENTERWHMCS\eServices\provisioning\SuspendAccount($params);
    return $suspendAccount->run();
}

function SSLCENTERWHMCS_UnsuspendAccount($params) {
    $unsuspendAccount = new MGModule\SSLCENTERWHMCS\eServices\provisioning\UnsuspendAccount($params);
    return $unsuspendAccount->run();
}

function SSLCENTERWHMCS_SSLStepOne($params) {
    $SSLStepOne = new MGModule\SSLCENTERWHMCS\eServices\provisioning\SSLStepOne($params);
    return $SSLStepOne->run();
}

function SSLCENTERWHMCS_SSLStepTwo($params) {
    $SSLStepTwo = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\SSLStepTwo($params);
    if(isset($_POST['privateKey']) && $_POST['privateKey'] != null) {
        $SSLStepTwo->setPrivateKey($_POST['privateKey']);
    }    
    return $SSLStepTwo->run();
}
function SSLCENTERWHMCS_SSLStepTwoJS($params) {
    $SSLStepTwoJS = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\SSLStepTwoJS($params);    
    return $SSLStepTwoJS->run();
}

function SSLCENTERWHMCS_SSLStepThree($params) {
   $SSLStepThree = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\SSLStepThree($params);
    return $SSLStepThree->run();
}

function SSLCENTERWHMCS_TerminateAccount($params) {
    $terminateAccount = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\TerminateAccount($params);
    return $terminateAccount->run();
}

function SSLCENTERWHMCS_AdminCustomButtonArray() {
    $adminCustomButtonArray = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\AdminCustomButtonArray();
    return $adminCustomButtonArray->run();
}

function SSLCENTERWHMCS_SSLAdminResendApproverEmail($params) {
    $resendApproverEmail = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\AdminResendApproverEmail($params);
    return $resendApproverEmail->run();
}

function SSLCENTERWHMCS_SSLAdminResendCertificate($params) {
    $adminResendCertificate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\AdminResendCertificate($params);
    return $adminResendCertificate->run();
}

function SSLCENTERWHMCS_Renew($params) {
    $renewCertificate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\Renew($params);
    return $renewCertificate->run();
}

function SSLCENTERWHMCS_AdminServicesTabFields(array $params) {
    $adminServiceJS = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\AdminServicesTabFields($params);
    return $adminServiceJS->run();
}

function SSLCENTERWHMCS_SSLAdminGetCertificate($p) {
    return MGModule\SSLCENTERWHMCS\eServices\provisioning\GetCertificate::runBySslId($p['serviceid']);
}

function SSLCENTERWHMCS_FlashErrorStepOne() {
    $errors = \MGModule\SSLCENTERWHMCS\eServices\FlashService::getStepOneError();
    if(isset($errors['errormessage']) && !empty($errors['errormessage']))
    {
        // WHMCS v7.2
       global $smartyvalues; 
       $smartyvalues['errormessage'] = $errors['errormessage'];
       
       // < WHMCS v7.2
       global $smarty;
       $smarty->assign('errormessage', $errors['errormessage']);
    }
}

if (isset($_POST['changeEmailModal'], $_SESSION['adminid']) AND $_POST['changeEmailModal'] === 'yes' AND $_SESSION['adminid']) {
    $adminChangeApproverEmail = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\AdminChangeApproverEmail($_POST);
    $adminChangeApproverEmail->run();
}

if (isset($_POST['action'], $_SESSION['adminid']) AND $_POST['action'] === 'getApprovalEmailsForDomain' AND $_SESSION['adminid']) {
    
    try{
        $serviceid = $_REQUEST['id'];
        $ssl        = new MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $ssl->getByServiceId($serviceid);
        
        $orderStatus = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
            
        
        if (!empty($orderStatus['domain'])) {            
            $domain = $orderStatus['domain'];
        }
            
        if(!empty($orderStatus['product_id'])) {                
            $apiRepo       = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
            $apiProduct    = $apiRepo->getProduct($orderStatus['product_id']);
            $brand = $apiProduct->brand;
        }
            
        $domainEmails = [];        
        if($brand == 'geotrust') {
            $apiDomainEmails             = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmailsForGeotrust($domain);
            $domainEmails = $apiDomainEmails['GeotrustApprovalEmails'];
        } else {
            $apiDomainEmails             = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($domain);
            $domainEmails = $apiDomainEmails['ComodoApprovalEmails'];
        }  

        $result = [
            'success' => 1,
            'domainEmails' => $domainEmails
        ];
          
    } catch(Exception $ex)  {
        $result = [
            'success' => 0,
            'error' => $ex->getMessage()
        ];
    }
    
    ob_clean();
    echo json_encode($result);
    die();
}
if (isset($_POST['reissueModal'], $_SESSION['adminid']) AND $_POST['reissueModal'] === 'yes' AND $_SESSION['adminid'] ) {   
       
    $adminReissueCertificate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\AdminReissueCertificate($_POST);
    $adminReissueCertificate->run();   
}

if (isset($_POST['recheckModal'], $_SESSION['adminid']) AND $_POST['recheckModal'] === 'yes' AND $_SESSION['adminid']) {
    $adminCheckCertificateDetails = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\AdminRecheckCertificateDetails($_POST);
    $adminCheckCertificateDetails->run();
}

if (isset($_POST['viewModal'], $_SESSION['adminid']) AND $_POST['viewModal'] === 'yes' AND $_SESSION['adminid']) {
    $adminViewCertyfifcate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\AdminViewCertyfifcate($_POST);
    $adminViewCertyfifcate->run();
}

function SSLCENTERWHMCS_ClientAreaCustomReissueCertificate($params) {    
    $clientReissueCertificate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\ClientReissueCertificate($params, $_POST, $_GET);
    return $clientReissueCertificate->run();
}

function SSLCENTERWHMCS_ClientAreaCustomContactDetails($params) {
    $clientReissueCertificate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\ClientContactDetails($params, $_POST, $_GET);
    return $clientReissueCertificate->run();
}

function SSLCENTERWHMCS_ClientArea(array $params) {
    
    if(!empty($_REQUEST['json']))
    {
        header('Content-Type: text/plain');
        echo MGModule\SSLCENTERWHMCS\Server::getJSONClientAreaPage($params, $_REQUEST);
        die();
    }
    
    return \MGModule\SSLCENTERWHMCS\Server::getHTMLClientAreaPage($params, $_REQUEST);
}

function SSLCENTERWHMCS_ClientAreaCustomButtonArray() {
    $lang = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance();
    return [
        $lang->T('Reissue Certificate') => 'ClientAreaCustomReissueCertificate'
        //$lang->T('contactDetails')     => 'ClientAreaCustomContactDetails'
    ];
}
//add_hook('ClientAreaHeadOutput', 1, 'SSLCENTERWHMCS_ClientAreaCustomButtonArray');
add_hook('ClientAreaHeadOutput', 1, 'SSLCENTERWHMCS_SSLStepTwoJS');
add_hook('ClientAreaHeadOutput', 9999999999, 'SSLCENTERWHMCS_FlashErrorStepOne');
