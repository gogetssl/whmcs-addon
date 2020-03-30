<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;
use \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service as Service;
use \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product as Product;
class SSLStepThree {

    /**
     *
     * @var array 
     */
    private $p;
    
    /**
     *
     * @var \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL
     */
    private $sslConfig;
    
    private $invoiceGenerator;
    
    /**
     *
     * @var \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product 
     */
    private $apiProduct;
    
    function __construct(&$params) {
        $this->p = &$params;
        if(!isset($this->p['model'])) {
            $this->p['model'] = \WHMCS\Service\Service::find($this->p['serviceid']);
        }
        
        $this->invoiceGenerator = new \MGModule\SSLCENTERWHMCS\eHelpers\Invoice();
    }

    public function run() {
        try {
            \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::decodeSanAprroverEmailsAndMethods($_POST);      
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
        $repo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $this->sslConfig  = $repo->getByServiceId($this->p['serviceid']);
        if (is_null($this->sslConfig)) {
            throw new Exception('Record for ssl service not exist.');
        }
    }
    
    private function loadApiProduct() {
        $apiProductId     = $this->p[ConfigOptions::API_PRODUCT_ID];
       
        $apiRepo          = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $this->apiProduct = $apiRepo->getProduct($apiProductId);
    }

    private function orderCertificate() { 
        
        if(isset($_POST['approveremail']) && $_POST['approveremail'] == 'defaultemail@defaultemail.com')
        {
            unset($_POST['approveremail']);
        }
        
        $billingPeriods = array(
            'Free Account'  =>  $this->p[ConfigOptions::API_PRODUCT_MONTHS],
            'One Time'      =>  $this->p[ConfigOptions::API_PRODUCT_MONTHS],
            'Monthly'       =>  12,
            'Quarterly'     =>  3,
            'Semi-Annually' =>  6,
            'Annually'      =>  12,
            'Biennially'    =>  24,
            'Triennially'   =>  36,
        );
        
        $brandsWithOnlyEmailValidation = ['geotrust','thawte','rapidssl','symantec'];        
        if(!empty($this->p[ConfigOptions::API_PRODUCT_ID])) {
            $apiRepo       = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
            $apiProduct    = $apiRepo->getProduct($this->p[ConfigOptions::API_PRODUCT_ID]);
            $brand = $apiProduct->brand;
            
            //get available periods for product
            $productAvailavlePeriods = $apiProduct->getPeriods();
            //if certificate have monthly billing cycle available
            if(in_array('1', $productAvailavlePeriods)) {
                $billingPeriods['Monthly'] = 1;
            } else {
                if(!in_array('12', $productAvailavlePeriods)) {
                    $billingPeriods['Monthly'] = $productAvailavlePeriods[0];
                }                
            }
            
            //one time billing set period to 12 months if avaiable else leave max period
            if(in_array('12', $productAvailavlePeriods)) {
                $billingPeriods['One Time'] = 12;
            }
        }
        
        if($this->p[ConfigOptions::MONTH_ONE_TIME] && !empty($this->p[ConfigOptions::MONTH_ONE_TIME]))
        {
            $billingPeriods['One Time'] = $this->p[ConfigOptions::MONTH_ONE_TIME];
        }

        $order               = [];
        $order['dcv_method'] = strtolower($this->p['fields']['dcv_method']);
       
        $order['product_id'] = $this->p[ConfigOptions::API_PRODUCT_ID]; // Required
        $order['period']     = $billingPeriods[$this->p['model']['attributes']['billingcycle']];//$this->p[ConfigOptions::API_PRODUCT_MONTHS]; // Required  
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

        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        
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
            $order['org_country']      = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountryCodeByName($org['org_country']);
            $order['org_fax']          = $org['org_fax'];
            $order['org_phone']        = $org['org_phone'];
            $order['org_postalcode']   = $org['org_postalcode'];
            $order['org_region']       = $org['org_regions'];
        }

        $sanEnabledForWHMCSProduct = $this->p[ConfigOptions::PRODUCT_ENABLE_SAN] === 'on';
        
        $decodedCSR   = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->p['csr']);
        if ($sanEnabledForWHMCSProduct AND count($_POST['approveremails'])) {
            
            $sansDomains = $this->p['configdata']['fields']['sans_domains'];
            $sansDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomains);
            //if entered san is the same as main domain
            if(count($sansDomains) != count($_POST['approveremails'])) {
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
            
            $apiRepo       = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
            $apiProduct    = $apiRepo->getProduct($order['product_id']);
        }
        //if brand is 'geotrust','thawte','rapidssl','symantec' do not send dcv method for sans
        if(in_array($brand, $brandsWithOnlyEmailValidation)) {
            unset($order['approver_emails']);
        }
        
