<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

class ClientReissueCertificate {

    // allow *.domain.com as SAN for products
    const PRODUCTS_WITH_ADDITIONAL_SAN_VALIDATION = array(100, 99, 63);
    /**
     *
     * @var array
     */
    private $p;

    /**
     *
     * @var array
     */
    private $get;

    /**
     *
     * @var array
     */
    private $post;

    /**
     *
     * @var array
     */
    private $vars;

    /**
     *
     * @var \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL
     */
    private $sslService;

    /**
     *
     * @var array
     */
    private $orderStatus;

    const STEP_ONE = 'pages/reissue/stepOne';
    const STEP_TWO = 'pages/reissue/stepTwo';
    const SUCCESS  = 'pages/reissue/stepSuccess';

    function __construct(&$params, &$post, &$get) {
        $this->p              = &$params;
        $this->get            = &$get;
        $this->post           = &$post;
        $this->vars           = [];
        $this->vars['errors'] = [];
    }
    public function run() {
        \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::decodeSanAprroverEmailsAndMethods($_POST);
        $this->setMainDomainDcvMethod($_POST);
        $this->setSansDomainsDcvMethod($_POST);
        return $this->miniControler();

    }

    private function miniControler() {

        try {

            $this->validateService();
        } catch (Exception $ex) {
            return '- ' . \MGModule\SSLCENTERWHMCS\eHelpers\Exception::e($ex);
        }
        if (isset($this->post['stepOneForm'])) {
            try {
                $this->stepOneForm();
                return $this->build(self::STEP_TWO);
            } catch (Exception $ex) {
                $this->vars['errors'][] = \MGModule\SSLCENTERWHMCS\eHelpers\Exception::e($ex);
            }
        }


        if (isset($this->post['stepTwoForm'])) {
            try {
                $this->stepTwoForm();
                global $CONFIG;
                $this->vars['actuallink'] = $CONFIG['SystemURL'].'/clientarea.php?action=productdetails&id='. $_GET['id'];
                return $this->build(self::SUCCESS);
            } catch (Exception $ex) {
                $this->vars['errors'][] = \MGModule\SSLCENTERWHMCS\eHelpers\Exception::e($ex);
            }
        }


        //dsiplay csr generator
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $displayCsrGenerator = $apiConf->display_csr_generator;
        $countriesForGenerateCsrForm = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountriesForMgAddonDropdown();

        //get selected default country for CSR Generator
        $defaultCsrGeneratorCountry = ($displayCsrGenerator) ? $apiConf->default_csr_generator_country : '';
        if(key_exists($defaultCsrGeneratorCountry, $countriesForGenerateCsrForm) AND $defaultCsrGeneratorCountry != NULL)
        {
            //get country name
            $elementValue = $countriesForGenerateCsrForm[$defaultCsrGeneratorCountry]/* . ' (default)'*/;
            //remove country from list
            unset($countriesForGenerateCsrForm[$defaultCsrGeneratorCountry]);
            //insert default country on the begin of countries list
            $countriesForGenerateCsrForm = array_merge(array($defaultCsrGeneratorCountry => $elementValue), $countriesForGenerateCsrForm);
        }

        $this->vars['generateCsrIntegrationCode'] =   ($displayCsrGenerator) ? \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getGenerateCsrModalScript($this->p['serviceid'], json_encode(array()), $countriesForGenerateCsrForm) : '';
        $this->vars['serviceID'] = $this->p['serviceid'];

        $this->loadServerList();
        $this->vars['sansLimit'] = $this->getSansLimit();
        $this->vars['sansLimitWildCard'] = $this->getSansLimitWildcard();

        $ssl = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $ssldata = $ssl->getByServiceId($this->p['serviceid']);
        $this->vars['csrreissue'] = $ssldata->configdata->csr;
        $sandetails = (array)$ssl->getByServiceId($this->p['serviceid'])->getSanDomains();
        $this->vars['sandetails'] = $sandetails;
        $this->vars['sans_domains'] = $sandetails['sans_domains'];

        $sanSingle = [];
        $sanWildcard = [];

        $this->vars['privKey'] = '';
        if(isset($ssldata->configdata->private_key) && !empty($ssldata->configdata->private_key))
        {
            $this->vars['privKey'] = $ssldata->configdata->private_key;
        }

        $allSans = $ssldata->configdata->san_details;

        foreach ($allSans as $san)
        {
            if (strpos($san->san_name, '*.') !== false) {
                $sanWildcard[] = $san->san_name;
            } else {
                $sanSingle[] = $san->san_name;
            }
        }

        $sanSingle = implode(PHP_EOL, $sanSingle);
        $sanWildcard = implode(PHP_EOL, $sanWildcard);

        if(!isset($this->vars['sandetails']['wildcard_san']) || empty($this->vars['sandetails']['wildcard_san'])) {
            $this->vars['sandetails']['sans_domains'] = $sanSingle;
            $this->vars['sandetails']['wildcard_san'] = $sanWildcard;
        }

        return $this->build(self::STEP_ONE);

    }


