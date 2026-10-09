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
                    $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeDomainValidationMethod($sslService->remoteid, $data);
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
            foreach($input['newDcvMethods'] as $method) { $new_methods[] = $method; }
            foreach($input['newdomains'] as $newdomain) { $newdomains[] = str_replace('___', '*', $newdomain); }
            $data = [
                'new_methods' => implode(',', $new_methods),
                'domains'     => implode(',', $newdomains)
            ];

            try
            {
                $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeDomainValidationMethod($sslService->remoteid, $data);
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
        $key      = decrypt($details['private_key']);

        try {
            $service       = new Service();
            $serviceCpanel = $service->getServiceByDomain($sslService->userid, $details['domain']);
            if($serviceCpanel !== false) {
                $cpanel = new Cpanel();
                $cpanel->setService($serviceCpanel);
                $cpanel->installSSL($serviceCpanel->user, $details['domain'], $cert, $key, $cabundle);
                $logsRepo->addLog($sslService->userid, $sslService->serviceid, 'success', 'The certificate for the ' . $details['domain'] . ' domain has been installed correctly.');
                $orderRepo->updateStatus($sslService->serviceid, 'Success');
            }
        } catch (\Exception $e) {
            $logsRepo->addLog($sslService->userid, $sslService->serviceid, 'error', '['.$details['domain'].'] Error: '.$e->getMessage());
            return ['success' => 0, 'message' => $e->getMessage()];
        }

        return ['success' => 1, 'message' => main\mgLibs\Lang::absoluteT('The certificate has been installed correctly')];
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
        if ($installType === 'autoinstall') {
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
