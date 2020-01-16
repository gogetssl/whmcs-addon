<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class Renew {

    private $p;

    /**
     *
     * @var \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL
     */
    private $sslService;

    /**
     *
     * @var \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product 
     */
    private $apiProduct;

    function __construct(&$params) {
        $this->p = &$params;

    }

    public function run() {

        $apiConf           = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        if(isset($apiConf->renew_new_order) && $apiConf->renew_new_order == '1')
        {
            return 'This action cannot be called, it will only be called when paying for a renew invoice. If you want to run this action manually please uncheck the "Renew - New Order" option in the SSLCENTER module settings.';
        }
        
        try {
            $this->renewCertificate();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        return 'success';

    }

    private function renewCertificate() {
        $this->loadSslService();
        $this->loadApiProduct();
     
        $addSSLRenewOrder = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLRenewOrder($this->getOrderParams());        
    
        $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($this->sslService);
        $configDataUpdate->run();
    }

    private function loadSslService() {
        $ssl              = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $this->sslService = $ssl->getByServiceId($this->p['serviceid']);
        if (is_null($this->sslService)) {
            throw new Exception('Create has not been initialized');
        }
    }

    private function loadApiProduct() {
        $apiProductId     = $this->p[ConfigOptions::API_PRODUCT_ID];
        $apiRepo          = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $this->apiProduct = $apiRepo->getProduct($apiProductId);

    }

    private function getOrderParams() {        
        $billingPeriods = array(
            'Free Account'  =>  $this->p[ConfigOptions::API_PRODUCT_MONTHS],
            'One Time'  =>  $this->p[ConfigOptions::API_PRODUCT_MONTHS],
            'Monthly'       =>  1,
            'Quarterly'     =>  3,
            'Semi-Annually' =>  6,
            'Annually'      =>  12,
            'Biennially'    =>  24,
            'Triennially'   =>  36,
        );
        
        $p = &$this->sslService->configdata;
        $f = &$p->fields;

        if(!isset($p->firstname) || empty($p->firstname))
        {
            $p->firstname = $this->p['model']->client->firstname;
        }
        
        if(!isset($p->lastname) || empty($p->lastname))
        {
            $p->lastname = $this->p['model']->client->lastname;
        }
        
        if(!isset($p->orgname) || empty($p->orgname))
        {
            $p->orgname = $this->p['model']->client->companyname;
        }
        
        if(!isset($p->address1) || empty($p->address1))
        {
            $p->address1 = $this->p['model']->client->address1;
        }
        
        if(!isset($p->phonenumber) || empty($p->phonenumber))
        {
            $p->phonenumber = $this->p['model']->client->phonenumber;
        }
        
        if(!isset($p->email) || empty($p->email))
        {
            $p->email = $this->p['model']->client->email;
        }
        
        if(!isset($p->city) || empty($p->city))
        {
            $p->city = $this->p['model']->client->city;
        }
        
        if(!isset($p->country) || empty($p->country))
        {
            $p->country = $this->p['model']->client->country;
        }
        
        if(!isset($p->postcode) || empty($p->postcode))
        {
            $p->postcode = $this->p['model']->client->postcode;
        }
        
        if(!isset($p->state) || empty($p->state))
        {
            $p->state = $this->p['model']->client->state;
        }

        $order                   = [];
        $order['dcv_method']     = $p->dcv_method;        
        $order['product_id']     = $this->p[ConfigOptions::API_PRODUCT_ID]; // Required
        $order['period']         = $billingPeriods[$this->p['model']['attributes']['billingcycle']];//$this->p[ConfigOptions::API_PRODUCT_MONTHS]; // Required
        $order['csr']            = $p->csr; // Required
        $order['server_count']   = -1; // Required . amount of servers, for Unlimited pass “-1”
        
        if($p->dcv_method == 'email')
        {
            $order['approver_email'] = $p->approveremail; // Required . amount of servers, for Unlimited pass “-1”
        }
        else 
        {
            $order['approver_method'] = $p->approver_method;
        }
        
        $order['webserver_type'] = $p->servertype; // Required . webserver type, can be taken from getWebservers method

        $order['admin_firstname']    = $p->firstname; // Required
        $order['admin_lastname']     = $p->lastname; // Required
        $order['admin_organization'] = $p->orgname; // required for OV SSL certificates
        $order['admin_title']        = $p->jobtitle; // Required
        $order['admin_addressline1'] = $p->address1;
        $order['admin_phone']        = $p->phonenumber; // Required
        $order['admin_email']        = $p->email; // Required
        $order['admin_city']         = $p->city; // required for OV SSL certificates
        $order['admin_country']      = $p->country; // required for OV SSL certificates
        $order['admin_postalcode']   = $p->postcode;
        $order['admin_region']       = $p->state;
        //$order['admin_fax']          = $cf['firstname']; // required for OV SSL certificates

        
        //id use administrative unchecked get tech contsact details from module configuration otherwise use client details
        $apiConf                    = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $useAdminContact = $apiConf->use_admin_contact;
       
        $order['tech_firstname']    = ($useAdminContact) ? $p->firstname : $apiConf->tech_firstname; // Required
        $order['tech_lastname']     = ($useAdminContact) ? $p->lastname : $apiConf->tech_lastname; // Required
        $order['tech_organization'] = ($useAdminContact) ? $p->orgname : $apiConf->tech_organization; // required for OV SSL certificates
        $order['tech_addressline1'] = ($useAdminContact) ? $p->address1 : $apiConf->tech_addressline1;
        $order['tech_phone']        = ($useAdminContact) ? $p->phonenumber : $apiConf->tech_phone; // Required
        $order['tech_title']        = ($useAdminContact) ? $p->jobtitle : $apiConf->tech_title; // Required
        $order['tech_email']        = ($useAdminContact) ? $p->email : $apiConf->tech_email; // Required
        $order['tech_city']         = ($useAdminContact) ? $p->city : $apiConf->tech_city; // required for OV SSL certificates
        $order['tech_country']      = ($useAdminContact) ? $p->country : $apiConf->tech_country; // required for OV SSL certificates
        $order['tech_fax']          = ($useAdminContact) ? '' : $apiConf->tech_fax;
        $order['tech_postalcode']   = ($useAdminContact) ? $p->postcode : $apiConf->tech_postalcode;
        $order['tech_region']       = ($useAdminContact) ? $p->state : $apiConf->tech_region;
 
        if ($this->apiProduct->isOrganizationRequired()) {
            $order['org_name']         = $f->org_name;
            $order['org_division']     = $f->org_division;
            $order['org_duns']         = $f->org_duns;
            $order['org_addressline1'] = $f->org_addressline1;
            $order['org_city']         = $f->org_city;
            $order['org_country']      = $f->org_country;
            $order['org_fax']          = $f->org_fax;
            $order['org_phone']        = $f->org_phone;
            $order['org_postalcode']   = $f->org_postalcode;
            $order['org_region']       = $f->org_regions;
        }
        
        if(!empty($f->sans_domains)) {
            $order['dns_names']       = $f->sans_domains;
        }
        
        if(!empty($f->approveremails)) {
            $order['approver_emails'] = $f->approveremails;
        }
        
        return $order;
    }
}