    private function setMainDomainDcvMethod($post) {
        $this->post['dcv_method']  = $post['dcvmethodMainDomain'];
    }

    private function setSansDomainsDcvMethod($post) {
        if(isset($post['dcvmethod']) && is_array($post['dcvmethod'])) {
            $this->post['sansDomansDcvMethod'] = $post['dcvmethod'];
        }
    }

    private function stepOneForm() {
        $this->validateWebServer();
        $this->validateSanDomains();
        $this->validateSansDomainsWildcard();
        $decodeCSR = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->post['csr']);

        $_SESSION['decodeCSR'] = $decodeCSR;

        $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($this->p['serviceid']);
        $product = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($service->productID);

        if($product->configuration()->text_name != '144')
        {
            if(!isset($decodeCSR['csrResult']['CN']) || strpos($decodeCSR['csrResult']['CN'], '*.') === false)
            {
                if(isset($decodeCSR['csrResult']['errorMessage'])){
                    throw new Exception($decodeCSR['csrResult']['errorMessage']);
                }
            }
        }

        $mainDomain                   = $decodeCSR['csrResult']['CN'];
        $domains                      = $mainDomain . PHP_EOL . $this->post['sans_domains'];
        $parseDomains                 = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains(strtolower($domains));
        $domainsWildcard              = $this->post['sans_domains_wildcard'];
        $parseDomainsWildcard         = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains(strtolower($domainsWildcard));
        $parseDomains = array_merge($parseDomains, $parseDomainsWildcard);
        $SSLStepTwoJS                 = new SSLStepTwoJS($this->p);
        $this->vars['approvalEmails'] = json_encode($SSLStepTwoJS->fetchApprovalEmailsForSansDomains($parseDomains));
        $this->vars['brand'] = json_encode($this->getCertificateBrand());
        if(isset($this->post['privateKey']))
        {
            $this->vars['privateKey'] = $this->post['privateKey'];
        }

