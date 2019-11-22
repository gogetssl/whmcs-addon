<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class AdminRecheckCertificateDetails extends Ajax 
{
    private $parameters;

    function __construct(&$params) 
    {
        $this->parameters = &$params;
    }

    public function run() {
        try {
            $this->getCertificateDetails();
        } catch (Exception $ex) {
            $this->response(false, $ex->getMessage());
        }
    }

    private function getCertificateDetails() {
        $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($this->parameters['serviceId']);

        if (is_null($sslService)) {
            throw new Exception('Create has not been initialized');
        }

        if ($this->parameters['userID'] != $sslService->userid) {
            throw new Exception('An error occurred');
        }
   
        $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService);
        $orderStatus = $configDataUpdate->run();

        $return = [];

        $return['SSLCenter API Order ID'] = $orderStatus['order_id'];
        $return['Comodo Order ID'] = $orderStatus['partner_order_id']?:"-";
        $return['Configuration Status'] = $sslService->status;
        $return['Domain'] = $orderStatus['domain'];
        $return['Order Status'] = ucfirst($orderStatus['status']);       
        $return['Order Status Description'] = $orderStatus['status_description']?:"-";
        
        if($orderStatus['status'] == "active")
        {
            $return['Valid From'] = $orderStatus['valid_from'];
            $return['Expires'] = $orderStatus['valid_till'];
        }
        
        foreach ($orderStatus['san'] as $key => $san) {
            $return['SAN ' . ($key + 1)] = sprintf('%s / %s', $san['san_name'], $san['status_description']);
        }
            
        $this->response(true, 'Details', $return);   
    }
}