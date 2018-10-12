<?php

namespace MGModule\GGSSLWHMCS\controllers\server\clientarea;

use MGModule\GGSSLWHMCS as main;

/**
 * Description of home
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class home extends main\mgLibs\process\AbstractController {

    function indexHTML($input, $vars = array()) {
        try {
            
            $serviceId  = $input['params']['serviceid'];            
            $serviceBillingCycle = $input['params']['templatevars']['billingcycle'];            
            $userid = $input['params']['userid'];
            $ssl        = new main\eRepository\whmcs\service\SSL();
            $sslService = $ssl->getByServiceId($serviceId);
            
            $vars['brandsWithOnlyEmailValidation'] = ['geotrust','thawte','rapidssl','symantec',];
           
            if(is_null($sslService)) {
                throw new \Exception('An error occurred please contact support');
            }

            $url = \MGModule\GGSSLWHMCS\eRepository\whmcs\config\Config::getInstance()->getConfigureSSLUrl($sslService->id, $serviceId);
            
            $privateKey = $sslService->getPrivateKey();            
            if($privateKey) {
                $vars['privateKey'] = $privateKey;
            } 
            if ($sslService->status !== 'Awaiting Configuration') {
                try {
                    
                    $orderStatus = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);  
                   
                    if(!empty($orderStatus['partner_order_id'])) {
                        $vars['partner_order_id'] = ($orderStatus['partner_order_id']);
                    }
                    if(!empty($orderStatus['product_id'])) {
                        $apiRepo       = new \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products();
                        $apiProduct    = $apiRepo->getProduct($orderStatus['product_id']);
                        $vars['brand'] = $apiProduct->brand;
                    }
                    if(!empty($orderStatus['approver_method'])) {                        
                        $vars['approver_method'] = ($orderStatus['approver_method']);
                    }
                    
                    $dcv_method = array_keys($vars['approver_method']);
                    if($dcv_method[0] != null) {
                        $vars['dcv_method'] = $dcv_method[0];
                    if($dcv_method[0] == 'http' || $dcv_method[0] == 'https'){
                       $vars['approver_method'][$dcv_method[0]]['content'] = explode(PHP_EOL, $vars['approver_method'][$dcv_method[0]]['content']);
                    }
                    } else {
                        $vars['dcv_method'] = 'email';
                    }
                    //if (!empty($orderStatus['csr_code'])) {
                    //    $vars['csr'] = ($orderStatus['csr_code']);
                    //}

                    if (!empty($orderStatus['crt_code'])) {
                        $vars['crt'] = ($orderStatus['crt_code']);
                    }
                    if (!empty($orderStatus['ca_code'])) {
                        $vars['ca'] = ($orderStatus['ca_code']);
                    }
                    /*if (!empty($orderStatus['order_id'])) {
                        $vars['order_id'] = $orderStatus['order_id'];
                    }*/
                    if (!empty($orderStatus['domain'])) {
                        $vars['domain'] = $orderStatus['domain'];
                    }
                    
                    /*if (!empty($orderStatus['san'])) {
                        foreach ($orderStatus['san'] as $san) {
                            $vars['sans'][] = $san['san_name'];
                        }
                        $vars['sans'] = implode('<br>', $vars['sans']);
                    }*/
                    if (!empty($orderStatus['san'])) {
                        foreach ($orderStatus['san'] as $san) {
                            $vars['sans'][$san['san_name']]['san_name'] = $san['san_name'];
                            $vars['sans'][$san['san_name']]['method'] = $san['validation_method'];
                            switch ($san['validation_method']) {
                                case 'dns':
                                    $vars['sans'][$san['san_name']]['san_validation'] = $san['validation']['dns']['record'];
                                    break;
                                case 'http':
                                    $vars['sans'][$san['san_name']]['san_validation'] = $san['validation']['http'];
                                    $vars['sans'][$san['san_name']]['san_validation']['content'] = explode(PHP_EOL, $san['validation']['http']['content']);
                                    break;
                                case 'https':
                                    $vars['sans'][$san['san_name']]['san_validation'] = $san['validation']['https'];                                    
                                    $vars['sans'][$san['san_name']]['san_validation']['content'] = explode(PHP_EOL, $san['validation']['https']['content']);
                                    break;
                                default:
                                    $vars['sans'][$san['san_name']]['san_validation'] = $san['validation']['email'];
                                    break;
                            }
                        }                        
                    }

                    $vars['activationStatus'] = $orderStatus['status'];
                    
                    //valid from
                    $vars['validFrom'] = $orderStatus['valid_from'];
                    //expires
                    $vars['validTill'] = $orderStatus['valid_till'];                    
                    //service billing cycle                   
                    $vars['serviceBillingCycle'] = $serviceBillingCycle;                    
                    $vars['displayRenewButton'] = false;
                    $today = date('Y-m-d');
                    $diffDays =  abs(strtotime($orderStatus['valid_till']) - strtotime($today))/86400; 

                    if($diffDays < 90)
                        $vars['displayRenewButton'] = true;
                    
                    
                    //get dsiabled validation methods
                    $disabledValidationMethods = array();
                    $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();        
                    if($apiConf->disable_email_validation && !in_array($vars['brand'], $vars['brandsWithOnlyEmailValidation']))
                    {
                        array_push($disabledValidationMethods, 'email');
                    }
                    
                } catch (\Exception $ex) {
                    $vars['error'] = 'Can not load order details';
                }
            } 
            $vars['disabledValidationMethods'] = $disabledValidationMethods;
            $vars['configurationStatus'] = $sslService->status;
            $vars['configurationURL']    = $url;
            $vars['allOk']               = true;
            $vars['assetsURL'] = main\Server::I()->getAssetsURL();
            $vars['serviceid'] = $serviceId;
            $vars['userid'] = $userid;
            
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
            $errorInvoiceExist = false;
            $cron = new \MGModule\GGSSLWHMCS\controllers\addon\admin\Cron();            
            $service = \WHMCS\Service\Service::where('id', $input['id'])->get();            
            $result = $cron->createAutoInvoice(array($input['params']['pid'] => $service), $input['id'], true);
            if(is_array($result) && isset($result['invoiceID']))
            {
                $existInvoiceID = $result['invoiceID'];
                $errorInvoiceExist =  \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('Related invoice already exist.');
            }
        }
        catch(Exception $e)
        {
            return array(
                'error' => $e->getMessage(),
            );   
        }
        if($errorInvoiceExist)
            return array(
                'error' => $errorInvoiceExist,                
                'invoiceID' => $existInvoiceID
            );
            
        return array(
            'success' => true,
            'msg' =>  main\mgLibs\Lang::getInstance()->T('A new invoice has been successfully created. '),
            'invoiceID' => $result
        );        
    }
    
    public function resendValidationEmailJSON($input, $vars = array()) {
        $ssl = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $ssl->getByServiceId($input['id']);
        $response = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->resendValidationEmail($serviceSSL->remoteid);
        
        return array(
            'success' => $response['message']
        );        
    }
    
    public function sendCertificateEmailJSON($input, $vars = array()) {
        $ssl = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $ssl->getByServiceId($input['id']);
        $orderStatus = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($serviceSSL->remoteid);
        
        if($orderStatus['status'] !== 'active') {
            throw new \Exception( \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('orderNotActiveError')); //Can not send certificate. Order status is different than active.
        }
        
        if(empty($orderStatus['ca_code'])) {
            throw new \Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('CACodeEmptyError')); //An error occurred. Certificate body is empty.
        }
        $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();        
        $sendCertyficateTermplate = $apiConf->send_certificate_template;  
       

        if($sendCertyficateTermplate == NULL)
        {            
            $result = sendMessage(\MGModule\GGSSLWHMCS\eServices\EmailTemplateService::SEND_CERTIFICATE_TEMPLATE_ID, $input['id'], [
                'ssl_certyficate' => nl2br($orderStatus['ca_code']),
            ]);
        } 
        else
        {
            $templateName = \MGModule\GGSSLWHMCS\eServices\EmailTemplateService::getTemplateName($sendCertyficateTermplate);
            $result = sendMessage($templateName, $input['id'], [
                'ssl_certyficate' => nl2br($orderStatus['ca_code']),
            ]);
        }  
        if($result === true)
        {
             return array(
                'success' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('sendCertificateSuccess')
            ); 
        }  
        
        throw new \Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T($result));
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
                $response = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeValidationMethod($sslService->remoteid, $data);
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
        
        return array(
            'success' => $response['success'],
            'msg'     => $response['message']
        );
    }
    public function getApprovalEmailsForDomainJSON($input, $vars = array()) {
                
        $domainEmails = [];
        
        if($input['brand'] == 'geotrust') {
            $apiDomainEmails             = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmailsForGeotrust($input['domain']);
            $domainEmails = $apiDomainEmails['GeotrustApprovalEmails'];
        } else {
            $apiDomainEmails             = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($input['domain']);
            $domainEmails = $apiDomainEmails['ComodoApprovalEmails'];
        }    
        $result = [
            'success' => 1,
            'domainEmails' => $domainEmails
        ];
        
        return $result;
    }
    function changeApproverEmailJSON($input, $vars = array()) {
        
        $sslRepo   = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $ssService = $sslRepo->getByServiceId($input['serviceId']);
        
        $data = [
            'approver_email' => $input['newEmail']
        ]; 
        
        $response = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeValidationEmail($ssService->remoteid, $data);          
        
        return array(
            'success' => $response['success'],
            'msg'     => $response['success_message']
        ); 
        
    }
    function getPrivateKeyJSON($input, $vars = array()) {
        $sslRepo   = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
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
    function getPasswordJSON($input, $vars = array()) {
        //do something with input
        unset($input);
        unset($vars);

        return array(
            'password' => 'fuNPassword'
        );

    }

}
