<?php

namespace MGModule\GGSSLWHMCS\eHelpers;

use \MGModule\GGSSLWHMCS as main;

class SSLSummary
{
    private $clientID  = null;
    private $services  = [];
    private $apiOrders = null;
    private $sslRepo = null;

    function __construct($clientID)
    {
        $this->clientID = $clientID;
        $this->sslRepo = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $this->loadClientsSSLServices();
    }
    

    public function getTotalSSLOrdersCount()
    {
        return count($this->services);
    }
    
    public function getTotalSSLOrders()
    {
        return $this->services;
    }

    public function getUnpaidSSLOrdersCount()
    {
        return count($this->getUnpaidSSLOrders());
    }
    
    public function getUnpaidSSLOrders()
    {
        $services = array();
        foreach ($this->services as $service)
        {
            $invoiceID = $service->order()->invoiceid;
            try
            {
                $invoice = new main\models\whmcs\invoices\Invoice($invoiceID);
            }
            catch (\Exception $ex)
            {
                continue;
            }

            if ($invoice->getStatus() == 'Unpaid')
                $services[] = $service;
        }
        
        return $services;
    }
    
    public function getProcessingSSLOrdersCount()
    {       
        return count($this->getProcessingSSLOrders());
    }
    
    public function getProcessingSSLOrders()
    {
        $services = array();
        
        foreach ($this->services as $service)
        {
            if($this->getSSLCertificateStatus($service->id) == 'processing')
            {
                $services[] = $service;
            }
        }
        
        return $services;
    }

    public function getExpiresSoonSSLOrdersCount()
    {
        return count($this->getExpiresSoonSSLOrders());
    }
    
    public function getExpiresSoonSSLOrders()
    {
        $services = array();
        
         $daysBefore = 30;
        $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();        
        $expiresSoonSelectedDays = $apiConf->summary_expires_soon_days;         
        if($expiresSoonSelectedDays != NULL && trim($expiresSoonSelectedDays) != '')
            $daysBefore = $expiresSoonSelectedDays;
        //$daysBefore = 1000; //to test
        foreach ($this->services as $service)
        {
            $SSLOrder = new main\eModels\whmcs\service\SSL();

            $ssl = $SSLOrder->getWhere(array('serviceid' => $service->id, 'userid' => $service->clientID))->first();

            if ($ssl == NULL || $ssl->remoteid == '')
            {
                continue;
            }
            $expiry_date = $this->getSSLCertificateValidTillDate($service->id);
            
            if ($expiry_date != '0000-00-00' && $this->getSSLCertificateStatus($service->id) == 'active')
            {                
                if($this->checkOrderExpireDate($expiry_date, $daysBefore))
                    $services[] = $service;
             } 
        }
        
        return $services;
    }

    private function checkOrderExpireDate($expireDate, $days = 30) {
       
        if (stripos($expireDate, ':') === false) {
            $expireDate .= ' 23:59:59';
        }
        $expire = new \DateTime($expireDate);
        $today = new \DateTime(); //to test ad properly date in format ->2019-06-19 23:59:59.000000
       
        $diff = $expire->diff($today)->format("%a");
        

        if ($diff == 0 || $expire < $today) {
            //if date from past
            return false;
        }
        
        return ($diff <= $days) ? true : false;
    }
    
    
    private function getSSLCertificateValidTillDate($serviceID)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        return $sslService->getConfigdataKey('valid_till');
    }
    
    private function getSSLCertificateStatus($serviceID)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        if($sslService == null)
            return ;

        return $sslService->getConfigdataKey('ssl_status');
    }
    
    private function loadClientsSSLServices()
    {
        $services = new main\models\whmcs\service\Repository();
        $services->onlyClient($this->clientID)->onlyStatus(['Active', 'Suspended', 'Pending']);

        $this->services = [];
        foreach ($services->get() as $service)
        {
            $product = $service->product();
            
            //check if product is GOGET
            if ($product->serverType == 'GGSSLWHMCS')
            {
                $this->services[] = $service;
            }
        }
    }

    private function loadSSLOrdersFromAPI()
    {
        $this->apiOrders = array();
        foreach ($this->services as $service)
        {
            $SSLOrder = new main\eModels\whmcs\service\SSL();

            $ssl = $SSLOrder->getWhere(array('serviceid' => $service->id, 'userid' => $service->clientID))->first();

            if ($ssl == NULL || $ssl->remoteid == '')
            {
                continue;
            }

            //get order details from API
            $this->apiOrders[] = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($ssl->remoteid);
        }
    }
}