        $disabledValidationMethods = array();
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();

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
            array_push($disabledValidationMethods, 'email');
        }
        if(!$productssl['product']['dcv_dns'])
        {
            array_push($disabledValidationMethods, 'dns');
        }
        if(!$productssl['product']['dcv_http'])
        {
            array_push($disabledValidationMethods, 'http');
        }
        if(!$productssl['product']['dcv_https'])
        {
            array_push($disabledValidationMethods, 'https');
        }

        $brand = $productssl['product']['brand'];
        if($brand == 'digicert' || $brand == 'geotrust' || $brand == 'thawte' || $brand == 'rapidssl')
        {
            foreach($SSLStepTwoJS->fetchApprovalEmailsForSansDomains($parseDomains) as $sandomain => $appreveEmails)
            {
                if (strpos($sandomain, '*.') !== false) {
                    array_push($disabledValidationMethods, 'http');
                    array_push($disabledValidationMethods, 'https');
                }
            }
        }

        $this->vars['disabledValidationMethods'] = json_encode($disabledValidationMethods);
    }

    private function stepTwoForm() {

        $data['dcv_method'] = strtolower($this->post['dcv_method']);
        $data['webserver_type'] = $this->post['webservertype'];
        $data['approver_email'] = ($data['dcv_method'] == 'email') ? $this->post['approveremail'] : '';
        $data['csr'] = $this->post['csr'];

        $brandsWithOnlyEmailValidation = ['geotrust','thawte','rapidssl','symantec'];

        $sansDomains = [];

        $this->validateWebServer();

        if(isset($_SESSION['decodeCSR']) && !empty($_SESSION['decodeCSR']))
        {
            $decodedCSR = $_SESSION['decodeCSR'];
        }
        else
        {
            $decodedCSR   = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->post['csr']);
        }

        if ($this->getSansLimit()) {
            $this->validateSanDomains();
            $sansDomains             = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($this->post['sans_domains']);
            $sansDomainsWildcard = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($this->post['sans_domains_wildcard']);
            $sansDomains = array_merge($sansDomains, $sansDomainsWildcard);

            //if entered san is the same as main domain
            if(count($sansDomains) != count($_POST['approveremails'])) {
                foreach($sansDomains as $key => $domain) {
                    if($decodedCSR['csrResult']['CN'] == $domain) {
                        unset($sansDomains[$key]);
                    }
                }
            }
            $data['dns_names']       = implode(',', $sansDomains);



            if(!empty($sanDcvMethods = $this->getSansDomainsValidationMethods())) {
                $i = 0;
                foreach($_POST['approveremails'] as $domain => $approveremail) {
                    if($sanDcvMethods[$i] != 'EMAIL') {
                        $_POST['approveremails']["$domain"] = strtolower($sanDcvMethods[$i]);
                    }
                    $i++;
                }
                $data['approver_emails'] = implode(',', $_POST['approveremails']);
            }
        }

        $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($this->p['serviceid']);
        $product = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($service->productID);

        if($product->configuration()->text_name == '144')
        {
            $sansDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($this->post['sans_domains']);

            $data['dns_names'] = implode(',', $sansDomains);
            $data['approver_emails'] = strtolower($_POST['dcvmethodMainDomain']);

            foreach ($_POST['dcvmethod'] as $method)
            {
                $data['approver_emails'] .= ','.strtolower($method);
            }
        }

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

        $brand = $productssl['product']['brand'];

        if($brand == 'digicert' || $brand == 'geotrust' || $brand == 'thawte' || $brand == 'rapidssl')
        {

            $sansDomainsMethod = [];
            foreach($sansDomains as $sd)
            {
                $sansDomainsMethod[] =  strtolower($this->post['dcv_method']);
            }
            $data['approver_emails'] = implode(',', $sansDomainsMethod);
        }
        //if brand is 'geotrust','thawte','rapidssl','symantec' do not send dcv method for sans
