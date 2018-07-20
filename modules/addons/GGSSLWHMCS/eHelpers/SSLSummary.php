<?php

namespace MGModule\GGSSLWHMCS\eHelpers;

use \MGModule\GGSSLWHMCS as main;

class SSLSummary
{
    private $clientID  = null;
    private $services  = [];
    private $apiOrders = null;

    function __construct($clientID)
    {
        $this->clientID = $clientID;
        $this->loadClientsSSLServices();
    }

    public function getTotalSSLOrders()
    {
        return count($this->services);
    }

    public function getUnpaidSSLOrders()
    {
        $unpaid = 0;
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
                $unpaid++;
        }

        return $unpaid;
    }

    public function getProcessingSSLOrders()
    {
        $processing = 0;
        if ($this->apiOrders == NULL)
            $this->loadSSLOrdersFromAPI();
        foreach ($this->apiOrders as $order)
        {
            if ($order['status'] == 'processing')
                $processing++;
        }

        return $processing;
    }

    public function getExpiresSoonSSLOrders()
    {
        $daysBefore = 30;
        $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();        
        $expiresSoonSelectedDays = $apiConf->summary_expires_soon_days;         
        if($expiresSoonSelectedDays != NULL && trim($expiresSoonSelectedDays) != '')
            $daysBefore = $expiresSoonSelectedDays;
        //$daysBefore = 1000; to test
        $expiresSoon = 0;
        if ($this->apiOrders == NULL)
            $this->loadSSLOrdersFromAPI();
        foreach ($this->apiOrders as $order)
        {
            if ($order['status'] == 'active')
            {
                if($this->checkOrderExpireDate($order['valid_till'], $daysBefore))
                    $expiresSoon++;
             } 
        }
        return $expiresSoon;
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
