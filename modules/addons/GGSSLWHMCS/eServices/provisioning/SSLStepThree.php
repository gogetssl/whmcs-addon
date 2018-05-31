<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class SSLStepThree {

    /**
     *
     * @var array 
     */
    private $p;
    
    /**
     *
     * @var \MGModule\GGSSLWHMCS\eModels\whmcs\service\SSL
     */
    private $sslConfig;

    /**
     *
     * @var \MGModule\GGSSLWHMCS\eModels\gogetssl\Product 
     */
    private $apiProduct;
    
    function __construct(&$params) {
        $this->p = &$params;
       
    }

    public function run() {
        try {
            $this->setMainDomainDcvMethod($_POST); 
            $this->setSansDomainsDcvMethod($_POST);               
            $this->SSLStepThree();
        } catch (Exception $ex) {            
            $this->redirectToStepOne($ex->getMessage());
        }
    }
    private function setMainDomainDcvMethod($post) {
        $this->p['fields']['dcv_method']  = $post['dcvmethodMainDomain']; 
    }

    private function setSansDomainsDcvMethod($post) {
        
        if(isset($post['dcvmethod']) && is_array($post['dcvmethod'])) {            
            $this->p['sansDomansDcvMethod'] = $post['dcvmethod'];
        }
    }
    private function SSLStepThree() {
        $this->loadSslConfig();
        $this->loadApiProduct();
        $this->orderCertificate();
    }
    
    private function loadSslConfig() {
        $repo = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $this->sslConfig  = $repo->getByServiceId($this->p['serviceid']);
        if (is_null($this->sslConfig)) {
            throw new Exception('Record for ssl service not exist.');
        }
    }
    
    private function loadApiProduct() {
        $apiProductId     = $this->p[ConfigOptions::API_PRODUCT_ID];
       
        $apiRepo          = new \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products();
        $this->apiProduct = $apiRepo->getProduct($apiProductId);
    }

    private function orderCertificate() { 
        
        $order               = [];
        $order['dcv_method'] = strtolower($this->p['fields']['dcv_method']);
       
        $order['product_id'] = $this->p[ConfigOptions::API_PRODUCT_ID]; // Required
        $order['period']     = $this->p[ConfigOptions::API_PRODUCT_MONTHS]; // Required
        $order['csr']        = $this->p['csr']; // Required
        $order['server_count']       = -1; // Required . amount of servers, for Unlimited pass “-1”
        $order['approver_email']     = ($order['dcv_method'] == 'email') ? $this->p['approveremail'] : ''; // Required . amount of servers, for Unlimited pass “-1”
        $order['webserver_type']     = $this->p['servertype']; // Required . webserver type, can be taken from getWebservers method
        
        
                        
        $order['admin_firstname']    = $this->p['firstname']; // Required
        $order['admin_lastname']     = $this->p['lastname']; // Required
        $order['admin_organization'] = $this->p['orgname']; // required for OV SSL certificates
        $order['admin_title']        = $this->p['jobtitle']; // Required
        $order['admin_addressline1'] = $this->p['address1'];
        $order['admin_phone']        = $this->p['phonenumber']; // Required
        $order['admin_email']        = $this->p['email']; // Required
        $order['admin_city']         = $this->p['city']; // required for OV SSL certificates
        $order['admin_country']      = $this->p['country']; // required for OV SSL certificates
        $order['admin_postalcode']   = $this->p['postcode'];
        $order['admin_region']       = $this->p['state'];
        //$order['admin_fax']          = $cf['firstname']; // required for OV SSL certificates

        $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();
        
        $useAdminContact = $apiConf->use_admin_contact;
        
        $order['tech_firstname']    = ($useAdminContact) ? $order['admin_firstname'] : $apiConf->tech_firstname; // Required
        $order['tech_lastname']     = ($useAdminContact) ? $order['admin_lastname']:$apiConf->tech_lastname; // Required
        $order['tech_organization'] = ($useAdminContact) ? $order['admin_organization'] : $apiConf->tech_organization; // required for OV SSL certificates
        $order['tech_addressline1'] = ($useAdminContact) ? $order['admin_addressline1'] : $apiConf->tech_addressline1;
        $order['tech_phone']        = ($useAdminContact) ? $order['admin_phone'] : $apiConf->tech_phone; // Required
        $order['tech_title']        = ($useAdminContact) ? $order['admin_title'] : $apiConf->tech_title; // Required
        $order['tech_email']        = ($useAdminContact) ? $order['admin_email'] : $apiConf->tech_email; // Required
        $order['tech_city']         = ($useAdminContact) ? $order['admin_city'] : $apiConf->tech_city; // required for OV SSL certificates
        $order['tech_country']      = ($useAdminContact) ? $order['admin_country'] : $apiConf->tech_country; // required for OV SSL certificates
        $order['tech_fax']          = ($useAdminContact) ? '' : $apiConf->tech_fax;
        $order['tech_postalcode']   = ($useAdminContact) ? $order['admin_postalcode'] : $apiConf->tech_postalcode;
        $order['tech_region']       = ($useAdminContact) ? $order['admin_region'] : $apiConf->tech_region;
        
        if ($this->apiProduct->isOrganizationRequired()) {
            $org                       = &$this->p['fields'];
            $order['org_name']         = $org['org_name'];
            $order['org_division']     = $org['org_division'];
            $order['org_duns']         = $org['org_duns'];
            $order['org_addressline1'] = $org['org_addressline1'];
            $order['org_city']         = $org['org_city'];
            $order['org_country']      = $org['org_country'];
            $order['org_fax']          = $org['org_fax'];
            $order['org_phone']        = $org['org_phone'];
            $order['org_postalcode']   = $org['org_postalcode'];
            $order['org_region']       = $org['org_regions'];
        }
        
        $sanEnabledForWHMCSProduct = $this->p[ConfigOptions::PRODUCT_ENABLE_SAN] === 'on';
        
        if ($sanEnabledForWHMCSProduct AND count($_POST['approveremails'])) {
            
            $sansDomains = $this->p['configdata']['fields']['sans_domains'];
            $sansDomains = \MGModule\GGSSLWHMCS\eHelpers\SansDomains::parseDomains($sansDomains);
            //if entered san is the same as main domain
            if(count($sansDomains) != count($_POST['approveremails'])) {
                $decodedCSR   = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->p['csr']);
                foreach($sansDomains as $key => $domain) {                    
                    if($decodedCSR['csrResult']['CN'] == $domain) {
                        unset($sansDomains[$key]);   
                    }                     
                }
            }
            $order['dns_names']       = implode(',', $sansDomains);
            $order['approver_emails'] = implode(',', $_POST['approveremails']);
            
            if(!empty($sanDcvMethods = $this->getSansDomainsValidationMethods())) {
                $i = 0;
                foreach($_POST['approveremails'] as $domain => $approveremail) {
                    if($sanDcvMethods[$i] != 'EMAIL') {
                        $_POST['approveremails']["$domain"] = strtolower($sanDcvMethods[$i]);
                    }
                    $i++;
                }
                $order['approver_emails'] = implode(',', $_POST['approveremails']);
            } 
            
            $apiRepo       = new \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products();
            $apiProduct    = $apiRepo->getProduct($order['product_id']);
        }
        
        $addedSSLOrder = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLOrder($order);
        
        
        $this->sslConfig->setRemoteId($addedSSLOrder['order_id']);        
        $this->sslConfig->setApproverEmails($order['approver_emails']); 
        //$this->sslConfig->setApproverEmails($order['approver_emails']); 
        $this->sslConfig->save();
        
        \MGModule\GGSSLWHMCS\eServices\FlashService::set('GGSSL_WHMCS_SERVICE_TO_ACTIVE', $this->p['serviceid']);
    }
    private function getSansDomainsValidationMethods() {  
        $data = [];
        foreach ($this->p['sansDomansDcvMethod'] as  $newMethod) { 
            $data[] = $newMethod;   
        }
        return $data;
    }

    private function redirectToStepOne($error) {
        $_SESSION['GGSSLWHMCS_FLASH_ERROR_STEP_ONE'] = $error;
        header('Location: configuressl.php?cert='. $_GET['cert']);
        die();
    }
}
