<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Illuminate\Database\Capsule\Manager as Capsule;
use Exception;

class SSLStepTwo {

    // allow *.domain.com as SAN for products
    const PRODUCTS_WITH_ADDITIONAL_SAN_VALIDATION = array(139, 100, 99, 63, 25, 24);
    
    private $p;
    private $errors = [];
    
    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        try {
            $this->SSLStepTwo();
            
        } catch (Exception $ex) {            
            return ['error' => $ex->getMessage()]; 
        }
        
        if (!empty($this->errors)) { 
            return ['error' => $this->errorsToWhmcsError()];
        }
        /*if(!isset($this->p['fields']['sans_domains']) || $this->p['fields']['sans_domains'] == '') {            
            $this->redirectToStepThree();                    
        }*/
        return ['approveremails' => 'loading...'];
    }
    public function setPrivateKey($privKey) {
        $this->p['privateKey'] = $privKey;
    }
    private function redirectToStepThree() {
        $tokenInput = generate_token();
        preg_match("/value=\"(.*)\\\"/", $tokenInput, $match);
        $token = $match[1];
        
        ob_clean();   
        header('Location: configuressl.php?cert='. $_GET['cert'] . '&step=3&token=' . $token);
        die();
    }
    private function SSLStepTwo() {
        \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSLTemplorary::getInstance()->setByParams($this->p);
        
        $this->storeFieldsAutoFill();        
        $this->validateSansDomains();
        $this->validateFields();
        if($this->p['configoption1'] != '144')
        {
            $this->validateCSR();
        }
        if(isset($this->p['privateKey']) && $this->p['privateKey'] != null) {            
            $privKey = decrypt($this->p['privateKey']);
            $GenerateSCR = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\GenerateCSR($this->p, $_POST);
            $GenerateSCR->savePrivateKeyToDatabase($this->p['serviceid'], $privKey);  
        }
      
    }
    
    private function validateSansDomains() {
        $sansDomains    = $this->p['fields']['sans_domains'];
        $sansDomains    = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomains);
        
        $apiProductId     = $this->p[ConfigOptions::API_PRODUCT_ID];
        
        $invalidDomains = \MGModule\SSLCENTERWHMCS\eHelpers\Domains::getInvalidDomains($sansDomains, in_array($apiProductId, self::PRODUCTS_WITH_ADDITIONAL_SAN_VALIDATION));
             
        if($apiProductId != '144') {
            
            if (count($invalidDomains)) {
                throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('incorrectSans') . implode(', ', $invalidDomains));
            }
            
        } else {
            
            $iperror = false;
            foreach($sansDomains as $domainname)
            {
                if(!filter_var($domainname, FILTER_VALIDATE_IP)) {
                    $iperror = true;
                }
            }
            
            if (count($invalidDomains) && $iperror) {
                throw new Exception('SANs are incorrect');
            }
            
        }
        
        
        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = (int) $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        $sansLimit = $includedSans + $boughtSans;
        if (count($sansDomains) > $sansLimit) {
            throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('sanLimitExceeded'));
        }
    }

    private function validateCSR() {
        $csr = trim(rtrim($this->p['csr']));
        $decodeCSR = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($csr);
        
        $_SESSION['decodeCSR'] = $decodeCSR;

        $productssl = false;
        $checkTable = Capsule::schema()->hasTable('mgfw_SSLCENTER_product_brand');
        if($checkTable)
        {
            if (Capsule::schema()->hasColumn('mgfw_SSLCENTER_product_brand', 'data'))
            {
                $productsslDB = Capsule::table('mgfw_SSLCENTER_product_brand')->where('pid', $this->p['configoption1'])->first();
                if(isset($productsslDB->data))
                {
                    $productssl['product'] = json_decode($productsslDB->data, true); 
                }
            }
        }
        
        if(!$productssl)
        {
            $productssl = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->getProduct($this->p['configoption1']);
        }
        
        if($productssl['product']['wildcard_enabled'])
        {
            if(strpos($decodeCSR['csrResult']['CN'], '*.') !== false)
            {
                return true;
            }
            else
            {
                if(isset($decodeCSR['csrResult']['errorMessage']))
                    throw new Exception($decodeCSR['csrResult']['errorMessage']);
                
                throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('incorrectCSR'));
            }
        }
        
        if(isset($decodeCSR['csrResult']['errorMessage'])) {
            
            if(isset($decodeCSR['csrResult']['CN']) && strpos($decodeCSR['csrResult']['CN'], '*.') !== false)
            {
                return true;
            }
            
            throw new Exception($decodeCSR['csrResult']['errorMessage']);
        }
    }
    
    private function validateFields() {
        if (empty(trim($this->p['jobtitle']))) {
            $this->errors[] = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('adminJobTitleMissing');
        }
        if (empty(trim($this->p['orgname']))) {
            $this->errors[] = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('organizationNameMissing');
        }
        if (empty(trim($this->p['fields']['order_type']))) {
            $this->errors[] = \MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('orderTypeMissing');
        }
    }
    
    private function storeFieldsAutoFill() {
        $fields = [];
        
        $a = ['servertype', 'csr', 'firstname', 'lastname', 'orgname',
            'jobtitle', 'email', 'address1', 'address2', 'city', 'state',
            'postcode', 'country', 'phonenumber','privateKey'];

        $b = [
            'order_type', 'sans_domains', 'org_name', 'org_division', 'org_duns', 'org_addressline1',
            'org_city', 'org_country', 'org_fax', 'org_phone', 'org_postalcode', 'org_regions'
        ];
        
        
        foreach ($a as $value) {
            $fields[] = [
                'name' => $value,
                'value' => $this->p[$value]
            ];
        } 
        foreach ($b as $value) {
            
            if($value == 'fields[order_type]') {
                $fields[] = [
                    'name' => sprintf('%s', $value),
                    'value' => $this->p['fields']['order_type']
                ];
            } else {
                $fields[] = [
                    'name' => sprintf('fields[%s]', $value),
                    'value' => $this->p['fields'][$value]
                ];
            }
            
        }   

        \MGModule\SSLCENTERWHMCS\eServices\FlashService::setFieldsMemory($_GET['cert'], $fields);
    }
    
    private function errorsToWhmcsError() {
        $i   = 0;
        $err = '';

        if (count($this->errors) === 1) {
            return $this->errors[0];
        }

        foreach ($this->errors as $error) {
            if ($i === 0) {
                $err .= $error . '</li>';
            } else {
                $err .= '<li>' . $error . '</li>';
            }
            $i++;
        }
        return $err;
    }
}
