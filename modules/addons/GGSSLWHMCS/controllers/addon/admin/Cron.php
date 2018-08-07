<?php

namespace MGModule\GGSSLWHMCS\controllers\addon\admin;

use \MGModule\GGSSLWHMCS as main;

class Cron extends main\mgLibs\process\AbstractController
{
    private $sslRepo = null;

    public function indexCRON($input, $vars = array())
    {

        $updatedServices = [];

        $this->sslRepo = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        
        //get all completed ssl orders
        $sslOrders = $this->getSSLOrders();        
        foreach ($sslOrders as $sslService)
            { 
            $serviceID = $sslService->serviceid;            
            
            $order = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid); 
            
            if ($order['status'] == 'expired')
            {
                $this->setSSLServiceAsTerminated($serviceID);
                $updatedServices[] = $serviceID;
            }
            //if service is montlhy, one time, free skip it
            if($this->checkServiceBillingPeriod($serviceID)) continue; 
            
            //if service is synchronized skip it
            if ($this->checkIfSynchronized($serviceID)) continue;                
            //if certificate is active
            
            if ($order['status'] == 'active')
            {
                //update whmcs service next due date
                $newNextDueDate = $order['valid_till'];
                $this->updateServiceNextDueDate($serviceID, $newNextDueDate);

                //set ssl certificate as synchronized
                $this->setSSLServiceAsSynchronized($serviceID);
                
                //set ssl certificate as terminated if expired  
                if(strtotime($order['valid_till']) < strtotime(date('Y-m-d')))
                {
                     $this->setSSLServiceAsTerminated($serviceID);
                }

                $updatedServices[] = $serviceID;
            }
            
        }
        echo 'Synchronization completed.';
        echo '<br />Number of synchronized services: ' . count($updatedServices);
        
        logActivity("GGSSL WHMCS: Synchronization completed. Number of synchronized services: " . count($updatedServices));
        
