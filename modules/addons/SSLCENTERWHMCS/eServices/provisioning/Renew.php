<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use WHMCS\Database\Capsule;
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
            if(isset($apiConf->automatic_processing_of_renewal_orders) && $apiConf->automatic_processing_of_renewal_orders == '1')
            {
                try {
                    $this->checkRenew($this->p['serviceid']);
                    $this->renewCertificate();
                    $this->updateOneTime();
                } catch (Exception $ex) {
                    return $ex->getMessage();
                }
                $this->addRenew($this->p['serviceid']);
                return 'success';
            }
            else
            {
                Capsule::table('tblsslorders')->where('serviceid', $this->p['serviceid'])->update(array(
                    'remoteid' => '',
                    'configdata' => '',
                    'completiondate' => '0000-00-00 00:00:00',
                    'status' => 'Awaiting Configuration'
                ));
                return 'success';
            }
        }
        else 
        {
            return 'This action cannot be called, it will only be called when paying for a renew invoice. If you want to run this action manually please check the "Renew order via existing order" option in the SSLCENTER module settings.';
        }

    }
    
    private function updateOneTime()
    {
        $serviceID = $this->p['serviceid'];
        
        $service = (array)Capsule::table('tblhosting')->where('id', $serviceID)->first();
        $product = (array)Capsule::table('tblproducts')->where('servertype', 'SSLCENTERWHMCS')->where('id', $service['packageid'])->first();
        
        $sslOrder = (array)Capsule::table('tblsslorders')->where('serviceid', $serviceID)->first();
        $configdata = json_decode($sslOrder['configdata'], true);

        if(isset($product['configoption7']) && !empty($product['configoption7']) && $service['billingcycle'] == 'One Time')
        {
            Capsule::table('tblhosting')->where('id', $serviceID)->update(array('termination_date' => $configdata['valid_till']));
        }
    }
    
    private function createRenewTable()
    {
        $checkTable = Capsule::schema()->hasTable('mgfw_SSLCENTER_renew');
        if($checkTable === false)
        {
            Capsule::schema()->create('mgfw_SSLCENTER_renew', function ($table) {
                $table->increments('id');
                $table->integer('serviceid');
                $table->dateTime('date');
            });
        }
    }

    private function checkRenew($serviceid)
    {
        $this->createRenewTable();
        
        $renew = Capsule::table('mgfw_SSLCENTER_renew')->where('serviceid', $serviceid)->where('date', 'like', date('Y-m-d H').'%')->first();
        
        if(isset($renew->id) && !empty($renew->id))
        {
            throw new Exception('Block double renew.');
        }
    }
    
    private function addRenew($serviceid)
    {
        $this->createRenewTable();
        
        $renew = Capsule::table('mgfw_SSLCENTER_renew')->where('serviceid', $serviceid)->first();
        
        if(isset($renew->id) && !empty($renew->id))
        {
            Capsule::table('mgfw_SSLCENTER_renew')->where('serviceid', $serviceid)->update(array(
                'date' => date('Y-m-d H:i:s')
            ));
        }
        else
        {
            Capsule::table('mgfw_SSLCENTER_renew')->insert(array(
                'serviceid' => $serviceid,
                'date' => date('Y-m-d H:i:s')
            ));
        }
        
    }

    private function renewCertificate() {
        $this->loadSslService();
        $this->loadApiProduct();
     
        $addSSLRenewOrder = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLRenewOrder($this->getOrderParams()); 
        $service = Capsule::table('tblhosting')->where('id', $this->p['serviceid'])->first();

        // dns manager
        sleep(2);
        $checkTable = Capsule::schema()->hasTable('dns_manager2_zone');
        $dnsmanagerfile = dirname(dirname(dirname(dirname(dirname(__DIR__))))).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'dnsmanager.php';
        if(file_exists($dnsmanagerfile) && $checkTable !== false)
        {
            $zoneDomain = $service->domain;
            $loaderDNS = dirname(dirname(dirname(dirname(dirname(__DIR__))))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.'DNSManager2'.DIRECTORY_SEPARATOR.'loader.php';
            if(file_exists($loaderDNS)) {
                require_once $loaderDNS;
                $loader = new \MGModule\DNSManager2\loader();
                \MGModule\DNSManager2\addon::I(true);
                $helper = new \MGModule\DNSManager2\mgLibs\custom\helpers\DomainHelper($service->domain);
                $zoneDomain = $helper->getDomainWithTLD();
            }

            $records = [];
            if(isset($addSSLRenewOrder['approver_method']['dns']['record']) && !empty($addSSLRenewOrder['approver_method']['dns']['record']))
            {
                
                if (strpos($addSSLRenewOrder['approver_method']['dns']['record'], 'CNAME') !== false) 
                {
                    $dnsrecord = explode("CNAME", $addSSLRenewOrder['approver_method']['dns']['record']);
                    $records[] = array(
                        'name' => trim(rtrim($dnsrecord[0])).'.',
                        'type' => 'CNAME',
                        'ttl' => '3600',
                        'data' => trim(rtrim($dnsrecord[1]))
                    );
                }
                else
                {
                    $dnsrecord = explode("IN   TXT", $addSSLRenewOrder['approver_method']['dns']['record']);
                    $length = strlen(trim(rtrim($dnsrecord[1])));
                    $records[] = array(
                        'name' => trim(rtrim($dnsrecord[0])).'.',
                        'type' => 'TXT',
                        'ttl' => '14440',
                        'data' => substr(trim(rtrim($dnsrecord[1])),1, $length-2)
                    );
                }

                $zone = Capsule::table('dns_manager2_zone')->where('name', $zoneDomain)->first();
                if(!isset($zone->id) || empty($zone->id))
                {
                    $postfields = array(
                        'action' => 'dnsmanager',
                        'dnsaction' => 'createZone',
                        'zone_name' => $zoneDomain,
                        'type' => '2',
                        'relid' => $this->p['serviceid'],
                        'zone_ip' => '',
                        'userid' => $this->p['userid']
                    );
                    $createZoneResults = localAPI('dnsmanager' ,$postfields);
                    logModuleCall('sslcenter [dns]', 'createZone', print_r($postfields, true), print_r($createZoneResults, true));
                }

                $zone = Capsule::table('dns_manager2_zone')->where('name', $zoneDomain)->first();
                if(isset($zone->id) && !empty($zone->id))
                {
                    $postfields =  array(
                        'dnsaction' => 'createRecords',
                        'zone_id' => $zone->id,
                        'records' => $records);
                    $createRecordCnameResults = localAPI('dnsmanager' ,$postfields);
                    logModuleCall('sslcenter [dns]', 'updateZone', print_r($postfields, true), print_r($createRecordCnameResults, true));
                }

            }
            if(isset($addSSLRenewOrder['san']) && !empty($addSSLRenewOrder['san']))
            {
                foreach($addSSLRenewOrder['san'] as $sanrecord)
                {
                    $records = [];
                    if(isset($sanrecord['validation']['dns']['record']) && !empty($sanrecord['validation']['dns']['record']))
                    {
                        if(file_exists($loaderDNS)) {
                            $helper = new \MGModule\DNSManager2\mgLibs\custom\helpers\DomainHelper(str_replace('*.', '',$sanrecord['san_name']));
                            $zoneDomain = $helper->getDomainWithTLD();
                        }

                        if (strpos($sanrecord['validation']['dns']['record'], 'CNAME') !== false) 
                        {
                            $dnsrecord = explode("CNAME", $sanrecord['validation']['dns']['record']);
                            $records[] = array(
                                'name' => trim(rtrim($dnsrecord[0])).'.',
                                'type' => 'CNAME',
                                'ttl' => '3600',
                                'data' => trim(rtrim($dnsrecord[1]))
                            );
                        }
                        else
                        {
                            $dnsrecord = explode("IN   TXT", $sanrecord['validation']['dns']['record']);
                            $length = strlen(trim(rtrim($dnsrecord[1])));
                            $records[] = array(
                                'name' => trim(rtrim($dnsrecord[0])).'.',
                                'type' => 'TXT',
                                'ttl' => '14440',
                                'data' => substr(trim(rtrim($dnsrecord[1])),1, $length-2)
                            );
                        }

                        $zone = Capsule::table('dns_manager2_zone')->where('name', $zoneDomain)->first();
                        if(!isset($zone->id) || empty($zone->id))
                        {
                            $postfields = array(
                                'action' => 'dnsmanager',
                                'dnsaction' => 'createZone',
                                'zone_name' => $zoneDomain,
                                'type' => '2',
                                'relid' => $this->p['serviceid'],
                                'zone_ip' => '',
                                'userid' => $this->p['userid']
                            );
                            $createZoneResults = localAPI('dnsmanager' ,$postfields);
                            logModuleCall('sslcenter [dns]', 'createZone', print_r($postfields, true), print_r($createZoneResults, true));
                        }

                        $zone = Capsule::table('dns_manager2_zone')->where('name', $zoneDomain)->first();
                        if(isset($zone->id) && !empty($zone->id))
                        {
                            $postfields =  array(
                                'dnsaction' => 'createRecords',
                                'zone_id' => $zone->id,
                                'records' => $records);
                            $createRecordCnameResults = localAPI('dnsmanager' ,$postfields);
                            logModuleCall('sslcenter [dns]', 'updateZone', print_r($postfields, true), print_r($createRecordCnameResults, true));
                        }

                    }
                }
            }
        }

        Capsule::table('tblsslorders')->where('serviceid', $this->p['serviceid'])->update(array(
            'remoteid' => $addSSLRenewOrder['order_id']
        ));     
        $this->loadSslService();        

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
                
        if($this->p[ConfigOptions::MONTH_ONE_TIME] && !empty($this->p[ConfigOptions::MONTH_ONE_TIME]))
        {
            $billingPeriods['One Time'] = $this->p[ConfigOptions::MONTH_ONE_TIME];
        }
        
        $p = &$this->sslService->configdata;
        $f = new \stdClass();
        if(isset($p->fields)) {
            $f = &$p->fields;
        }

        if(!isset($p->firstname) || empty($p->firstname))
        {
            $p->firstname = $this->p['clientsdetails']['firstname'];
        }

        if(!isset($p->lastname) || empty($p->lastname))
        {
            $p->lastname = $$this->p['clientsdetails']['lastname'];
        }

        if(!isset($p->orgname) || empty($p->orgname))
        {
            $p->orgname = $this->p['clientsdetails']['companyname'];
        }

        if(!isset($p->address1) || empty($p->address1))
        {
            $p->address1 = $this->p['clientsdetails']['address1'];
        }

        if(!isset($p->phonenumber) || empty($p->phonenumber))
        {
            $p->phonenumber = $this->p['clientsdetails']['phonenumber'];
        }

        if(!isset($p->email) || empty($p->email))
        {
            $p->email = $this->p['clientsdetails']['email'];
        }

        if(!isset($p->city) || empty($p->city))
        {
            $p->city = $this->p['clientsdetails']['city'];
        }

        if(!isset($p->country) || empty($p->country))
        {
            $p->country = $this->p['clientsdetails']['country'];
        }

        if(!isset($p->postcode) || empty($p->postcode))
        {
            $p->postcode = $this->p['clientsdetails']['postcode'];
        }

        if(!isset($p->state) || empty($p->state))
        {
            $p->state = $this->p['clientsdetails']['state'];
        }

        $order                   = [];
        $order['dcv_method']     = $p->dcv_method;        
        $order['product_id']     = $this->p[ConfigOptions::API_PRODUCT_ID]; // Required
        $order['period']         = $billingPeriods[$this->p['model']->billingcycle];//$this->p[ConfigOptions::API_PRODUCT_MONTHS]; // Required
        $order['csr']            = $p->csr; // Required
        $order['server_count']   = -1; // Required . amount of servers, for Unlimited pass “-1”
        
        if($p->dcv_method == 'email')
        {
            $order['approver_email'] = $p->approveremail; // Required . amount of servers, for Unlimited pass “-1”
            if(empty($order['approver_email']) && isset($p->approver_method->email))
            {
                $order['approver_email'] = $p->approver_method->email;
            }
        }
        else 
        {
            $order['approver_method'] = $p->dcv_method;
        }
        
        $order['webserver_type'] = $p->servertype; // Required . webserver type, can be taken from getWebservers method
        
        $apiRepo       = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $apiProduct    = $apiRepo->getProduct($this->p[ConfigOptions::API_PRODUCT_ID]);
        $brand = $apiProduct->brand;
        $orgRequired = $apiProduct->org_required;
        
        if($brand == 'geotrust' || $brand == 'rapidssl' || $brand == 'digicert' || $brand == 'thawte')
        {
            $order['webserver_type']     = '18'; // Required . webserver type, can be taken from getWebservers method
        }
        else
        {
            $order['webserver_type']     = '-1'; // Required . webserver type, can be taken from getWebservers method
        }

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

        if ($orgRequired && isset($f->org_name)) {
            $order['org_name']         = $f->org_name;
            $order['org_division']     = $f->org_division;
            $order['org_duns']         = $f->org_duns;
            $order['org_addressline1'] = $f->org_addressline1;
            $order['org_city']         = $f->org_city;
            if(strlen($f->org_country) != 2)
            {
                $order['org_country']      = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountryCodeByName($f->org_country);
            }
            else
            {
                $order['org_country']      = $f->org_country;
            }
            $order['org_fax']          = $f->org_fax;
            $order['org_phone']        = $f->org_phone;
            $order['org_postalcode']   = $f->org_postalcode;
            $order['org_region']       = $f->org_regions;
        }
        elseif ($orgRequired) {
            $order['org_name']         = $p->orgname;
//            $order['org_division']     = $f->org_division;
//            $order['org_duns']         = $f->org_duns;
            $order['org_addressline1'] = $p->address1;
            $order['org_city']         = $p->city;
            $order['org_country']      = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountryCodeByName($p->country);
            $order['org_fax']          = $apiConf->tech_fax;
            $order['org_phone']        = $p->phonenumber;
            $order['org_postalcode']   = $p->postcode;
            $order['org_region']       = $p->state;
        }
        
        if(!empty($p->san_details)) {
            
            $dns_names = array();
            $approver_emails = array();
            
            foreach($p->san_details as $san)
            {
                if($san->validation_method == 'email')
                {
                    $dns_names[] = $san->san_name;

                    if($san->validation->email == 'email')
                    {
                        $approver_emails[] = $order['approver_email'];
                    }
                    else
                    {
                        $approver_emails[] = $san->validation->email;
                    }
                }
                else
                {
                    $dns_names[] = $san->san_name;
                    $approver_emails[] = $san->validation_method;
                }
            }
            
            $order['dns_names']       = implode(',', $dns_names);
            $order['approver_emails'] = implode(',', $approver_emails);
        }
             
        return $order;
    }
}