//        if(in_array($brand, $brandsWithOnlyEmailValidation)) {
//            unset($data['approver_emails']);
//        }
//

        $ssl        = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $ssl->getByServiceId($this->p['serviceid']);

        $orderStatus = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($this->sslService->remoteid);

        $singleDomainsCount = $orderStatus['single_san_count'];
        $wildcardDomainsCount = $orderStatus['wildcard_san_count'];

        if(empty($this->post['sans_domains']))
        {
            $newSanDomainSingleCount = 0;
        }
        else
        {
            $newSanDomainSingleCount = count(explode(PHP_EOL,$this->post['sans_domains']));
        }

        if(empty($this->post['sans_domains_wildcard']))
        {
            $newSanDomainWildcardCount = 0;
        }
        else
        {
            $newSanDomainWildcardCount = count(explode(PHP_EOL,$this->post['sans_domains_wildcard']));
        }

        if(!empty($this->post['sans_domains']) || !empty($this->post['sans_domains_wildcard'])) {
            if ($newSanDomainSingleCount > $singleDomainsCount || $newSanDomainWildcardCount > $wildcardDomainsCount) {
                $singleToAdd = $newSanDomainSingleCount - $singleDomainsCount;
                if ($singleToAdd < 0) {
                    $singleToAdd = 0;
                }
                $wildcardToAdd = $newSanDomainWildcardCount - $wildcardDomainsCount;
                if ($wildcardToAdd < 0) {
                    $wildcardToAdd = 0;
                }
                $allToAdd = $singleToAdd + $wildcardToAdd;

                if ($singleToAdd <= 0) {
                    $allToAdd = 0;
                }

                \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSslSan(
                    $this->sslService->remoteid,
                    $allToAdd,
                    $singleToAdd,
                    $wildcardToAdd
                );
            }
        }

        $reissueData = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->reIssueOrder($this->sslService->remoteid, $data);
        $orderDetails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($this->sslService->remoteid);

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
            if(isset($orderDetails['approver_method']['dns']['record']) && !empty($orderDetails['approver_method']['dns']['record']))
            {

                if (strpos($orderDetails['approver_method']['dns']['record'], 'CNAME') !== false)
                {
                    $dnsrecord = explode("CNAME", $orderDetails['approver_method']['dns']['record']);
                    $records[] = array(
                        'name' => trim(rtrim($dnsrecord[0])).'.',
                        'type' => 'CNAME',
                        'ttl' => '3600',
                        'data' => trim(rtrim($dnsrecord[1]))
                    );
                }
                else
                {
                    $dnsrecord = explode("IN   TXT", $orderDetails['approver_method']['dns']['record']);
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
            if(isset($orderDetails['san']) && !empty($orderDetails['san']))
            {
                foreach($orderDetails['san'] as $sanrecord)
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

        //save private key
        if(isset($_POST['privateKey']) && $_POST['privateKey'] != null) {
            $privKey = decrypt($_POST['privateKey']);
            $GenerateSCR = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\GenerateCSR($this->p, $_POST);
            $GenerateSCR->savePrivateKeyToDatabase($this->p['serviceid'], $privKey);
        }

        //update domain column in tblhostings
        $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($this->p['serviceid']);
        $service->save(array('domain' => $decodedCSR['csrResult']['CN']));

        $this->sslService->setDomain($decodedCSR['csrResult']['CN']);
        $this->sslService->setSSLStatus('processing');
        $this->sslService->setConfigdataKey('servertype', $data['webserver_type']);
        $this->sslService->setConfigdataKey('csr', $data['csr']);
        $this->sslService->setConfigdataKey('approveremail', $data['approver_email']);
        $this->sslService->setConfigdataKey('private_key', $_POST['privateKey']);
        $this->sslService->setApproverEmails($data['approver_emails']);
        //$this->sslService->setSansDomains($data['dns_names']);
        $this->sslService->setSanDetails($orderDetails['san']);
        $this->sslService->save();

        //$configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($this->sslService);
        //$configDataUpdate->run();

        try
        {
            \MGModule\SSLCENTERWHMCS\eHelpers\Invoice::insertDomainInfoIntoInvoiceItemDescription($this->p['serviceid'], $decodedCSR['csrResult']['CN'], true);
        }
        catch(Exception $e)
        {

        }
    }

    private function getSansDomainsValidationMethods() {
        $data = [];
        foreach ($this->post['sansDomansDcvMethod'] as $newMethod) {
            $data[] = $newMethod;
        }
        return $data;
    }

    private function validateWebServer() {
        if($this->post['webservertype'] == 0) {
            throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('mustSelectServer'));
        }
    }


    private function getCertificateBrand()
    {
        if(!empty($this->p[ConfigOptions::API_PRODUCT_ID])) {
            $apiRepo       = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
            $apiProduct    = $apiRepo->getProduct($this->p[ConfigOptions::API_PRODUCT_ID]);
            return $apiProduct->brand;
        }
    }

    private function validateSanDomains() {
        $sansDomains = $this->post['sans_domains'];
        $sansDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomains);

        $apiProductId     = $this->p[ConfigOptions::API_PRODUCT_ID];

        $invalidDomains = \MGModule\SSLCENTERWHMCS\eHelpers\Domains::getInvalidDomains($sansDomains, in_array($apiProductId, self::PRODUCTS_WITH_ADDITIONAL_SAN_VALIDATION));

        if($apiProductId != '144') {

            if (count($invalidDomains)) {
                throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('incorrectSans') . implode(', ', $invalidDomains));
            }

        } else {

            if (count($invalidDomains)) {

                $iperror = false;

                foreach($invalidDomains as $domainname)
                {
                    if(!filter_var($domainname, FILTER_VALIDATE_IP)) {
                        $iperror = true;
                    }
                }

                if ($iperror) {
                    throw new Exception('SANs are incorrect');
                }
            }

        }

        $includedSans = $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        $sansLimit    = $this->getSansLimit();
        if (count($sansDomains) > $sansLimit) {
            throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('exceededLimitOfSans'));
        }

    }

    private function validateSansDomainsWildcard() {
        $sansDomainsWildcard = $this->post['sans_domains_wildcard'];
        $sansDomainsWildcard = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomainsWildcard);

        foreach($sansDomainsWildcard as $domain)
        {
            $check = substr($domain, 0,2);
            if($check != '*.')
            {
                throw new Exception('SAN\'s Wildcard are incorrect');
            }
            $domaincheck = \MGModule\SSLCENTERWHMCS\eHelpers\Domains::validateDomain(substr($domain, 2));
            if($domaincheck !== true)
            {
                throw new Exception('SAN\'s Wildcard are incorrect');
            }
        }

        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS_WILDCARD];
        $boughtSans   = (int) $this->p['configoptions']['sans_wildcard_count'];

        $sansLimit = $includedSans + $boughtSans;
        if (count($sansDomainsWildcard) > $sansLimit) {
            throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('sanLimitExceededWildcard'));
        }
    }

    private function validateService() {
        $ssl              = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $this->sslService = $ssl->getByServiceId($this->p['serviceid']);
        if (is_null($this->sslService)) {
            throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('createNotInitialized'));
        }

        //$this->orderStatus = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($this->sslService->remoteid);

        if ($this->sslService->configdata->ssl_status !== 'active') {
            throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('notAllowToReissue'));
        }

    }

    private function loadServerList() {

        try {
            $apiRepo                  = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
            $apiProduct               = $apiRepo->getProduct($this->p[\MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions::API_PRODUCT_ID]);

            if($apiProduct->brand == 'comodo')
            {
                $apiWebServers = array(
                    array('id' => '35', 'software' => 'IIS'),
                    array('id' => '-1', 'software' => 'Any Other')
                );
            }
            else
            {
                $apiWebServers = array(
                    array('id' => '18', 'software' => 'IIS'),
                    array('id' => '18', 'software' => 'Any Other')
                );
            }

            $this->vars['webServers'] = $apiWebServers;
            \MGModule\SSLCENTERWHMCS\eServices\FlashService::set('SSLCENTERWHMCS_SERVER_LIST_' . \MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions::API_PRODUCT_ID, $apiWebServers);
        } catch (Exception $ex) {
            $this->vars['errors'][] .= \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('canNotFetchWebServer');
        }

    }

    private function build($template) {
        $this->vars['error'] = implode('<br>', $this->vars['errors']);
        $content             = \MGModule\SSLCENTERWHMCS\eServices\TemplateService::buildTemplate($template, $this->vars);
        return [
            'templatefile' => 'main',
            'vars'         => ['content' => $content],
        ];

    }

    private function getSansLimit() {
        $sanEnabledForWHMCSProduct = $this->p[ConfigOptions::PRODUCT_ENABLE_SAN] === 'on';
        if (!$sanEnabledForWHMCSProduct) {
            return 0;
        }
        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = (int) $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        return $includedSans + $boughtSans;

    }

    private function getSansLimitWildcard() {
        $sanEnabledForWHMCSProduct = $this->p[ConfigOptions::PRODUCT_ENABLE_SAN_WILDCARD] === 'on';
        if (!$sanEnabledForWHMCSProduct) {
            return 0;
        }
        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS_WILDCARD];
        $boughtSans   = (int) $this->p['configoptions']['sans_wildcard_count'];
        return $includedSans + $boughtSans;

    }

}