        $orderType = $this->p['fields']['order_type'];        
        switch ($orderType)
        {
            case 'renew':
                $addedSSLOrder = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLRenewOrder($order);
                break;            
            case 'new':
            default:                
                $addedSSLOrder = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLOrder($order);
                break;
        }
        //update domain column in tblhostings
        $service = new Service($this->p['serviceid']);
        $service->save(array('domain' => $decodedCSR['csrResult']['CN']));
        
        $orderDetails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($addedSSLOrder['order_id']);
        
        if($this->p[ConfigOptions::MONTH_ONE_TIME] && !empty($this->p[ConfigOptions::MONTH_ONE_TIME]))
        {
            $service = new Service($this->p['serviceid']);
            $service->save(array('termination_date' => $orderDetails['valid_till']));
        }
        
        // logModuleCall(
        //     'SSLCENTERWHMCS',
        //     'CREATE',
        //     $order,
        //     $addedSSLOrder
        // );
        // logModuleCall(
        //     'SSLCENTERWHMCS',
        //     'CREATE',
        //     $order,
        //     $orderDetails
        // );
        $this->sslConfig->setRemoteId($addedSSLOrder['order_id']); 
        $this->sslConfig->setApproverEmails($order['approver_emails']);
       
        $this->sslConfig->setCa($orderDetails['ca_code']);
        $this->sslConfig->setCrt($orderDetails['crt_code']);
        $this->sslConfig->setPartnerOrderId($orderDetails['partner_order_id']);
        $this->sslConfig->setValidFrom($orderDetails['valid_from']);
        $this->sslConfig->setValidTill($orderDetails['valid_till']);
        $this->sslConfig->setDomain($orderDetails['domain']);
        $this->sslConfig->setOrderStatusDescription($orderDetails['status_description']);
        $this->sslConfig->setApproverMethod($orderDetails['approver_method']);
        $this->sslConfig->setDcvMethod($orderDetails['dcv_method']);
        $this->sslConfig->setProductId($orderDetails['product_id']);
        $this->sslConfig->setSanDetails($orderDetails['san']);
        //$this->sslConfig->setSanDetails($orderDetails['san']);
        $this->sslConfig->setSSLStatus($orderDetails['status']);
        $this->sslConfig->save();   
        //try to mark previous order as completed if it is autoinvoiced and autocreated product
        $this->invoiceGenerator->markPreviousOrderAsCompleted($this->p['serviceid']);
        
        \MGModule\SSLCENTERWHMCS\eServices\FlashService::set('SSLCENTER_WHMCS_SERVICE_TO_ACTIVE', $this->p['serviceid']);
        
        \MGModule\SSLCENTERWHMCS\eHelpers\Invoice::insertDomainInfoIntoInvoiceItemDescription($this->p['serviceid'], $decodedCSR['csrResult']['CN']);
    }   
        
    private function getSansDomainsValidationMethods() {  
        $data = [];
        foreach ($this->p['sansDomansDcvMethod'] as  $newMethod) { 
            $data[] = $newMethod;   
        }
        return $data;
    }

    private function redirectToStepOne($error) {
        $_SESSION['SSLCENTERWHMCS_FLASH_ERROR_STEP_ONE'] = $error;
        header('Location: configuressl.php?cert='. $_GET['cert']);
        die();
    }
}
