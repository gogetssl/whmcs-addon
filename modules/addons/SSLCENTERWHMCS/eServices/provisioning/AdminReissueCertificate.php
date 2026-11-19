<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

class AdminReissueCertificate extends Ajax {

    private $p;
    private $serviceParams;
    private $product;

    function __construct(&$params) {
        $this->p = &$params;

    }

    public function run() {
        try {
            return $this->miniControler();
        } catch (Exception $ex) {
            $this->response(false, $ex->getMessage());
        }
    }

    private function miniControler() {


        if ($this->p['action'] === 'reissueCertificate') {
            return $this->reissueCertificate();
        }

        if ($this->p['action'] === 'webServers') {
            return $this->webServers();
        }

        if ($this->p['action'] === 'getApprovals') {
            return $this->getApprovals();
        }

    }

    private function reissueCertificate() {
        $this->validateSanDomains();
        $this->validateServerType();

        $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($this->p['serviceId']);

        if (is_null($sslService)) {
            throw new Exception('Create has not been initialized.');
        }

        if ($this->p['userID'] != $sslService->userid) {
            throw new Exception('An error occurred.');
        }

        // Certificate before the reissue: the validation must wait for the new one
        $previousConfigdata = (array) $sslService->configdata;
        $previousCrt        = isset($previousConfigdata['crt']) ? $previousConfigdata['crt'] : '';

        $data = [
            'webserver_type'  => $this->p['webServer'],
            'csr'             => $this->p['csr'],
            'approver_email' => $this->p['approveremail'],
        ];

        $sansDomains = [];

        // SANs whenever the product or the certificate has them (a product with SANs switched off must not drop them)
        if (($this->getSansLimit() || $this->getSansLimitWildcard()) AND count($_POST['approveremails'])) {
            $this->validateSanDomains();
            $sansDomains             = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($this->p['sanDomains']);
            $sansDomainsWildCard     = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($this->p['sanDomainsWildcard']);

            $sansDomains = array_merge($sansDomains, $sansDomainsWildCard);

            $approverEmails = $this->p['approveremails'];

            $data['approver_email'] = $approverEmails[0];
            unset($approverEmails[0]);
            $approverEmailsText = implode(',', $approverEmails);

            $data['dns_names']       = implode(',', $sansDomains);
            $data['approver_emails'] = $approverEmailsText;
        }

        $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($this->p['serviceId']);
        $product = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($service->productID);

        if($product->configuration()->text_name == '144')
        {
            $sansDomains             = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($this->p['sanDomains']);

            $data['dns_names'] = implode(',', $sansDomains);
            $data['approver_emails'] = $sslService->configdata->dcv_method;

            for($i=0;$i<count($sansDomains)-1;$i++)
            {
                $data['approver_emails'] .= ','.$sslService->configdata->dcv_method;
            }
            $data['dcv_method'] = $sslService->configdata->dcv_method;
        }

        $orderStatus = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);

        $singleDomainsCount = $orderStatus['single_san_count'];
        $wildcardDomainsCount = $orderStatus['wildcard_san_count'];

