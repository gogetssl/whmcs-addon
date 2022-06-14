<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

class SSLStepTwoJS {

    private $p;
    private $domainsEmailApprovals = [];
    private $brand = '';
    private $disabledValidationMethods = array();
    private $csrDecode = []; 

    function __construct(&$params, $csrdecode = []) {
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

            if(!$productssl['product']['dcv_email'])
            {
                array_push($this->disabledValidationMethods, 'email');
            }
            if(!$productssl['product']['dcv_dns'])
            {
                array_push($this->disabledValidationMethods, 'dns');
            }
            if(!$productssl['product']['dcv_http'])
            {
                array_push($this->disabledValidationMethods, 'http');
            }
            if(!$productssl['product']['dcv_https'])
            {
                array_push($this->disabledValidationMethods, 'https');
            }
            
//            if($product->configuration()->text_name == '144')
//            {
//                array_push($this->disabledValidationMethods, 'email');
//                array_push($this->disabledValidationMethods, 'dns');
//            }
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
        
        if(!isset($_SESSION['csrDecode']) || empty($_SESSION['csrDecode']))
        {
            $this->csrDecode   = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR(trim(rtrim($_POST['csr'])));
        }
        else 
        {
             $this->csrDecode = $_SESSION['csrDecode'];
             unset($_SESSION['csrDecode']);
        }
        
        $decodedCSR = $this->csrDecode;
        
        Capsule::table('tblhosting')->where('id', $this->p['serviceid'])->update([
            'domain' => $decodedCSR['csrResult']['CN']
        ]);
        
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
        
        $mainDomain = '';
        if(isset($decodedCSR['csrResult']['CN']))
        {
            $mainDomain       = $decodedCSR['csrResult']['CN'];
        }
        if(isset($decodedCSR['csrResult']['dnsName(s)'][0]))
        {
            $mainDomain = $decodedCSR['csrResult']['dnsName(s)'][0];
        }
        
        if($product->configuration()->text_name != '144')
        {
            if($productssl['product']['wildcard_enabled'])
            {
                if(strpos($mainDomain, '*.') === false)
                {
                    if(isset($decodedCSR['csrResult']['errorMessage']))
                        throw new Exception($decodedCSR['csrResult']['errorMessage']);

                    throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('incorrectCSR'));
                }
            }
        }

        $domains = $mainDomain . PHP_EOL . $_POST['fields']['sans_domains'];
        
        $sansDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains(strtolower($domains));
        $wildcardDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains(strtolower($_POST['fields']['wildcard_san']));
        
        if(isset($_SESSION['approveremails']) && !empty($_SESSION['approveremails']))
        {
            $this->domainsEmailApprovals = $_SESSION['approveremails'];
            unset($_SESSION['approveremails']);
        }
        else 
        {
            $this->fetchApprovalEmailsForSansDomains($sansDomains);
        }
        
        //$this->fetchApprovalEmailsForSansDomains($sansDomains);       
        $this->fetchApprovalEmailsForSansDomains($wildcardDomains);
        
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
            
            $this->domainsEmailApprovals[$sansDomain] = [];
            
            try{
            
                $apiDomainEmails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($sansDomain);
            
            } catch (\Exception $e) {
                
                continue;
                
            }
            
            $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
            if($apiConf->email_whois)
            {
                foreach($apiDomainEmails['ComodoApprovalEmails'] as $emailkey => $email)
                {
                    if (strpos($email, 'admin@') === false && 
                            strpos($email, 'administrator@') === false && 
                            strpos($email, 'hostmaster@') === false && 
                            strpos($email, 'postmaster@') === false && 
                            strpos($email, 'webmaster@') === false) 
                    {
                        unset($apiDomainEmails['ComodoApprovalEmails'][$emailkey]);
                        
                    }
                }
                $apiDomainEmails['ComodoApprovalEmails'] = array_values($apiDomainEmails['ComodoApprovalEmails']);
            }
            
            $this->domainsEmailApprovals[$sansDomain] = $apiDomainEmails['ComodoApprovalEmails'];
        }
        
        return $this->domainsEmailApprovals;
    }
}
