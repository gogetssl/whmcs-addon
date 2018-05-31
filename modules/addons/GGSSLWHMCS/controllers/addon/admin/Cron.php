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
            
            //if service is montlhy, one time, free skip it
            if($this->checkServiceBillingPeriod($serviceID)) continue;       
            
            //if service is synchronized skip it
            if ($this->checkIfSynchronized($serviceID)) continue;                
            
            $order = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);            
            //if certificate is active
            if ($order['status'] == 'active')
            {
                //update whmcs service next due date
                $newNextDueDate = $order['valid_till'];
                $this->updateServiceNextDueDate($serviceID, $newNextDueDate);

                //set ssl certificate as synchronized
                $this->setSSLServiceAsSynchronized($serviceID);

                $updatedServices[] = $serviceID;
            }
        }
        echo 'Synchronization completed.';
        echo '<br />Number of synchronized services: ' . count($updatedServices);
        
        logActivity("GGSSL WHMCS: Synchronization completed. Number of synchronized services: " . count($updatedServices));
        
        return array();
    }

    private function getSSLOrders()
    {
        $where = [
            'status' => 'Completed'
        ];

        return $this->sslRepo->getBy($where);
    }

    private function updateServiceNextDueDate($serviceID, $date)
    {
        $service              = \WHMCS\Service\Service::findOrFail($serviceID);
        $service->nextduedate = $date;
        $service->save();
    }

    private function setSSLServiceAsSynchronized($serviceID)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('synchronized', true);
        $sslService->save();
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
    private function checkServiceBillingPeriod($serviceID)
    {
        $skipPeriods = ['Monthly', 'One Time', 'Free Account'];
        $skip = false;
        
        $service              = \WHMCS\Service\Service::findOrFail($serviceID);
        if(in_array($service->billingcycle, $skipPeriods))
        {
            $skip = true;
        }
        
        return $skip;
    }
}
