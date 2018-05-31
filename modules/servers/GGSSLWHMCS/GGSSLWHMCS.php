<?php

require_once 'Loader.php';
new \MGModule\GGSSLWHMCS\Loader();
MGModule\GGSSLWHMCS\Server::I();

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function GGSSLWHMCS_MetaData() {
    return array(
        'DisplayName' => 'GGSSL WHMCS',
        'APIVersion' => '2.9',
    );
}

function GGSSLWHMCS_ConfigOptions() {
    $configOptions = new \MGModule\GGSSLWHMCS\eServices\provisioning\ConfigOptions();
    return $configOptions->run();
}

function GGSSLWHMCS_CreateAccount($params) {
    $createAccount = new MGModule\GGSSLWHMCS\eServices\provisioning\CreateAccount($params);
    return $createAccount->run();
}

function GGSSLWHMCS_SSLStepOne($params) {
    $SSLStepOne = new MGModule\GGSSLWHMCS\eServices\provisioning\SSLStepOne($params);
    return $SSLStepOne->run();
}

function GGSSLWHMCS_SSLStepTwo($params) {
    $SSLStepTwo = new \MGModule\GGSSLWHMCS\eServices\provisioning\SSLStepTwo($params);
    if(isset($_POST['privateKey']) && $_POST['privateKey'] != null) {
        $SSLStepTwo->setPrivateKey($_POST['privateKey']);
    }
    return $SSLStepTwo->run();
}
function GGSSLWHMCS_SSLStepTwoJS($params) {
    $SSLStepTwoJS = new \MGModule\GGSSLWHMCS\eServices\provisioning\SSLStepTwoJS($params);
    return $SSLStepTwoJS->run();
}

function GGSSLWHMCS_SSLStepThree($params) {
    $SSLStepThree = new \MGModule\GGSSLWHMCS\eServices\provisioning\SSLStepThree($params);
    return $SSLStepThree->run();
}

function GGSSLWHMCS_TerminateAccount($params) {
    $terminateAccount = new \MGModule\GGSSLWHMCS\eServices\provisioning\TerminateAccount($params);
    return $terminateAccount->run();
}

function GGSSLWHMCS_AdminCustomButtonArray() {
    $adminCustomButtonArray = new \MGModule\GGSSLWHMCS\eServices\provisioning\AdminCustomButtonArray();
    return $adminCustomButtonArray->run();
}

function GGSSLWHMCS_SSLAdminResendApproverEmail($params) {
    $resendApproverEmail = new \MGModule\GGSSLWHMCS\eServices\provisioning\AdminResendApproverEmail($params);
    return $resendApproverEmail->run();
}

function GGSSLWHMCS_SSLAdminResendCertificate($params) {
    $adminResendCertificate = new \MGModule\GGSSLWHMCS\eServices\provisioning\AdminResendCertificate($params);
    return $adminResendCertificate->run();
}

function GGSSLWHMCS_Renew($params) {
    $renewCertificate = new \MGModule\GGSSLWHMCS\eServices\provisioning\Renew($params);
    return $renewCertificate->run();
}

function GGSSLWHMCS_AdminServicesTabFields(array $params) {
    $adminServiceJS = new \MGModule\GGSSLWHMCS\eServices\provisioning\AdminServicesTabFields($params);
    return $adminServiceJS->run();
}

function GGSSLWHMCS_SSLAdminGetCertificate($p) {
    return MGModule\GGSSLWHMCS\eServices\provisioning\GetCertificate::runBySslId($p['serviceid']);
}

function GGSSLWHMCS_FlashErrorStepOne() {
    return \MGModule\GGSSLWHMCS\eServices\FlashService::getStepOneError();
}

if (isset($_POST['changeEmailModal'], $_SESSION['adminid']) AND $_POST['changeEmailModal'] === 'yes' AND $_SESSION['adminid']) {
    $adminChangeApproverEmail = new \MGModule\GGSSLWHMCS\eServices\provisioning\AdminChangeApproverEmail($_POST);
    $adminChangeApproverEmail->run();
}
//tu
if (isset($_POST['action'], $_SESSION['adminid']) AND $_POST['action'] === 'getApprovalEmailsForDomain' AND $_SESSION['adminid']) {
    
    try{
        $serviceid = $_REQUEST['id'];
        $ssl        = new MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $ssl->getByServiceId($serviceid);
        
        $orderStatus = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
            
        
        if (!empty($orderStatus['domain'])) {            
            $domain = $orderStatus['domain'];
        }
            
        if(!empty($orderStatus['product_id'])) {                
            $apiRepo       = new \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products();
            $apiProduct    = $apiRepo->getProduct($orderStatus['product_id']);
            $brand = $apiProduct->brand;
        }
            
        $domainEmails = [];        
        if($brand == 'geotrust') {
            $apiDomainEmails             = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmailsForGeotrust($domain);
            $domainEmails = $apiDomainEmails['GeotrustApprovalEmails'];
        } else {
            $apiDomainEmails             = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($domain);
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
       
    $adminReissueCertificate = new \MGModule\GGSSLWHMCS\eServices\provisioning\AdminReissueCertificate($_POST);
    $adminReissueCertificate->run();   
}

if (isset($_POST['viewModal'], $_SESSION['adminid']) AND $_POST['viewModal'] === 'yes' AND $_SESSION['adminid']) {
    $adminViewCertyfifcate = new \MGModule\GGSSLWHMCS\eServices\provisioning\AdminViewCertyfifcate($_POST);
    $adminViewCertyfifcate->run();
}

function GGSSLWHMCS_ClientAreaCustomReissueCertificate($params) {    
    $clientReissueCertificate = new \MGModule\GGSSLWHMCS\eServices\provisioning\ClientReissueCertificate($params, $_POST, $_GET);
    return $clientReissueCertificate->run();
}

function GGSSLWHMCS_ClientAreaCustomContactDetails($params) {
    $clientReissueCertificate = new \MGModule\GGSSLWHMCS\eServices\provisioning\ClientContactDetails($params, $_POST, $_GET);
    return $clientReissueCertificate->run();
}

function GGSSLWHMCS_ClientArea(array $params) {
    
    if(!empty($_REQUEST['json']))
    {
        ob_clean();
        header('Content-Type: text/plain');
        echo MGModule\GGSSLWHMCS\Server::getJSONClientAreaPage($params, $_REQUEST);
        die();
    }
    
    return \MGModule\GGSSLWHMCS\Server::getHTMLClientAreaPage($params, $_REQUEST);
}

function GGSSLWHMCS_ClientAreaCustomButtonArray() {
    $lang = \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance();
    return [
        $lang->T('Reissue Certificate') => 'ClientAreaCustomReissueCertificate'
        //$lang->T('contactDetails')     => 'ClientAreaCustomContactDetails'
    ];
}
//add_hook('ClientAreaHeadOutput', 1, 'GGSSLWHMCS_ClientAreaCustomButtonArray');
add_hook('ClientAreaHeadOutput', 1, 'GGSSLWHMCS_SSLStepTwoJS');
add_hook('ClientAreaPage', 1, 'GGSSLWHMCS_FlashErrorStepOne');
