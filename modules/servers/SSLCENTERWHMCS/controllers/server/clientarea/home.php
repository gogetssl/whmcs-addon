<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea;

use MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule;

/**
 * Description of home
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class home extends main\mgLibs\process\AbstractController {

    function indexHTML($input, $vars = array()) {
        try {

            global $CONFIG;

            if($input['params']['status'] != 'Active')
            {
                return true;
            }

            $disabledValidationMethods = [];
 
            $serviceId  = $input['params']['serviceid'];
            $serviceBillingCycle = $input['params']['templatevars']['billingcycle'];
            $userid = $input['params']['userid'];
            $ssl        = new main\eRepository\whmcs\service\SSL();
            $sslService = $ssl->getByServiceId($serviceId);
        
            if(($sslService->configdata->ssl_status == 'pending' || $sslService->configdata->ssl_status == 'reissue' || $sslService->configdata->ssl_status == 'new_order' || $sslService->configdata->ssl_status == 'processing' || $sslService->configdata->ssl_status == '') && $sslService->remoteid != '')
            {
                $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
                $sslService = $sslRepo->getByServiceId($serviceId);

                if (is_null($sslService)) {
                    throw new \Exception('Create has not been initialized');
                }

                if ($input['params']['userid'] != $sslService->userid) {
                    throw new \Exception('An error occurred');
                }

                $apicertdata = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);

                $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService, $apicertdata);
                $configDataUpdate->run();

               // if($apicertdata['status'] != 'new_order')
                //{
                //    $sslService->setSSLStatus($apicertdata['status']);
                //    $sslService->setPartnerOrderId($apicertdata['partner_order_id']);
                //    $sslService->setApproverEmails($apicertdata['approver_email']);
                //    $sslService->setDomain($apicertdata['domain']);
                //    $sslService->save();
                //}
                $vars['activationStatus'] = $apicertdata['status'];
                //var_dump($apicertdata);
            }
            //var_dump(\MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid)); die;

            $vars['brandsWithOnlyEmailValidation'] = ['geotrust','thawte','rapidssl','symantec',];

            if(is_null($sslService)) {
                throw new \Exception('An error occurred please contact support');
            }

            $url = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Config::getInstance()->getConfigureSSLUrl($sslService->id, $serviceId);

            $vars['privateKey'] = '';
            $privateKey = $sslService->getPrivateKey();
            if($privateKey) {
                $vars['privateKey'] = $privateKey;
            }
            $vars['san_revalidate'] = false;

            if ($sslService->status !== 'Awaiting Configuration') {
                try {
                    $certificateDetails = (array)$sslService->configdata;

                    if(!empty($certificateDetails['partner_order_id'])) {
                        $vars['partner_order_id'] = $certificateDetails['partner_order_id'];
                    }
                    if(!empty($certificateDetails['product_brand'])) {
                        $vars['brand'] = $certificateDetails['product_brand'];
                    }

                    if(!empty($certificateDetails['dcv_method']))
                    {
                        $vars['dcv_method'] = $certificateDetails['dcv_method'];

                        if(in_array($vars['dcv_method'], ["http", "https", "dns"]))
                        {

                            if(is_array($certificateDetails['approver_method']))
                            {
                                $vars['approver_method'][$vars['dcv_method']] = $certificateDetails['approver_method'][$vars['dcv_method']];
                            }
                            else
                            {
                                $vars['approver_method'][$vars['dcv_method']] = (array) $certificateDetails['approver_method']->{$vars['dcv_method']};
                            }
                            
                            if($vars['dcv_method'] == 'http' || $vars['dcv_method'] == 'https'){
                               $vars['approver_method'][$vars['dcv_method']]['content'] = explode(PHP_EOL, $vars['approver_method'][$vars['dcv_method']]['content']);
                            }
                        } else {
                            $vars['dcv_method'] = 'email';
                            $vars['approver_method'] = $certificateDetails['approveremail'];
                        }
                    }

                    if (!empty($certificateDetails['csr'])) {
                        $vars['csr'] = ($certificateDetails['csr']);
                    }

                    if (!empty($certificateDetails['crt'])) {
                        $vars['crt'] = ($certificateDetails['crt']);
                    }
                    if (!empty($certificateDetails['ca'])) {
                        $vars['ca'] = ($certificateDetails['ca']);
                    }

                    if (!empty($certificateDetails['domain'])) {
                        $vars['domain'] = $certificateDetails['domain'];
                    }

                    if (!empty($certificateDetails['san_details'])) {
                        foreach ($certificateDetails['san_details'] as $san) {
                            $vars['sans'][$san->san_name]['san_name'] = $san->san_name;
                            $vars['sans'][$san->san_name]['method'] = $san->validation_method;
                            switch ($san->validation_method) {
                                case 'dns':
                                    $vars['san_revalidate'] = true;
                                    $vars['sans'][$san->san_name]['san_validation'] = $san->validation->dns->record;
                                    break;
                                case 'http':
                                    $vars['san_revalidate'] = true;
                                    $vars['sans'][$san->san_name]['san_validation'] = (array)$san->validation->http;
                                    $vars['sans'][$san->san_name]['san_validation']['content'] = explode(PHP_EOL, $san->validation->http->content);
                                    break;
                                case 'https':
                                    $vars['san_revalidate'] = true;
                                    $vars['sans'][$san->san_name]['san_validation'] = (array)$san->validation->https;
                                    $vars['sans'][$san->san_name]['san_validation']['content'] = explode(PHP_EOL, $san->validation->https->content);
                                    break;
                                default:
                                    $vars['sans'][$san->san_name]['san_validation'] = $san->validation->email;
                                    break;
                            }
                        }
                    }
                    if (!$vars['activationStatus']) {
                        $vars['activationStatus'] = $certificateDetails['ssl_status'];
                    }
                    //valid from
                    $vars['validFrom'] = fromMySQLDate($certificateDetails['valid_from'], false, true);
                    //expires
                    $vars['validTill'] = fromMySQLDate($certificateDetails['valid_till'],false,true);
                                        
                    $now = strtotime(date('Y-m-d'));
                    $end_date = strtotime($certificateDetails['valid_till']);
                    $datediff = $now - $end_date;
                        
                    $vars['nextReissue'] = abs(round($datediff / (60 * 60 * 24)));
                    
                    if(isset($certificateDetails['begin_date']) && !empty($certificateDetails['begin_date']))
                    {
                        $vars['subscriptionStarts'] = fromMySQLDate($certificateDetails['begin_date'],false,true);
                    }
                    
                    if(isset($certificateDetails['end_date']) && !empty($certificateDetails['end_date']))
                    {
                        $vars['subscriptionEnds'] = fromMySQLDate($certificateDetails['end_date'],false,true);
                    }
                    
                    //service billing cycle
                    $vars['serviceBillingCycle'] = $serviceBillingCycle;
                    $vars['displayRenewButton'] = false;
                    $today = date('Y-m-d');
                    $diffDays =  abs(strtotime($certificateDetails['end_date']) - strtotime($today))/86400;

                    if($diffDays < 30)
                        $vars['displayRenewButton'] = true;

                    $disabledValidationMethods = [];
                    $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
                    
                    $product = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($input['params']['pid']);
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
                    
                } catch (\Exception $ex) {
                    $vars['error'] = 'Can not load order details';
                }
            }

            $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
            
            $vars['custom_guide'] = $apiConf->custom_guide;
            $vars['visible_renew_button'] = $apiConf->visible_renew_button;
            $vars['disabledValidationMethods'] = $disabledValidationMethods;
            $vars['configurationStatus'] = $sslService->status;
            $vars['configurationURL']    = $url;
            $vars['allOk']               = true;
            $vars['assetsURL'] = main\Server::I()->getAssetsURL();
            $vars['serviceid'] = $serviceId;
            $vars['userid'] = $userid;
            
            $filenameCsr = isset($vars['domain']) && !empty($vars['domain']) ? $vars['domain'] : 'csr_code';
            $filenameCrt = isset($vars['domain']) && !empty($vars['domain']) ? $vars['domain'] : 'crt_code';
            $filenameCa = isset($vars['domain']) && !empty($vars['domain']) ? $vars['domain'] : 'ca_code';
            
            if($_GET['download'] == '1')
            {
                if(isset($vars['sans'][$_GET['domain']]) && !empty($vars['sans'][$_GET['domain']]) && ($vars['sans'][$_GET['domain']]['method'] == 'http' || $vars['sans'][$_GET['domain']]['method'] == 'https'))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['sans'][$_GET['domain']]['san_validation']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    print implode(PHP_EOL, $vars['sans'][$_GET['domain']]['san_validation']['content']);
                    exit;
                }

                if(isset($vars['approver_method']['https']) && !empty($vars['approver_method']['https']))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['approver_method']['https']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    print implode(PHP_EOL, $vars['approver_method']['https']['content']);
                    exit;
                }

                if(isset($vars['approver_method']['http']) && !empty($vars['approver_method']['http']))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['approver_method']['http']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    print implode(PHP_EOL, $vars['approver_method']['http']['content']);
                    exit;
                }
            }

            if($_GET['downloadcsr'] == '1' && !empty($certificateDetails['csr']))
            {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$filenameCsr.'.csr');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                print $certificateDetails['csr'];
                exit;
            }
            if($_GET['downloadcrt'] == '1' && !empty($certificateDetails['crt']))
            {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$filenameCrt.'.crt');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                print $certificateDetails['crt'];
                exit;
            }
            if($_GET['downloadca'] == '1' && !empty($certificateDetails['ca']))
            {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$filenameCa.'.ca');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                print $certificateDetails['ca'];
                exit;
            }
            if($_GET['downloadpem'] == '1' && !empty($certificateDetails['crt']) && !empty($certificateDetails['ca']))
            {
                $pemfile = '';
                
                $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
                $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);
                $privateKey = $sslService->getPrivateKey();

                if(!empty($privateKey))
                {
                    if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') !== false) {
                        $pemfile .= $privateKey;
                    }
                    else {
                        $pemfile .= decrypt($privateKey);
                    }
                }

                $pemfile .= $certificateDetails['crt']. "\n";
                $pemfile .= $certificateDetails['ca'];
                
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$filenameCa.'.pem');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                print $pemfile;
                exit;
            }

            $vars['actual_link'] = $CONFIG['SystemURL'].'/clientarea.php?action=productdetails&id='.$vars['serviceid'];

            $vars['btndownload'] = false;

            if (!empty($certificateDetails['csr'])) {
                $vars['downloadcsr'] = $vars['actual_link'].'&downloadcsr=1';
            }

            if (!empty($certificateDetails['crt'])) {
                $vars['downloadcrt'] = $vars['actual_link'].'&downloadcrt=1';
            }

            if (!empty($certificateDetails['ca'])) {
                $vars['downloadca'] = $vars['actual_link'].'&downloadca=1';
            }
            
            if (!empty($certificateDetails['crt']) && !empty($certificateDetails['ca'])) {
                $vars['downloadpem'] = $vars['actual_link'].'&downloadpem=1';
            }

            if((isset($vars['approver_method']['http']) && !empty($vars['approver_method']['http'])) || (isset($vars['approver_method']['https']) && !empty($vars['approver_method']['https'])))
            {
                $vars['btndownload'] = $vars['actual_link'].'&download=1';
            }

            foreach($vars['sans'] as $detailssan)
            {
                if($detailssan['method'] == 'http' || $detailssan['method'] == 'https')
                {
                    $vars['btndownload'] = $vars['actual_link'].'&download=1&domain='.$detailssan['san_name'];
                }
            }

        } catch (\Exception $ex) {
            $vars['error'] = $ex->getMessage();
        }

        $vars['configoption24'] = $input['params']['configoption24'];
           
        $vars['approver_email'] = isset($sslService->configdata->approver_method->email) && !empty($sslService->configdata->approver_method->email) ? $sslService->configdata->approver_method->email : false;
        
        return array(
            'tpl'  => 'home'
            , 'vars' => $vars
        );

    }

    function testHTML($input, $vars = array()) {
        return array(
            'tpl'  => 'test'
            , 'vars' => $vars
        );

    }

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
            'msg' =>  main\mgLibs\Lang::getInstance()->T('A new invoice has been successfully created. '),
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
            throw new \Exception( \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('orderNotActiveError')); //Can not send certificate. Order status is different than active.
        }

        if(empty($orderStatus['ca_code'])) {
            throw new \Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('CACodeEmptyError')); //An error occurred. Certificate body is empty.
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

                $attachments[] = array(
                    'displayname' => 'ca_code.ca',
                    'path' => $tmp_ca_code
                );
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

                $attachments[] = array(
                    'displayname' => 'crt_code.crt',
                    'path' => $tmp_crt_code
                );
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

                $attachments[] = array(
                    'displayname' => 'csr_code.csr',
                    'path' => $tmp_csr_code
                );
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

        if(!empty($orderStatus['ca_code'])) {

            unlink($tmp_ca_code);

        }

        if(!empty($orderStatus['crt_code'])) {

            unlink($tmp_crt_code);

        }

        if(!empty($orderStatus['csr_code'])) {

            unlink($tmp_csr_code);

        }

        if($result === true)
        {
             return array(
                'success' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('sendCertificateSuccess')
            );
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

                $input['newDcvMethods']= $newDcvMethodArray;
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
                    'new_methods'      => implode(',', $new_methods),
                    'domains'          => implode(',', $newdomains)
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

                    return array(
                        'success' => 0,
                        'msg'     => $message
                    );
                }
            }

            $sslorder = (array)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->first();

            $sslorderconfigdata = json_decode($sslorder['configdata'], true);

            $sslorderconfigdata['dcv_method'] = $data['new_method'];

            if($data['new_method'] != 'email')
            {
                $sslorderconfigdata['approveremail'] = '';
            }

            Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(array(
                'configdata' => json_encode($sslorderconfigdata)
            ));

            return array(
                'success' => $response['success'],
                'msg'     => $response['message']
            );
        }
        else
        {
            $new_methods = [];
            $newdomains = [];
            
            foreach($input['newDcvMethods'] as $method)
            {
                $new_methods[] = $method;
            }
            
            foreach($input['newdomains'] as $newdomain)
            {
                $newdomains[] = str_replace('___', '*', $newdomain);
            }
            $data = [
                'new_methods'      => implode(',', $new_methods),
                'domains'          => implode(',', $newdomains)
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

                return array(
                    'success' => 0,
                    'msg'     => $message
                );
            }

            $sslorder = (array)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->first();

            $sslorderconfigdata = json_decode($sslorder['configdata'], true);

            $sslorderconfigdata['dcv_method'] = $data['new_method'];

            if($data['new_method'] != 'email')
            {
                $sslorderconfigdata['approveremail'] = '';
            }

            Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(array(
                'configdata' => json_encode($sslorderconfigdata)
            ));

            return array(
                'success' => $response['success'],
                'msg'     => $response['message']
            );
        }
        
        
  
    }

    public function getApprovalEmailsForDomainJSON($input, $vars = array()) {

        $domainEmails = [];

        if($input['brand'] == 'geotrust') {
            $apiDomainEmails             = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmailsForGeotrust($input['domain']);
            $domainEmails = $apiDomainEmails['GeotrustApprovalEmails'];
        } else {
            $apiDomainEmails             = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($input['domain']);
            $domainEmails = $apiDomainEmails['ComodoApprovalEmails'];
        }
        $result = [
            'success' => 1,
            'domainEmails' => $domainEmails
        ];

        return $result;
    }

    function changeApproverEmailJSON($input, $vars = array()) {

        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $ssService = $sslRepo->getByServiceId($input['serviceId']);

        $data = [
            'approver_email' => $input['newEmail']
        ];

        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeValidationEmail($ssService->remoteid, $data);

        $ssService->setConfigdataKey("approveremail", $data['approver_email']);
        $ssService->save();

        return array(
            'success' => $response['success'],
            'msg'     => $response['success_message']
        );
    }

    function getPrivateKeyJSON($input, $vars = array()) {
        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);
        $privateKey = $sslService->getPrivateKey();

        if($privateKey = $sslService->getPrivateKey()) {
            if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
                $privateKey =  decrypt($privateKey);
            }

            $result = array(
                'success'     => 1,
                'privateKey'  => $privateKey
            );
        } else {
            $result = array(
                'success'   => 0,
                'message'   => main\mgLibs\Lang::getInstance()->T('Can not get Private Key, please refresh page or contact support')
            );
        }

        return $result;
    }

    function revalidateNewJSON($input, $vars = array()) {

        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['id']);

        $data = [
            'domain' => $input['params']['domain']
        ];

        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->revalidate($sslService->remoteid, $data);

        return $response;
    }

    function getCertificateDetailsJSON($input, $vars = array()) {

        $clientCheckCertificateDetails = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\ClientRecheckCertificateDetails($input);
        $details = $clientCheckCertificateDetails->run();
    }

    function getPasswordJSON($input, $vars = array()) {
        //do something with input
        unset($input);
        unset($vars);

        return array(
            'password' => 'fuNPassword'
        );

    }

}
