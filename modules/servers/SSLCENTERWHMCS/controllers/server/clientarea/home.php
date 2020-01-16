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

            if($input['params']['status'] != 'Active')
            {
                return true;
            }
            
            $serviceId  = $input['params']['serviceid'];            
            $serviceBillingCycle = $input['params']['templatevars']['billingcycle'];            
            $userid = $input['params']['userid'];
            $ssl        = new main\eRepository\whmcs\service\SSL();
            $sslService = $ssl->getByServiceId($serviceId);
            
            if(($sslService->configdata->ssl_status == 'new_order' || $sslService->configdata->ssl_status == 'processing' || $sslService->getPartnerOrderId() == '' || $sslService->configdata->ssl_status == '') && $sslService->remoteid != '')
            {
                $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
                $sslService = $sslRepo->getByServiceId($serviceId);
        
                if (is_null($sslService)) {
                    throw new \Exception('Create has not been initialized');
                }
        
                if ($input['params']['userid'] != $sslService->userid) {
                    throw new \Exception('An error occurred');
                }
           
                $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService);
                $orderStatus = $configDataUpdate->run();

                $apicertdata = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
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
            
            $privateKey = $sslService->getPrivateKey();            
            if($privateKey) {
                $vars['privateKey'] = $privateKey;
            }             
            
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
                            $vars['approver_method'][$vars['dcv_method']] = (array) $certificateDetails['approver_method']->{$vars['dcv_method']};

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
                                    $vars['sans'][$san->san_name]['san_validation'] = $san->validation->dns->record;
                                    break;
                                case 'http':
                                    $vars['sans'][$san->san_name]['san_validation'] = (array)$san->validation->http;
                                    $vars['sans'][$san->san_name]['san_validation']['content'] = explode(PHP_EOL, $san->validation->http->content);
                                    break;
                                case 'https':
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
                    $vars['validFrom'] = $certificateDetails['valid_from'];
                    //expires
                    $vars['validTill'] = $certificateDetails['valid_till'];                    
                    //service billing cycle                   
                    $vars['serviceBillingCycle'] = $serviceBillingCycle;                    
                    $vars['displayRenewButton'] = false;
                    $today = date('Y-m-d');
                    $diffDays =  abs(strtotime($certificateDetails['valid_till']) - strtotime($today))/86400; 
                    
                    if($diffDays < 90)
                        $vars['displayRenewButton'] = true;
                    
                    
                    //get dsiabled validation methods
                    $disabledValidationMethods = array();
                    $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();        
                    if($apiConf->disable_email_validation && !in_array($vars['brand'], $vars['brandsWithOnlyEmailValidation']))
                    {
                        array_push($disabledValidationMethods, 'email');
                    }
                    
                } catch (\Exception $ex) {
                    $vars['error'] = 'Can not load order details';
                }
            } 
            
            $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();        
            $vars['visible_renew_button'] = $apiConf->visible_renew_button;
            $vars['disabledValidationMethods'] = $disabledValidationMethods;
            $vars['configurationStatus'] = $sslService->status;
            $vars['configurationURL']    = $url;
            $vars['allOk']               = true;
            $vars['assetsURL'] = main\Server::I()->getAssetsURL();
            $vars['serviceid'] = $serviceId;
            $vars['userid'] = $userid;
            
            
            if($_GET['download'] == '1')
            {
                if(isset($vars['approver_method']['https']) && !empty($vars['approver_method']['https']))
                {
                    $handle = fopen($vars['approver_method']['https']['filename'], "w");
                    fwrite($handle, implode(PHP_EOL, $vars['approver_method']['https']['content']));
                    fclose($handle);

                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['approver_method']['https']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($vars['approver_method']['https']['filename']));
                    readfile($vars['approver_method']['https']['filename']);
                    exit;
                }
                
                if(isset($vars['approver_method']['http']) && !empty($vars['approver_method']['http']))
                {
                    $handle = fopen($vars['approver_method']['http']['filename'], "w");
                    fwrite($handle, implode(PHP_EOL, $vars['approver_method']['http']['content']));
                    fclose($handle);

                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['approver_method']['http']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($vars['approver_method']['http']['filename']));
                    readfile($vars['approver_method']['http']['filename']);
                    exit;
                }
            }
            
            $vars['actual_link'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                        
        } catch (\Exception $ex) {
            $vars['error'] = $ex->getMessage();
        }

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
       

        if($sendCertyficateTermplate == NULL)
        {            
            $result = sendMessage(\MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::SEND_CERTIFICATE_TEMPLATE_ID, $input['id'], [
                'ssl_certyficate' => nl2br($orderStatus['ca_code']),
                'crt_code' => nl2br($orderStatus['crt_code']),
            ]);
        } 
        else
        {
            $templateName = \MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::getTemplateName($sendCertyficateTermplate);
            $result = sendMessage($templateName, $input['id'], [
                'ssl_certyficate' => nl2br($orderStatus['ca_code']),
                'crt_code' => nl2br($orderStatus['crt_code']),
            ]);
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
            $data = [
                'new_method'      => $newMethod, 
                'domain'          => $domain
            ];
            try 
            {  
                $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeValidationMethod($sslService->remoteid, $data);
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
            $result = array(
                'success'     => 1,
                'privateKey'  => decrypt($privateKey)
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