        $csrCommonName = '';
        try {
            $decodedForSans = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->p['csr']);
            $csrCommonName  = isset($decodedForSans['csrResult']['CN']) ? $decodedForSans['csrResult']['CN'] : '';
        } catch (\Exception $e) {
            $csrCommonName = isset($sslService->configdata->domain) ? $sslService->configdata->domain : '';
        }
        $toBuy = \MGModule\SSLCENTERWHMCS\eHelpers\SanList::getSlotsToBuy(
            $csrCommonName,
            \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains((string) $this->p['sanDomains']),
            \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains((string) $this->p['sanDomainsWildcard']),
            $singleDomainsCount,
            $wildcardDomainsCount
        );
        \MGModule\SSLCENTERWHMCS\eHelpers\SanList::buySlots(
            \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(),
            $sslService->remoteid,
            $toBuy['single'],
            $toBuy['wildcard']
        );

        $reissueData = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->reIssueOrder($sslService->remoteid, $data);
        sleep(2);
        $orderDetails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
        $decodedCSR   = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->p['csr']);

        // dns manager
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

        $sslService->setConfigdataKey('servertype', $data['webserver_type']);
        $sslService->setConfigdataKey('csr', $data['csr']);
        // Reissued with another CSR: the stored key no longer belongs to the certificate
        \MGModule\SSLCENTERWHMCS\eHelpers\PrivateKey::dropIfNotMatchingCsr($sslService, $data['csr']);
        $sslService->setConfigdataKey('approveremail', $data['approver_email']);
        $sslService->setApproverEmails($data['approver_emails']);
        $sslService->setSansDomains($data['dns_names']);
        \MGModule\SSLCENTERWHMCS\eHelpers\SanList::storePurchased($sslService, $orderDetails, false);
        $sslService->save();

        try
        {
            $decodedCSR   = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->p['csr']);
            \MGModule\SSLCENTERWHMCS\eHelpers\Invoice::insertDomainInfoIntoInvoiceItemDescription($this->p['serviceId'], $decodedCSR['csrResult']['CN'], true);

            $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($this->p['serviceId']);
            $service->save(array('domain' => $decodedCSR['csrResult']['CN']));

            $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService);
            $configDataUpdate->run();
        }
        catch(Exception $e)
        {

        }

        // Automatic cPanel installation: validate (upload the validation file) and install the reissued certificate
        $reissuedDomain = !empty($decodedCSR['csrResult']['CN']) ? $decodedCSR['csrResult']['CN'] : $sslService->getDomain();
        \MGModule\SSLCENTERWHMCS\eHelpers\ReissueValidation::queue($sslService, $sslService->userid, $this->p['serviceId'], $reissuedDomain, $previousCrt);

        $this->response(true, 'Certificate was successfully reissued.');

    }

    private function webServers() {
        $this->moduleBuildParams();
        $apiProductId  = $this->serviceParams[ConfigOptions::API_PRODUCT_ID];
        $apiRepo       = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $apiProduct    = $apiRepo->getProduct($apiProductId);
        $apiWebServers = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\WebServers::getAll($apiProduct->getWebServerTypeId());
        $this->response(true, 'Web Servers', $apiWebServers);

    }

    private function moduleBuildParams() {
        $this->serviceParams = \ModuleBuildParams($this->p['serviceId']);
        if (empty($this->serviceParams)) {
            throw new Exception('Can not build module params.');
        }
    }

    private function getApprovals() {
        $this->validateSanDomains();
        $this->validateSansDomainsWildcard();
        $this->validateServerType();
        $decodeCSR    = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->p['csr']);

        $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($this->p['serviceId']);
        $product = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($service->productID);

        if($product->configuration()->text_name != '144')
        {
            if(isset($decodeCSR['csrResult']['errorMessage'])){
                throw new Exception($decodeCSR['csrResult']['errorMessage']);
            }
        }
        $mainDomain   = $decodeCSR['csrResult']['CN'];
        $domains      = $mainDomain . PHP_EOL . $this->p['sanDomains'].PHP_EOL.$this->p['sanDomainsWildcard'];
        $parseDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($domains);
        $SSLStepTwoJS = new SSLStepTwoJS($this->p);
        $this->response(true, 'Approve Emails', $SSLStepTwoJS->fetchApprovalEmailsForSansDomains($parseDomains));

    }

    private function validateSanDomains() {
        $this->moduleBuildParams();
        $sansDomains = $this->p['sanDomains'];
        $sansDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sansDomains);

        $apiProductId     = $this->serviceParams[ConfigOptions::API_PRODUCT_ID];

        $invalidDomains = \MGModule\SSLCENTERWHMCS\eHelpers\Domains::getInvalidDomains($sansDomains, in_array($apiProductId, array(100, 99, 63)));

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

        $includedSans = $this->serviceParams[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = $this->serviceParams['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        $sansLimit    = $this->getSansLimit();
        if (count($sansDomains) > $sansLimit) {
            throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('exceededLimitOfSans'));
        }
    }

    private function validateSansDomainsWildcard() {
        $sansDomainsWildcard = $this->p['sanDomainsWildcard'];
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

        if (count($sansDomainsWildcard) > $this->getSansLimitWildcard()) {
            throw new Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::T('sanLimitExceededWildcard'));
        }
    }

    private function validateServerType() {
        if($this->p['webServer'] == 0) {
            throw new Exception('You must select client server type');
        }
    }

    private $reissueLimits = null;

    /**
     * SAN limits of the reissue: the product limits, but never fewer than the names the certificate already has.
     */
    private function getReissueLimits() {
        if ($this->reissueLimits === null) {
            if (empty($this->serviceParams)) {
                $this->moduleBuildParams();
            }
            $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
            $sslService = $sslRepo->getByServiceId($this->p['serviceId']);
            $this->reissueLimits = \MGModule\SSLCENTERWHMCS\eHelpers\SanList::getReissueLimits(
                $this->serviceParams, $sslService, \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()
            );
        }
        return $this->reissueLimits;
    }

    private function getSansLimit() {
        $limits = $this->getReissueLimits();
        return $limits['single'];
    }

    private function getSansLimitWildcard() {
        $limits = $this->getReissueLimits();
        return $limits['wildcard'];
    }
}
