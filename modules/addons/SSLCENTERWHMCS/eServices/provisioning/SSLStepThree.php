<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;
use MGModule\SSLCENTERWHMCS\models\actions\Repository as ActionsRepository;
use \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service as Service;
use \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product as Product;
use WHMCS\Database\Capsule;
use MGModule\SSLCENTERWHMCS\models\orders\Repository as OrderRepo;
use MGModule\SSLCENTERWHMCS\models\logs\Repository as LogsRepo;

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

            $actionsRepo = new ActionsRepository();

            if($actionsRepo->checkStepThree($this->p['serviceid']) !== true) {

                $actionsRepo->addAction($this->p['userid'], $this->p['serviceid'], 'step_three', 'processing');
                \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::decodeSanAprroverEmailsAndMethods($_POST);
                $this->setMainDomainDcvMethod($_POST);
                $this->setSansDomainsDcvMethod($_POST);
                $this->SSLStepThree();

            }

            $actionsRepo->updateStatusStepThree($this->p['serviceid'], 'success');

        } catch (Exception $ex) {

            $actionsRepo->updateStatusStepThree($this->p['serviceid'], 'error');

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
        
        $apiRepo       = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $apiProduct    = $apiRepo->getProduct($this->p[ConfigOptions::API_PRODUCT_ID]);
        $brand = $apiProduct->brand;

        $order               = [];
        $order['dcv_method'] = strtolower($this->p['fields']['dcv_method']);
       
        $order['product_id'] = $this->p[ConfigOptions::API_PRODUCT_ID]; // Required
        $order['period']     = $billingPeriods[$this->p['model']->billingcycle];//$this->p[ConfigOptions::API_PRODUCT_MONTHS]; // Required  
        $order['csr']        = $this->p['csr']; // Required
        $order['server_count']       = -1; // Required . amount of servers, for Unlimited pass “-1”
        $order['approver_email']     = ($order['dcv_method'] == 'email') ? $this->p['approveremail'] : ''; // Required . amount of servers, for Unlimited pass “-1”
        
        $order['webserver_type']     = $this->p['servertype']; // Required . webserver type, can be taken from getWebservers method
        if($brand == 'geotrust' || $brand == 'rapidssl' || $brand == 'digicert' || $brand == 'thawte')
        {
            $order['webserver_type']     = '18'; // Required . webserver type, can be taken from getWebservers method
        }
        else
        {
            $order['webserver_type']     = '-1'; // Required . webserver type, can be taken from getWebservers method
        }
              
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
        
        $template = Capsule::table('tblconfiguration')->where('setting', 'Template')->first();
        if(isset($template->value) && $template->value == 'twenty-one')
        {
            $order['tech_country'] = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountryCodeByName($order['tech_country']);
            $order['admin_country'] = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountryCodeByName($order['admin_country']);
        }
        
        if ($this->apiProduct->isOrganizationRequired()) {
            $org                       = &$this->p['fields'];
            $order['org_name']         = $org['org_name'];
            $order['org_division']     = $org['org_division'];
            $order['org_lei']          = $org['org_lei'];
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

        $san_domains = explode(PHP_EOL, $this->p['configdata']['fields']['sans_domains']);
        $wildcard_domains = explode(PHP_EOL, $this->p['configdata']['fields']['wildcard_san']);
        $all_san = array_merge($san_domains, $wildcard_domains);

        $decodedCSR = [];
        $decodedCSR['csrResult']['CN'] = $this->p['domain'];
        
        //$decodedCSR   = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->p['csr']);
        if ($sanEnabledForWHMCSProduct AND count($all_san)) {
                 
            $sansDomains = $this->p['configdata']['fields']['sans_domains'];
            $sansDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomains);
            
            $sansDomainsWildcard = $this->p['configdata']['fields']['wildcard_san'];
            $sansDomainsWildcard = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomainsWildcard);
            
            $sansDomains = array_merge($sansDomains, $sansDomainsWildcard);



            //if entered san is the same as main domain
            if(is_array($_POST['approveremails'])){

                if(count($sansDomains) != count($_POST['approveremails'])) {
                    foreach($sansDomains as $key => $domain) {
                        if($decodedCSR['csrResult']['CN'] == $domain) {
                            unset($sansDomains[$key]);
                        }
                    }
                }

            }

            $order['dns_names']       = implode(',', $sansDomains);
            $approver_emails_method = [];
            foreach ($sansDomains as $d)
            {
                if($_POST['approval_method'] == 'email')
                {
                    $approver_emails_method[] = $_POST['approveremail'];
                }
                else
                {
                    $approver_emails_method[] = $order['dcv_method'];
                }
            }
            $order['approver_emails'] = implode(',', $approver_emails_method);

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
        
        if($order['product_id'] == '144')
        {
            $sansDomains = $this->p['configdata']['fields']['sans_domains'];
            $sansDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomains);

            $sansDomainsWildcard = $this->p['configdata']['fields']['wildcard_san'];
            $sansDomainsWildcard = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomainsWildcard);

            $sansDomains = array_merge($sansDomains, $sansDomainsWildcard);
            
            $order['dns_names'] = implode(',', $sansDomains);
            $order['approver_emails'] = strtolower($_POST['dcvmethodMainDomain']);

            $approver_emails_method = [];
            foreach ($sansDomains as $d)
            {
                $approver_emails_method[] = $order['dcv_method'];
            }
            $order['approver_emails'] = implode(',', $approver_emails_method);
        }

        //if brand is 'geotrust','thawte','rapidssl','symantec' do not send dcv method for sans
