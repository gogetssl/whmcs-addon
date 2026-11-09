<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits;

use MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule;
use MGModule\SSLCENTERWHMCS\eModels\cpanelservices\Service;
use MGModule\SSLCENTERWHMCS\eHelpers\Cpanel;
use MGModule\SSLCENTERWHMCS\models\orders\Repository as OrderRepo;
use MGModule\SSLCENTERWHMCS\models\logs\Repository as LogsRepo;

trait StandardSslTrait
{
    public function renewJSON($input, $vars = array()) {

        try
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: The renewal action was initiated for the Service ID: " . $input['id']);

            $errorInvoiceExist = false;
            $cron = new \MGModule\SSLCENTERWHMCS\controllers\addon\admin\Cron();
            $service = \WHMCS\Service\Service::where('id', $input['id'])->get();
            $result = $cron->createAutoInvoice(array($input['params']['pid'] => $service), $input['id'], true);
            if(is_array($result) && isset($result['invoiceID']))
            {
                $existInvoiceID = $result['invoiceID'];
                $errorInvoiceExist =  \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('Related invoice already exist.');
            }
        }
        catch(Exception $e)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMC Renew Action Error: " . $e->getMessage());
            return array(
                'error' => $e->getMessage(),
            );
        }
        if($errorInvoiceExist)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMC Renew Action Error: " . $errorInvoiceExist);

            return array(
                'error' => $errorInvoiceExist,
                'invoiceID' => $existInvoiceID
            );
        }

        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMC Renew Action: A new invoice has been successfully created for the Service ID: " . $input['id']);
        return array(
            'success' => true,
            'msg' =>  main\mgLibs\Lang::absoluteT('A new invoice has been successfully created. '),
            'invoiceID' => $result
        );
    }

    public function resendValidationEmailJSON($input, $vars = array()) {
        $ssl = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $ssl->getByServiceId($input['id']);
        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->resendValidationEmail($serviceSSL->remoteid);

        return array(
            'success' => $response['message']
        );
    }

    public function sendCertificateEmailJSON($input, $vars = array()) {
        $ssl = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $ssl->getByServiceId($input['id']);
        $orderStatus = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($serviceSSL->remoteid);

        if($orderStatus['status'] !== 'active') {
            throw new \Exception( \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('orderNotActiveError'));
        }

        if(empty($orderStatus['ca_code'])) {
            throw new \Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('CACodeEmptyError'));
        }
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $sendCertyficateTermplate = $apiConf->send_certificate_template;

        $pathAttachemts = false;
        $checkSettings = Capsule::schema()->hasTable('tblfileassetsettings');
        if($checkSettings !== false) {
            $filesetting = Capsule::table('tblfileassetsettings')->where('asset_type', 'email_attachments')->first();
            if(isset($filesetting->storageconfiguration_id) && !empty($filesetting->storageconfiguration_id))
            {
                $checkStorage = Capsule::schema()->hasTable('tblstorageconfigurations');
                if($checkStorage !== false) {
                    $storage = Capsule::table('tblstorageconfigurations')->where('id', $filesetting->storageconfiguration_id)->first();
                    if(isset($storage->settings) && !empty($storage->settings))
                    {
                        $storageData = json_decode($storage->settings, true);
                        if(isset($storageData['local_path']) && !empty($storageData['local_path']))
                        {
                            $pathAttachemts = $storageData['local_path'];
                        }
                    }
                }
            }
        }

        $attachments = array();
        if(!empty($orderStatus['ca_code'])) {
            if($pathAttachemts === false) {
                $tmp_ca_code = tempnam("/tmp", "FOO");
                $handle = fopen($tmp_ca_code, "w");
                fwrite($handle, $orderStatus['ca_code']);
                fclose($handle);
                $attachments[] = array('displayname' => 'ca_code.ca', 'path' => $tmp_ca_code);
            }
            else
            {
                $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$input['params']['serviceid'].$input['params']['accountid'].'_ca_code.ca';
                file_exists($filetemp) or touch($filetemp);
                file_put_contents($filetemp, $orderStatus['csr_code']);
                $attachments[] = array(
                    'displayname' => $input['params']['serviceid'].$input['params']['accountid'].'_ca_code.ca',
                    'filename' => $input['params']['serviceid'].$input['params']['accountid'].'_ca_code.ca'
                );
            }
        }

        if(!empty($orderStatus['crt_code'])) {
            if($pathAttachemts === false) {
                $tmp_crt_code = tempnam("/tmp", "FOO");
                $handle = fopen($tmp_crt_code, "w");
                fwrite($handle, $orderStatus['crt_code']);
                fclose($handle);
                $attachments[] = array('displayname' => 'crt_code.crt', 'path' => $tmp_crt_code);
            }
            else
            {
                $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$input['params']['serviceid'].$input['params']['accountid'].'_crt_code.crt';
                file_exists($filetemp) or touch($filetemp);
                file_put_contents($filetemp, $orderStatus['csr_code']);
                $attachments[] = array(
                    'displayname' => $input['params']['serviceid'].$input['params']['accountid'].'_crt_code.crt',
                    'filename' => $input['params']['serviceid'].$input['params']['accountid'].'_crt_code.crt'
                );
            }
        }

        if(!empty($orderStatus['csr_code'])) {
            if($pathAttachemts === false) {
                $tmp_csr_code = tempnam("/tmp", "FOO");
                $handle = fopen($tmp_csr_code, "w");
                fwrite($handle, $orderStatus['csr_code']);
                fclose($handle);
                $attachments[] = array('displayname' => 'csr_code.csr', 'path' => $tmp_csr_code);
            }
            else
            {
                $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$input['params']['serviceid'].$input['params']['accountid'].'_csr_code.csr';
                file_exists($filetemp) or touch($filetemp);
                file_put_contents($filetemp, $orderStatus['csr_code']);
                $attachments[] = array(
                    'displayname' => $input['params']['serviceid'].$input['params']['accountid'].'_csr_code.csr',
                    'filename' => $input['params']['serviceid'].$input['params']['accountid'].'_csr_code.csr'
                );
            }
        }

        if($sendCertyficateTermplate == NULL)
        {
            $result = sendMessage(\MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::SEND_CERTIFICATE_TEMPLATE_ID, $input['id'], [
                'domain' => $orderStatus['domain'],
                'ssl_certyficate' => nl2br($orderStatus['ca_code']),
                'crt_code' => nl2br($orderStatus['crt_code']),
            ], false, $attachments);
        }
        else
        {
            $templateName = \MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::getTemplateName($sendCertyficateTermplate);
            $result = sendMessage($templateName, $input['id'], [
                'domain' => $orderStatus['domain'],
                'ssl_certyficate' => nl2br($orderStatus['ca_code']),
                'crt_code' => nl2br($orderStatus['crt_code']),
            ], false, $attachments);
        }

        if(!empty($orderStatus['ca_code'])) { unlink($tmp_ca_code); }
        if(!empty($orderStatus['crt_code'])) { unlink($tmp_crt_code); }
        if(!empty($orderStatus['csr_code'])) { unlink($tmp_csr_code); }

        if($result === true)
        {
            return array('success' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('sendCertificateSuccess'));
        }

        throw new \Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T($result));
    }

    function revalidateJSON($input, $vars = array()) {
        $serviceId  = $input['params']['serviceid'];
        $ssl        = new main\eRepository\whmcs\service\SSL();
        $sslService = $ssl->getByServiceId($serviceId);

        $brand = $input['brand'];

        // The dialog lists the domains saved in WHMCS. GoGetSSL rejects the whole request
        // ("This domain name not found in this certificate") if any of them is not on its order,
        // e.g. a saved www. SAN, so send only the domains the order really has.
        $orderMainDomain = $this->limitDcvChangeToOrderDomains($input, $sslService);
        if (empty($input['newdomains']) || empty($input['newDcvMethods'])) {
            return array('success' => 0, 'msg' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('changeDcvNoOrderDomains'));
        }

        if($brand == 'digicert' || $brand == 'geotrust' || $brand == 'thawte' || $brand == 'rapidssl')
        {
            if(isset($input['newDcvMethods']))
            {
                $newDcvMethodArray = array();
                foreach($input['newDcvMethods'] as $domain => $method)
                {
                    if(strpos($domain, '___') !== FALSE)
                    {
                        $domain = str_replace('___', '*', $domain);
                    }
                    $newDcvMethodArray[$domain] = $method;
                }
                $input['newDcvMethods'] = $newDcvMethodArray;
            }

            foreach ($input['newDcvMethods'] as $domain => $newMethod) {

                $newdomains = [];
                $new_methods = [];
                foreach ($input['newdomains'] as $newd)
                {
                    $newdomains[] = str_replace('___', '*', $newd);
                    $new_methods[] = $newMethod;
                }
                $data = [
                    'new_methods' => implode(',', $new_methods),
                    'domains'     => implode(',', $newdomains)
                ];

                try
                {
                    $response = $this->changeDcvOnOrder($sslService, $newdomains, $new_methods, $orderMainDomain, $data);
                } catch (\Exception $ex) {
                    if(strpos($ex->getMessage(), 'Function is locked for') !== false ) {
                        if(strpos($domain, '___') !== FALSE)
                        {
                            $domain = str_replace('___', '*', $domain);
                        }
                        $message = substr($ex->getMessage(), 0, -1) . ' for the domain: ' . $domain . '.';
                    } else {
                        $message = $domain.': '.$ex->getMessage();
                    }
                    return array('success' => 0, 'msg' => $message);
                }
            }
        }
        else
        {
            $new_methods = [];
            $newdomains = [];
            $positionalMethods = array_values($input['newDcvMethods']);
            foreach (array_values($input['newdomains']) as $i => $newdomain) {
                $methodKey = $newdomain;
                $newdomain = str_replace('___', '*', $newdomain);
                if (isset($input['newDcvMethods'][$methodKey])) {
                    $method = $input['newDcvMethods'][$methodKey];
                } elseif (isset($input['newDcvMethods'][$newdomain])) {
                    $method = $input['newDcvMethods'][$newdomain];
                } elseif (!array_filter(array_keys($input['newDcvMethods']), 'is_string') && isset($positionalMethods[$i])) {
                    $method = $positionalMethods[$i];
                } else {
                    continue; // no new method chosen for this domain
                }
                $newdomains[]  = $newdomain;
                $new_methods[] = $method;
            }
            if (!$newdomains) {
                return array('success' => 0, 'msg' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('changeDcvNoOrderDomains'));
            }
            $data = [
                'new_methods' => implode(',', $new_methods),
                'domains'     => implode(',', $newdomains)
            ];

            try
            {
                $response = $this->changeDcvOnOrder($sslService, $newdomains, $new_methods, $orderMainDomain, $data);
            } catch (\Exception $ex) {
                $message = isset($domain) ? $domain.': '.$ex->getMessage() : $ex->getMessage();
                return array('success' => 0, 'msg' => $message);
            }
        }

        $sslorder = (array)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->first();
        $sslorderconfigdata = json_decode($sslorder['configdata'], true);
        $sslorderconfigdata['dcv_method'] = isset($data['new_methods']) ? explode(',', $data['new_methods'])[0] : '';
        if($sslorderconfigdata['dcv_method'] != 'email') { $sslorderconfigdata['approveremail'] = ''; }
        Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(array(
            'configdata' => json_encode($sslorderconfigdata)
        ));

        return array('success' => $response['success'], 'msg' => $response['message']);
    }

    /**
     * Calls change_domains_validation_method. GoGetSSL lists the free www. twin of the main domain
     * (e.g. DomainSSL/PositiveSSL) as a SAN of the order but rejects it here with
     * "This domain name not found in this certificate"; the main domain's method covers it,
     * so on that error the request is sent once more without the twin.
     */
    protected function changeDcvOnOrder($sslService, array &$domains, array &$methods, $mainDomain, array &$data)
    {
        $api = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi();

        try {
            return $api->changeDomainValidationMethod($sslService->remoteid, $data);
        } catch (\Exception $ex) {
            if ($mainDomain === '' || stripos($ex->getMessage(), 'not found in this certificate') === false) {
                throw $ex;
            }

            $twins = [
                'www.' . $mainDomain => true,
                (strpos($mainDomain, 'www.') === 0 ? substr($mainDomain, 4) : '') => true,
            ];
            $keptDomains = [];
            $keptMethods = [];
            $dropped     = [];
            $hasMain     = false;
            $twinMethod  = null;
            foreach (array_values($domains) as $i => $domain) {
                $method = isset($methods[$i]) ? $methods[$i] : end($methods);
                $name   = strtolower(trim($domain));
                if (isset($twins[$name])) {
                    $dropped[]  = $domain;
                    $twinMethod = $twinMethod === null ? $method : $twinMethod;
                    continue;
                }
                $hasMain       = $hasMain || $name === $mainDomain;
                $keptDomains[] = $domain;
                $keptMethods[] = $method;
            }
            if (!$dropped) {
                throw $ex;
            }
            // Only the twin was listed (e.g. WHMCS saved www.example.com): change the main domain instead
            if (!$hasMain) {
                array_unshift($keptDomains, $mainDomain);
                array_unshift($keptMethods, $twinMethod);
            }

            $domains = $keptDomains;
            $methods = $keptMethods;
            $data    = [
                'new_methods' => implode(',', $keptMethods),
                'domains'     => implode(',', $keptDomains),
            ];
            (new LogsRepo())->addLog($sslService->userid, $sslService->serviceid, 'info',
                '[Change DCV] GoGetSSL does not accept ' . implode(', ', $dropped) . ' on order #' . $sslService->remoteid
                . ' (covered by ' . $mainDomain . '); sent again without it');

            return $api->changeDomainValidationMethod($sslService->remoteid, $data);
        }
    }

    /**
     * Drops from the change-DCV request every domain that is not on the GoGetSSL order
     * (main domain + SANs from the API). If the order cannot be read, the request is left unchanged.
     * Returns the order's main domain (lower case), or '' if it is unknown.
     */
    protected function limitDcvChangeToOrderDomains(array &$input, $sslService)
    {
        if (!$sslService || empty($sslService->remoteid)) {
            return '';
        }

        try {
            $order = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
        } catch (\Exception $ex) {
            return '';
        }

        $normalize = function ($domain) {
            return strtolower(trim(str_replace('___', '*', (string) $domain)));
        };

        $mainDomain   = !empty($order['domain']) ? $normalize($order['domain']) : '';
        $orderDomains = [];
        if ($mainDomain !== '') {
            $orderDomains[$mainDomain] = true;
        }
        if (!empty($order['san']) && is_array($order['san'])) {
            foreach ($order['san'] as $san) {
                if (is_array($san) && !empty($san['san_name'])) {
                    $orderDomains[$normalize($san['san_name'])] = true;
                }
            }
        }
        if (empty($orderDomains)) {
            return $mainDomain;
        }

        // A www. / bare twin of the main domain that the order does not list stands for the main domain
        $resolve = function ($domain) use ($normalize, $orderDomains, $mainDomain) {
            $name = $normalize($domain);
            if (isset($orderDomains[$name])) {
                return $name;
            }
            if ($mainDomain !== '' && ($name === 'www.' . $mainDomain || 'www.' . $name === $mainDomain)) {
                return $mainDomain;
            }
            return null;
        };

        $skipped = [];

        $domains = [];
        foreach ((array) (isset($input['newdomains']) ? $input['newdomains'] : []) as $domain) {
            $name = $resolve($domain);
            if ($name === null) {
                $skipped[$normalize($domain)] = true;
            } elseif (!isset($domains[$name])) {
                $domains[$name] = $name;
            }
        }
        $input['newdomains'] = $domains;

        $methods = [];
        foreach ((array) (isset($input['newDcvMethods']) ? $input['newDcvMethods'] : []) as $domain => $method) {
            if (is_int($domain)) {
                $methods[$domain] = $method;
                continue;
            }
            $name = $resolve($domain);
            if ($name === null) {
                $skipped[$normalize($domain)] = true;
            } elseif (!isset($methods[$name])) {
                $methods[$name] = $method;
            }
        }
        $input['newDcvMethods'] = $methods;

        if ($skipped) {
            (new LogsRepo())->addLog($sslService->userid, $sslService->serviceid, 'info',
                '[Change DCV] Skipped domains that are not on the GoGetSSL order #' . $sslService->remoteid . ': '
                . implode(', ', array_keys($skipped)));
        }

        return $mainDomain;
    }

    public function getApprovalEmailsForDomainJSON($input, $vars = array()) {
        $domainEmails = [];

        if($input['brand'] == 'geotrust') {
            $apiDomainEmails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmailsForGeotrust($input['domain']);
            $domainEmails = $apiDomainEmails['GeotrustApprovalEmails'];
        } else {
            $apiDomainEmails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($input['domain']);
            $domainEmails = $apiDomainEmails['ComodoApprovalEmails'];
        }

        return [
            'success'      => 1,
            'domainEmails' => $domainEmails
        ];
    }

    function changeApproverEmailJSON($input, $vars = array()) {
        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $ssService = $sslRepo->getByServiceId($input['serviceId']);

        $data = ['approver_email' => $input['newEmail']];
        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeValidationEmail($ssService->remoteid, $data);

        $ssService->setConfigdataKey("approveremail", $data['approver_email']);
        $ssService->save();

        return array(
            'success' => $response['success'],
            'msg'     => $response['success_message']
        );
    }

    function getPrivateKeyJSON($input, $vars = array()) {
        $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);

        if ($privateKey = $sslService->getPrivateKey()) {
            if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
                $privateKey = decrypt($privateKey);
            }
            return array('success' => 1, 'privateKey' => $privateKey);
        }

        return array(
            'success' => 0,
            'message' => main\mgLibs\Lang::absoluteT('Can not get Private Key, please refresh page or contact support')
        );
    }

    function installCertificateJSON($input, $vars = array()) {
        $logsRepo  = new LogsRepo();
        $orderRepo = new OrderRepo();
        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);

        $details  = (array)$sslService->configdata;
        $cert     = $details['crt'];
        $cabundle = $details['ca'];
        $key      = '';
        if (!empty($details['private_key'])) {
            $key = (strpos($details['private_key'], '-----BEGIN') !== false) ? $details['private_key'] : decrypt($details['private_key']);
        }

        try {
            $service       = new Service();
            $serviceCpanel = $service->getServiceForSsl($sslService->serviceid, $sslService->userid, $details['domain']);
            if($serviceCpanel !== false) {
                $cpanel = new Cpanel();
                $cpanel->setService($serviceCpanel);
                $cpanel->installSSL($serviceCpanel->user, $details['domain'], $cert, $key, $cabundle);
                $logsRepo->addLog($sslService->userid, $sslService->serviceid, 'success', '[Install] Certificate installed for domain: ' . $details['domain']);
                $installDetails = $cpanel->describeLastInstall();
                if ($installDetails !== '') {
                    $logsRepo->addLog($sslService->userid, $sslService->serviceid,
                        empty($cpanel->getLastInstallReport()['failed']) ? 'info' : 'error',
                        '[Install] Wildcard certificate ' . $installDetails);
                }
                $orderRepo->updateStatus($sslService->serviceid, 'Success');
            }
        } catch (\Exception $e) {
            $logsRepo->addLog($sslService->userid, $sslService->serviceid, 'error', '[Install] Error for domain: ' . $details['domain'] . ' — ' . $e->getMessage());
            return ['success' => 0, 'message' => $e->getMessage()];
        }

        return ['success' => 1, 'message' => main\mgLibs\Lang::absoluteT('The certificate has been installed correctly')];
    }

    /**
     * Saves "Install certificate on" (cPanel hosting service) and the automatic cPanel installation switch.
     */
    function saveInstallTargetJSON($input, $vars = array()) {
        $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);

        if (!$sslService || (int) $sslService->userid !== (int) $input['params']['userid']) {
            return ['success' => false, 'message' => main\mgLibs\Lang::absoluteT('serverCA', 'home', 'anErrorOccurred')];
        }

        $result = \MGModule\SSLCENTERWHMCS\eHelpers\InstallTarget::save(
            $sslService,
            isset($input['install_target']) ? (int) $input['install_target'] : 0,
            !empty($input['install_auto'])
        );

        if (!$result['success']) {
            return ['success' => false, 'message' => main\mgLibs\Lang::absoluteT('serverCA', 'home', 'installTargetError_' . $result['error'])];
        }

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('serverCA', 'home', $result['queued'] ? 'installTargetSavedQueued' : 'installTargetSaved'),
        ];
    }

    /**
     * Seconds the Revalidate button stays disabled after it was used.
     */
    protected function getRevalidateCooldownSeconds() {
        return 180;
    }

    /**
     * Revalidate (/orders/ssl/revalidate/) is offered only for standard SSL orders that:
     * - are not Automated SSL products (ACME 300, AutoInstall SSL 400-403),
     * - are installed manually (install_type is not "autoinstall"),
     * - have API status "processing",
     * - use HTTP/HTTPS/DNS validation for the main domain or at least one SAN.
     *
     * @return array ['allowed' => bool, 'cooldown' => seconds left before it can be used again]
     */
    protected function getRevalidateState($params, $sslService, $activationStatus) {
        $state = ['allowed' => false, 'cooldown' => 0];

        if (!$sslService || empty($sslService->remoteid)) {
            return $state;
        }

        if (\MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeByServiceParams($params)
            || \MGModule\SSLCENTERWHMCS\eHelpers\AisSubscription::isAisByServiceParams($params)) {
            return $state;
        }

        $configdata  = (array) $sslService->configdata;
        $installType = isset($configdata['install_type']) ? (string) $configdata['install_type'] : 'manual';
        // Automatic installation: the module validates by itself while its validate task runs;
        // without one (e.g. after a manual reissue in older versions) the client must be able to restart it
        if ($installType === 'autoinstall'
            && (new \MGModule\SSLCENTERWHMCS\models\cronQueue\Repository())->getActiveTask($sslService->serviceid, 'validate')) {
            return $state;
        }

        if (strtolower((string) $activationStatus) !== 'processing') {
            return $state;
        }

        $methods = [];
        if (!empty($configdata['dcv_method'])) {
            $methods[] = (string) $configdata['dcv_method'];
        }
        if (!empty($configdata['san_details'])) {
            foreach ((array) $configdata['san_details'] as $san) {
                $san = (object) $san;
                if (!empty($san->validation_method)) {
                    $methods[] = (string) $san->validation_method;
                }
            }
        }
        if (!array_intersect($methods, ['http', 'https', 'dns'])) {
            return $state;
        }

        $lastAt = isset($configdata['revalidate_last_at']) ? (int) $configdata['revalidate_last_at'] : 0;

        $state['allowed']  = true;
        $state['cooldown'] = max(0, $lastAt + $this->getRevalidateCooldownSeconds() - time());

        return $state;
    }

    function revalidateNewJSON($input, $vars = array()) {
        $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);

        $activationStatus = '';
        if ($sslService && !empty($sslService->remoteid)) {
            try {
                $orderStatus      = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
                $activationStatus = isset($orderStatus['status']) ? $orderStatus['status'] : '';
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        $state = $this->getRevalidateState($input['params'], $sslService, $activationStatus);
        if (!$state['allowed']) {
            return ['success' => false, 'message' => main\mgLibs\Lang::absoluteT('serverCA', 'home', 'revalidateNotAvailable')];
        }
        if ($state['cooldown'] > 0) {
            return [
                'success'  => false,
                'message'  => main\mgLibs\Lang::absoluteT('serverCA', 'home', 'revalidateCooldown'),
                'cooldown' => $state['cooldown'],
            ];
        }

        $sslService->setConfigdataKey('revalidate_last_at', time());
        $sslService->save();

        $configdata = (array) $sslService->configdata;
        $domain     = !empty($configdata['domain']) ? $configdata['domain'] : $input['params']['domain'];
        $cooldown   = $this->getRevalidateCooldownSeconds();

        // Automatic installation: restart the module's validation (uploads the HTTP/HTTPS validation
        // file to cPanel, then installs the certificate once it is issued)
        if (isset($configdata['install_type']) && $configdata['install_type'] === 'autoinstall') {
            \MGModule\SSLCENTERWHMCS\eHelpers\ReissueValidation::queue(
                $sslService, $sslService->userid, $sslService->serviceid, $domain,
                isset($configdata['crt']) ? $configdata['crt'] : '', false
            );
        }

        try {
            $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->revalidate($sslService->remoteid, ['domain' => $domain]);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'cooldown' => $cooldown];
        }

        $success = !empty($response['success']);
        $message = !empty($response['message'])
            ? $response['message']
            : main\mgLibs\Lang::absoluteT('serverCA', 'home', $success ? 'revalidateSuccess' : 'anErrorOccurred');

        return ['success' => $success, 'message' => $message, 'cooldown' => $cooldown];
    }

    function getCertificateDetailsJSON($input, $vars = array()) {
        $clientCheckCertificateDetails = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\ClientRecheckCertificateDetails($input);
        $details = $clientCheckCertificateDetails->run();
    }

    function getPasswordJSON($input, $vars = array()) {
        unset($input);
        unset($vars);

        return array('password' => 'fuNPassword');
    }
}
