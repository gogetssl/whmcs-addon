<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class Renew {

    private $p;

    /**
     *
     * @var \MGModule\GGSSLWHMCS\eModels\whmcs\service\SSL
     */
    private $sslService;

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
            $this->renewCertificate();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        return 'success';

    }

    private function renewCertificate() {
        $this->loadSslService();
        $this->loadApiProduct();
        $addSSLRenewOrder = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLRenewOrder($this->getOrderParams());
    }

    private function loadSslService() {
        $ssl              = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $this->sslService = $ssl->getByServiceId($this->p['serviceid']);
        if (is_null($this->sslService)) {
            throw new Exception('Create has not been initialized');
        }
    }

    private function loadApiProduct() {
        $apiProductId     = $this->p[ConfigOptions::API_PRODUCT_ID];
        $apiRepo          = new \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products();
        $this->apiProduct = $apiRepo->getProduct($apiProductId);

    }

    private function getOrderParams() {        
        $billingPeriods = array(
            'Annually'  =>  12,
            'Biennially'  =>  24,
            'Triennially'  =>  36,
        );
        
        $p = &$this->sslService->configdata;
        $f = &$p['fields'];

        $order                   = [];
        $order['dcv_method']     = 'email';        
        $order['product_id']     = $this->p[ConfigOptions::API_PRODUCT_ID]; // Required
        $order['period']         = $billingPeriods[$this->p['model']['attributes']['billingcycle']];//$this->p[ConfigOptions::API_PRODUCT_MONTHS]; // Required
        $order['csr']            = $p['csr']; // Required
        $order['server_count']   = -1; // Required . amount of servers, for Unlimited pass “-1”
        $order['approver_email'] = $p['approveremail']; // Required . amount of servers, for Unlimited pass “-1”
        $order['webserver_type'] = $p['servertype']; // Required . webserver type, can be taken from getWebservers method

        $order['admin_firstname']    = $p['firstname']; // Required
        $order['admin_lastname']     = $p['lastname']; // Required
        $order['admin_organization'] = $p['orgname']; // required for OV SSL certificates
        $order['admin_title']        = $p['jobtitle']; // Required
        $order['admin_addressline1'] = $p['address1'];
        $order['admin_phone']        = $p['phonenumber']; // Required
        $order['admin_email']        = $p['email']; // Required
        $order['admin_city']         = $p['city']; // required for OV SSL certificates
        $order['admin_country']      = $p['country']; // required for OV SSL certificates
        $order['admin_postalcode']   = $p['postcode'];
        $order['admin_region']       = $p['state'];
        //$order['admin_fax']          = $cf['firstname']; // required for OV SSL certificates

        $apiConf                    = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();
        $order['tech_firstname']    = $apiConf->tech_firstname; // Required
        $order['tech_lastname']     = $apiConf->tech_lastname; // Required
        $order['tech_organization'] = $apiConf->tech_organization; // required for OV SSL certificates
        $order['tech_addressline1'] = $apiConf->tech_addressline1;
        $order['tech_phone']        = $apiConf->tech_phone; // Required
        $order['tech_title']        = $apiConf->tech_title; // Required
        $order['tech_email']        = $apiConf->tech_email; // Required
        $order['tech_city']         = $apiConf->tech_city; // required for OV SSL certificates
        $order['tech_country']      = $apiConf->tech_country; // required for OV SSL certificates
        $order['tech_fax']          = $apiConf->tech_fax;
        $order['tech_postalcode']   = $apiConf->tech_postalcode;
        $order['tech_region']       = $apiConf->tech_region;

        if ($this->apiProduct->isOrganizationRequired()) {
            $order['org_name']         = $f['org_name'];
            $order['org_division']     = $f['org_division'];
            $order['org_duns']         = $f['org_duns'];
            $order['org_addressline1'] = $f['org_addressline1'];
            $order['org_city']         = $f['org_city'];
            $order['org_country']      = $f['org_country'];
            $order['org_fax']          = $f['org_fax'];
            $order['org_phone']        = $f['org_phone'];
            $order['org_postalcode']   = $f['org_postalcode'];
            $order['org_region']       = $f['org_regions'];
        }

        if(!empty($f['sans_domains'])) {
            $order['dns_names']       = $f['sans_domains'];
        }
        
        if(!empty($f['approveremails'])) {
            $order['approver_emails'] = $f['approveremails'];
        }
        
        return $order;
    }
}