        return array();
    }
    
    public function notifyCRON($input, $vars = array())
    {
        //get renewal settings
        $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get(); 
        $auto_renew_invoice_one_time = (bool)$apiConf->auto_renew_invoice_one_time;
        $auto_renew_invoice_reccuring = (bool)$apiConf->auto_renew_invoice_reccuring;
        //get saved amount days to generate invoice (one time & reccuring)
        $renew_invoice_days_one_time = $apiConf->renew_invoice_days_one_time;
        $renew_invoice_days_reccuring = $apiConf->renew_invoice_days_reccuring;
        
        $send_expiration_notification_reccuring = (bool)$apiConf->send_expiration_notification_reccuring;
        $send_expiration_notification_one_time = (bool)$apiConf->send_expiration_notification_one_time;
        
        $this->sslRepo = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        
        //get all completed ssl orders
        $sslOrders = $this->getSSLOrders(); 
        $synchServicesId = array_map(
                function($row) 
                { 
                    $config = json_decode($row->configdata);                     
                    if (isset($config->synchronized)) 
                    { 
                        return $row->serviceid;                    
                    }   
                    else
                    {
                        return \WHMCS\Service\Service::where('id', $row->serviceid)->where('billingcycle', 'One Time')->first()['id']; 
                    }
                }, $sslOrders);
               
        $services        = \WHMCS\Service\Service::whereIn('id', $synchServicesId)->get();        
        
        $emailSendsCount = 0;
        
        $packageLists = [];
        $serviceIDs = [];
       
        foreach ($services as $srv)
        {     
            //get days left to expire from WHMCS              
            $daysLeft = $this->checkOrderExpireDate($srv->nextduedate);            
            //if service is One Time and nextduedate is setted as 0000-00-00 get valid_till from GoGet API
            if($srv->billingcycle == 'One Time' && $srv->nextduedate = '0000-00-00')
            { 
                $sslOrder = $this->getSSLOrders($srv->id)[0];
                $order = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslOrder->remoteid);                 
                $daysLeft = $this->checkOrderExpireDate($order['valid_till']);                     
            }
            
            //service was synchronized, so we can base on nextduedate, that should be the same as valid_till
            if ( $daysLeft >= 0) {
                if($srv->billingcycle == 'One Time' && $send_expiration_notification_one_time || $srv->billingcycle != 'One Time' && $send_expiration_notification_reccuring)
                    $emailSendsCount += $this->sendExpireNotfiyEmail($srv->id, $daysLeft);
            }
            
            $savedRenewDays = $renew_invoice_days_reccuring;
            if($srv->billingcycle == 'One Time')
            {
                $savedRenewDays = $renew_invoice_days_one_time;
            }                   
            //if it is proper amount of days before expiry, we create invoice
            if ($daysLeft == (int)$savedRenewDays) {
                if($srv->billingcycle == 'One Time' && $auto_renew_invoice_one_time || $srv->billingcycle != 'One Time' && $auto_renew_invoice_reccuring)
                { 
                    $packageLists[$srv->packageid][] = $srv;
                    $serviceIDs[] = $srv->id;                    
                }
               
            }            
        }  
        $invoicesCreatedCount = $this->createAutoInvoice($packageLists, $serviceIDs);
        
        echo 'Notifier completed.' . PHP_EOL;
        echo '<br />Number of emails send: ' . $emailSendsCount . PHP_EOL;
        echo '<br />Number of invoiced created: ' . $invoicesCreatedCount . PHP_EOL;
        
        logActivity("GGSSL WHMCS: Notifier completed. Number of emails send: " . $emailSendsCount);
        logActivity("GGSSL WHMCS: Notifier completed. Number of invoiced created: " . $invoicesCreatedCount);
        
        return array();
    }
    
    public function certificateSendCRON($input, $vars = array())
    {
        echo 'Certificate Sender started.' . PHP_EOL;
        logActivity("GGSSL WHMCS: Certificate Sender started.");
        
        $emailSendsCount = 0;
        $this->sslRepo = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        
        $services = new main\models\whmcs\service\Repository();
        $services->onlyStatus(['Active']);

        $servicesArray = [];
        foreach ($services->get() as $service)
        {
            $apiOrders = null;
            $product = $service->product();
            //check if product is GOGET
            if ($product->serverType != 'GGSSLWHMCS')
            {
                continue;
            }
            
            $SSLOrder = new main\eModels\whmcs\service\SSL();
            $ssl = $SSLOrder->getWhere(array('serviceid' => $service->id, 'userid' => $service->clientID))->first();
            
            if ($ssl == NULL || $ssl->remoteid == '')
            {
                continue;
            }
            $apiOrder = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($ssl->remoteid);
            if($apiOrder['status'] !== 'active' || empty($apiOrder['ca_code'])) {
                continue;
            }
            
            if($this->checkIfCertificateSent($service->id)) continue;
            
            $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();        
            $sendCertyficateTermplate = $apiConf->send_certificate_template;  
            if($sendCertyficateTermplate == NULL)
            {            
                sendMessage(\MGModule\GGSSLWHMCS\eServices\EmailTemplateService::SEND_CERTIFICATE_TEMPLATE_ID, $service->id, [
                    'ssl_certyficate' => nl2br($apiOrder['ca_code']),
                ]);
            } 
            else
            {
                $templateName = \MGModule\GGSSLWHMCS\eServices\EmailTemplateService::getTemplateName($sendCertyficateTermplate);
                sendMessage($templateName, $service->id, [
                    'ssl_certyficate' => nl2br($apiOrder['ca_code']),
                ]);
            } 
            $this->setSSLCertificateAsSent($service->id);
            $emailSendsCount++;
        }

        echo 'Certificate Sender completed.' . PHP_EOL;
        echo '<br />The number of messages sent: ' . $emailSendsCount . PHP_EOL;
        
        logActivity("GGSSL WHMCS: Certificate Sender completed. The number of messages sent: " . $emailSendsCount);
        return array();
    }
    
    public function loadCertificateStatsCRON($input, $vars = array())
    {
        echo 'Certificate Stats Loader started.' . PHP_EOL;
        logActivity("GGSSL WHMCS: Certificate Stats Loader started.");
        
        $emailSendsCount = 0;
        $this->sslRepo = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        
        $services = new main\models\whmcs\service\Repository();
        $services->onlyStatus(['Active', 'Suspended']);

        $servicesArray = [];
        foreach ($services->get() as $service)
        {
            $apiOrders = null;
            $product = $service->product();
            //check if product is GOGET
            if ($product->serverType != 'GGSSLWHMCS')
            {
                continue;
            }
            
            $SSLOrder = new main\eModels\whmcs\service\SSL();
            $ssl = $SSLOrder->getWhere(array('serviceid' => $service->id, 'userid' => $service->clientID))->first();
            
            if ($ssl == NULL || $ssl->remoteid == '')
            {
                continue;
            }
            $apiOrder = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($ssl->remoteid);
                        
            $this->setSSLCertificateValidTillDate($service->id, $apiOrder['valid_till']);
            $this->setSSLCertificateStatus($service->id, $apiOrder['status']);        
            
        }
        echo '<br/ >'; 
        echo 'Certificate Stats Loader completed.' . PHP_EOL; 
        logActivity("GGSSL WHMCS: Certificate Stats Loader completed.");
        return array();
    }

    private function getSSLOrders($serviceID = null)
    {
        $where = [
            'status' => 'Completed',
            'module' => 'GGSSLWHMCS'
        ];
        
        if($serviceID != NULL)
            $where['serviceid'] = $serviceID;

        return $this->sslRepo->getBy($where);
    }

    private function updateServiceNextDueDate($serviceID, $date)
    {
        $service              = \WHMCS\Service\Service::find($serviceID);
        if(!empty($service))
        {
            $service->nextduedate = $date;
            $service->save();
        }
    }

    private function setSSLServiceAsSynchronized($serviceID)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('synchronized', true);
        $sslService->save();
    }

    private function setSSLServiceAsTerminated($serviceID)
    {
        $service         = \WHMCS\Service\Service::find($serviceID);
        if(!empty($service))
        {
            $service->status = 'terminated';
            $service->save();
        }
    }
    
    private function checkIfSynchronized($serviceID)
    {
        $result     = false;
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        if ($sslService->getConfigdataKey('synchronized'))
        {
            $result = true;
        }

        return $result;
    }
    
    private function checkIfCertificateSent($serviceID)
    {
        $result     = false;
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        if ($sslService->getConfigdataKey('certificateSent'))
        {
            $result = true;
        }

        return $result;
    }
    
    private function setSSLCertificateAsSent($serviceID)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('certificateSent', true);
        $sslService->save();
    }
    
    private function setSSLCertificateValidTillDate($serviceID, $date)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('valid_till', $date);
        $sslService->save();
    }
         
    private function setSSLCertificateStatus($serviceID, $status)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('ssl_status', $status);
        $sslService->save();
    }
    
    private function checkServiceBillingPeriod($serviceID)
    {
        $skipPeriods = ['Monthly', 'One Time', 'Free Account'];
        $skip = false;
        $service = \WHMCS\Service\Service::find($serviceID);
       
        if(in_array($service->billingcycle, $skipPeriods) || $service == null)
        {
            $skip = true;
        }
        
        return $skip;
    }
    
    public function checkOrderExpireDate($expireDate) {
        $expireDaysNotify = array_flip(array('90', '60', '30', '15', '10', '7', '3', '1', '0'));
       
        if (stripos($expireDate, ':') === false) {
            $expireDate .= ' 23:59:59';
        }
        $expire = new \DateTime($expireDate);
        $today = new \DateTime();
        
        $diff = $expire->diff($today, false);
        if ($diff->invert == 0) {
            //if date from past
            return -1;
        }
        
        return isset($expireDaysNotify[$diff->days]) ? $diff->days : -1;
    }
    
    public function sendExpireNotfiyEmail($serviceId, $daysLeft) {
        $command = 'SendEmail';
        
        $postData = array(
            'id' => $serviceId,
            'messagename' => main\eServices\EmailTemplateService::EXPIRATION_TEMPLATE_ID,
            'customvars' => base64_encode(serialize(array("expireDaysLeft" => $daysLeft) )),
        );
        
        $adminUserName = main\eHelpers\Admin::getAdminUserName();

        $results = localAPI($command, $postData, $adminUserName);
        
        $resultSuccess = $results['result'] == 'success';
        if (!$resultSuccess) {
            logActivity('GGSSL WHMCS Notifier: Error while sending customer notifications (service ' . $serviceId . '): ' . $results['message'], 0);
        }
        return $resultSuccess;
    }

    
    public function createAutoInvoice($packages, $serviceIds, $jsonAction = false) {
        if (empty($packages)) {
            return 0;
        }        
        
        $products    = \WHMCS\Product\Product::whereIn('id', array_keys($packages) )->get();        
        $invoiceGenerator = new main\eHelpers\Invoice();        
        $servicesAlreadyAdded = $invoiceGenerator->checkInvoiceAlreadyCreated($serviceIds);
        $getInvoiceID = false;
        if($jsonAction){
            $getInvoiceID = true;
        } 
        $invoiceCounter = 0;
        foreach ($products as $prod) {
            
            foreach ($packages[$prod->id] as $service) {
                //have product, service                
                if (isset($servicesAlreadyAdded[$service->id])) {
                    if($jsonAction)     
                    {
                        return array('invoiceID' => $invoiceGenerator->getLatestCreatedInvoiceInfo($service->id)['invoice_id']);
                    }
                    continue;
                }
                $invoiceCounter += $invoiceGenerator->createInvoice($service, $prod, $getInvoiceID);
            }
        }
        
        return $invoiceCounter;
    }
    
}
