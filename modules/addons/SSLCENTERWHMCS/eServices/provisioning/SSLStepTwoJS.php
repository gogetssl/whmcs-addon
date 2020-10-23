<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

class SSLStepTwoJS {

    private $p;
    private $domainsEmailApprovals = [];
    private $brand = '';
    private $disabledValidationMethods = array();

    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        
        if (!$this->canRun()) {
            return '';
        }

        if (!$this->isValidModule()) {
            return '';
        }
        try {
            $this->setBrand($_POST);
            $this->setDisabledValidationMethods($_POST);
            
            $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($this->p['serviceid']);

            $product = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($service->productID);
            
            if($product->configuration()->text_name == '144')
            {
                array_push($this->disabledValidationMethods, 'email');
                array_push($this->disabledValidationMethods, 'dns');
            }
            
            $this->SSLStepTwoJS($this->p);
            
            return \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getSanEmailsScript(json_encode($this->domainsEmailApprovals), json_encode(\MGModule\SSLCENTERWHMCS\eServices\FlashService::getFieldsMemory($_GET['cert'])), json_encode($this->brand), json_encode($this->disabledValidationMethods));
        } catch (Exception $ex) {
            return '';
        }

    }

    private function canRun() {
        if ($this->p['filename'] !== 'configuressl') {
            return false;
        }
        if ($_GET['step'] != 2) {
            return false;
        }
        return true;
    }    

    private function setBrand($params) {
        if(isset($params['sslbrand']) &&  $params['sslbrand'] != null){
            $this->brand = $params['sslbrand'];
        }
    }
    
    private function setDisabledValidationMethods($params) {
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();        
        if($apiConf->disable_email_validation)
        {
            array_push($this->disabledValidationMethods, 'email');
        }
    }
    
    private function isValidModule() {
        return \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSLTemplorary::getInstance()->get($_GET['cert']) === true;

    }

    private function SSLStepTwoJS() {
        
        if(isset($_SESSION['decodeCSR']) && !empty($_SESSION['decodeCSR']))
        {
            $decodedCSR = $_SESSION['decodeCSR'];
            unset($_SESSION['decodeCSR']);
        }
        else
        {
            $decodedCSR   = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR(trim(rtrim($_POST['csr'])));
        }
        
        $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($this->p['serviceid']);
        $product = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($service->productID);
        
        $productssl = false;
        $checkTable = Capsule::schema()->hasTable('mgfw_SSLCENTER_product_brand');
        if($checkTable)
        {
            if (Capsule::schema()->hasColumn('mgfw_SSLCENTER_product_brand', 'data'))
            {
                $productsslDB = Capsule::table('mgfw_SSLCENTER_product_brand')->where('pid', $product->configuration()->text_name)->first();
                if(isset($productsslDB->data))
                {
                    $productssl['product'] = json_decode($productsslDB->data, true); 
                }
            }
        }
        
        if(!$productssl)
        {
            $productssl = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->getProduct($product->configuration()->text_name);
        }
        
        if($product->configuration()->text_name != '144')
        {
            if($productssl['product']['wildcard_enabled'])
            {
                if(strpos($decodedCSR['csrResult']['CN'], '*.') === false)
                {
                    if(isset($decodedCSR['csrResult']['errorMessage']))
                        throw new Exception($decodedCSR['csrResult']['errorMessage']);

                    throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('incorrectCSR'));
                }
            }
        }
        
        $mainDomain       = $decodedCSR['csrResult']['CN'];
        $domains = $mainDomain . PHP_EOL . $_POST['fields']['sans_domains'];
        $sansDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains(strtolower($domains));
        $this->fetchApprovalEmailsForSansDomains($sansDomains);        
        if(\MGModule\SSLCENTERWHMCS\eHelpers\Whmcs::isWHMCS73()) {
            if(isset($_POST['privateKey']) && $_POST['privateKey'] != null) {            
                $privKey = decrypt($_POST['privateKey']);
                $GenerateSCR = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\GenerateCSR($this->p, $_POST);
                $GenerateSCR->savePrivateKeyToDatabase($this->p['serviceid'], $privKey);  
            }
        }
    }

    public function fetchApprovalEmailsForSansDomains($sansDomains) {
        foreach ($sansDomains as $sansDomain) {
            $apiDomainEmails             = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($sansDomain);
            $this->domainsEmailApprovals[$sansDomain] = $apiDomainEmails['ComodoApprovalEmails'];
        }
        return $this->domainsEmailApprovals;
    }
}