//        if(in_array($brand, $brandsWithOnlyEmailValidation)) {
//            unset($order['approver_emails']);
//        }

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

        // dns manager
        sleep(2);
        $dnsmanagerfile = dirname(dirname(dirname(dirname(dirname(__DIR__))))).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'dnsmanager.php';
        $checkTable = Capsule::schema()->hasTable('DNSManager3_Zone');
        if(file_exists($dnsmanagerfile) && $checkTable !== false)
        {
            $zoneDomain = $decodedCSR['csrResult']['CN'];
            $loaderDNS = dirname(dirname(dirname(dirname(dirname(__DIR__))))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.'DNSManager3'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'AppContext.php';
            if(file_exists($loaderDNS)) {
                require_once $loaderDNS;
                $appContext = new \ModulesGarden\DNSManager3\Core\App\AppContext();
                $helper = new \ModulesGarden\DNSManager3\App\Legacy\DNSManager2\mgLibs\custom\helpers\DomainHelper($decodedCSR['csrResult']['CN']);
                $zoneDomain = $helper->getDomainWithTLD();
            }

            $records = [];
            if(isset($addedSSLOrder['approver_method']['dns']['record']) && !empty($addedSSLOrder['approver_method']['dns']['record']))
            {
                if (strpos($addedSSLOrder['approver_method']['dns']['record'], 'CNAME') !== false) 
                {
                    $dnsrecord = explode("CNAME", $addedSSLOrder['approver_method']['dns']['record']);
                    $records[] = array(
                        'name' => trim(rtrim($dnsrecord[0])).'.',
                        'type' => 'CNAME',
                        'ttl' => '3600',
                        'data' => trim(rtrim($dnsrecord[1]))
                    );
                }
                else
                {
                    $dnsrecord = explode("IN   TXT", $addedSSLOrder['approver_method']['dns']['record']);
                    $length = strlen(trim(rtrim($dnsrecord[1])));
                    $records[] = array(
                        'name' => trim(rtrim($dnsrecord[0])).'.',
                        'type' => 'TXT',
                        'ttl' => '14440',
                        'data' => substr(trim(rtrim($dnsrecord[1])),1, $length-2)
                    );
                }
                $zone = Capsule::table('DNSManager3_Zone')->where('name', $zoneDomain)->first();
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

                $zone = Capsule::table('DNSManager3_Zone')->where('name', $zoneDomain)->first();
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
            if(isset($addedSSLOrder['san']) && !empty($addedSSLOrder['san']))
            {
                foreach($addedSSLOrder['san'] as $sanrecord)
                {
                    $records = [];
                    if(isset($sanrecord['validation']['dns']['record']) && !empty($sanrecord['validation']['dns']['record']))
                    {
                        if(file_exists($loaderDNS)) {
                            $helper = new \ModulesGarden\DNSManager3\App\Legacy\DNSManager2\mgLibs\custom\helpers\DomainHelper(str_replace('*.', '',$sanrecord['san_name']));
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
                        $zone = Capsule::table('DNSManager3_Zone')->where('name', $zoneDomain)->first();
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

                        $zone = Capsule::table('DNSManager3_Zone')->where('name', $zoneDomain)->first();
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
        
        $orderDetails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($addedSSLOrder['order_id']);
        if($this->p[ConfigOptions::MONTH_ONE_TIME] && !empty($this->p[ConfigOptions::MONTH_ONE_TIME]))
        {
            $service = new Service($this->p['serviceid']);
            $service->save(array('termination_date' => $orderDetails['valid_till']));
        }

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

        $sslOrder = Capsule::table('tblsslorders')->where('serviceid', $this->p['serviceid'])->first();
        $orderRepo = new OrderRepo();
        $orderRepo->addOrder(
            $this->p['userid'],
            $this->p['serviceid'],
            $sslOrder->id,
            $orderDetails['dcv_method'],
            'Pending Verification',
            $addedSSLOrder
        );

        $logs = new LogsRepo();
        $logs-> addLog($this->p['userid'], $this->p['serviceid'], 'success', 'The order has been placed.');

        $order = Capsule::table('SSLCENTER_orders')->where('service_id', $this->p['serviceid'])->first();
        $sslOrder = Capsule::table('tblsslorders')->where('id', $order->ssl_order_id)->first();
        $service = Capsule::table('tblhosting')->where('id', $this->p['serviceid'])->first();
        $orderDetails = json_decode($order->data, true);

        run_hook('SSLCenter_StepThree', [
            'serviceid' => $this->p['serviceid'],
            'userid' => $this->p['userid'],
            'api_ssl_order' => $addedSSLOrder,
            'order_details' => $orderDetails
        ]);

        $revalidate = false;

        foreach ($orderDetails['approver_method'] as $method => $data)
        {
            try {

                $cPanelService = new \MGModule\SSLCENTERWHMCS\eModels\cpanelservices\Service();
                $cpanelDetails = $cPanelService->getServiceByDomain($service->userid, $service->domain);
                $cpanel = new \MGModule\SSLCENTERWHMCS\eHelpers\Cpanel();

                if ($cpanelDetails === false) continue;

                if ($method == 'http' || $method == 'https') {
                    $cpanel->setService($cpanelDetails);
                    $directory = $cpanel->getRootDirectory($cpanelDetails->user, $service->domain);
                    $content = $data['content'];

                    $cpanel->addDirectory($cpanelDetails->user, [
                        [
                            'dir' => $directory,
                            'name' => '.well-known',
                        ],
                        [
                            'dir' => $directory . '/.well-known',
                            'name' => 'pki-validation',
                        ]
                    ]);

                    $cpanel->saveFile($cpanelDetails->user, $data['filename'], $directory . '/.well-known/pki-validation/', $content);
                    $logs-> addLog($this->p['userid'], $this->p['serviceid'], 'success', 'The '.$service->domain.' domain has been verified using the file method.');
                    $revalidate = true;
                }

                if ($method == 'dns') {

                    if (strpos($data['record'], 'CNAME') !== false) {
                        $cpanel->setService($cpanelDetails);
                        $records = explode('CNAME', $data['record']);
                        $record = new \stdClass();
                        $record->domain = $service->domain;
                        $record->name = trim($records[0]).'.';
                        $record->cname = trim($records[1]);
                        $record->type = 'CNAME';
                        $cpanel->addRecord($cpanelDetails->user, $record);
                        $logs->addLog($this->p['userid'], $this->p['serviceid'], 'success', 'The ' . $service->domain . ' domain has been verified using the dns method.');
                        $revalidate = true;
                    }

                    if (strpos($data['record'], 'IN   TXT') !== false) {
                        $cpanel->setService($cpanelDetails);
                        $records = explode('IN   TXT', $data['record']);
                        $record = new \stdClass();
                        $record->domain = $service->domain;
                        $record->name = trim($records[0]);
                        $record->type = 'TXT';
                        $record->ttl = "14400";
                        $record->txtdata = str_replace('"','', trim($records[1]));
                        $cpanel->addRecord($cpanelDetails->user, $record);
                        $logs->addLog($this->p['userid'], $this->p['serviceid'], 'success', 'The ' . $service->domain . ' domain has been verified using the dns method.');
                        $revalidate = true;
                    }

                }

            } catch (\Exception $e) {
                $logs-> addLog($this->p['userid'], $this->p['serviceid'], 'error', '['.$service->domain.'] Error:'.$e->getMessage());
                continue;
            }
        }

        if(isset($orderDetails['san']) && !empty($orderDetails['san']))
        {
            foreach ($orderDetails['san'] as $san)
            {
                try {

                    $cPanelService = new \MGModule\SSLCENTERWHMCS\eModels\cpanelservices\Service();
                    $cpanelDetails = $cPanelService->getServiceByDomain($service->userid, $san['san_name']);

                    if($cpanelDetails === false) continue;

                    $cpanel = new \MGModule\SSLCENTERWHMCS\eHelpers\Cpanel();
                    $cpanel->setService($cpanelDetails);

                    if($san['validation_method'] == 'http' || $san['validation_method'] == 'https')
                    {
                        $directory = $cpanel->getRootDirectory($cpanelDetails->user, $san['san_name']);
                        $content = $san['validation'][$san['validation_method']]['content'];

                        $cpanel->addDirectory($cpanelDetails->user, [
                            [
                                'dir' => $directory,
                                'name' => '.well-known',
                            ],
                            [
                                'dir' => $directory . '/.well-known',
                                'name' => 'pki-validation',
                            ]
                        ]);

                        $cpanel->saveFile($cpanelDetails->user, $san['validation'][$san['validation_method']]['filename'], $directory.'/.well-known/pki-validation/', $content);
                        $logs-> addLog($this->p['userid'], $this->p['serviceid'], 'success', 'The '.$san['san_name'].' domain has been verified using the file method.');
                        $revalidate = true;
                    }

                    if($san['validation_method'] == 'dns')
                    {

                        if (strpos($san['validation'][$san['validation_method']]['record'], 'CNAME') !== false) {
                            $records = explode('CNAME', $san['validation'][$san['validation_method']]['record']);
                            $record = new \stdClass();
                            $record->domain = $san['san_name'];
                            $record->name = trim($records[0]).'.';
                            $record->cname = trim($records[1]);
                            $record->type = 'CNAME';
                            $cpanel->addRecord($cpanelDetails->user, $record);
                            $logs->addLog($this->p['userid'], $this->p['serviceid'], 'success', 'The ' . $san['san_name'] . ' domain has been verified using the dns method.');
                            $revalidate = true;
                        }

                        if (strpos($san['validation'][$san['validation_method']]['record'], 'IN   TXT') !== false) {
                            $records = explode('IN   TXT', $san['validation'][$san['validation_method']]['record']);
                            $record = new \stdClass();
                            $record->domain = $san['san_name'];
                            $record->name = trim($records[0]);
                            $record->type = 'TXT';
                            $record->ttl = "14400";
                            $record->txtdata = str_replace('"','', trim($records[1]));
                            $cpanel->addRecord($cpanelDetails->user, $record);
                            $logs->addLog($this->p['userid'], $this->p['serviceid'], 'success', 'The ' . $san['san_name'] . ' domain has been verified using the dns method.');
                            $revalidate = true;
                        }

                    }

                } catch (\Exception $e) {

                    $logs-> addLog($this->p['userid'], $this->p['serviceid'], 'error', '['.$san['san_name'].'] Error:'.$e->getMessage());
                    continue;
                }
            }
        }

        if($revalidate === true) {
            try {

                $dataAPI = [
                    'domain' => $service->domain
                ];
                $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->revalidate($sslOrder->remoteid, $dataAPI);

                $logs->addLog($this->p['userid'], $this->p['serviceid'], 'info', '[' . $service->domain . '] Revalidate,');

                if (isset($response['success']) && !empty($response['success'])) {
                    $orderRepo->updateStatus($this->p['serviceid'], 'Pending Installation');
                    $logs->addLog($this->p['userid'], $this->p['serviceid'], 'success', '[' . $service->domain . '] Revalidate Succces.');
                }

            } catch (\Exception $e) {
                $logs->addLog($this->p['userid'], $this->p['serviceid'], 'error', '[' . $service->domain . '] Error:' . $e->getMessage());
            }
        }
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
