<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use MGModule\SSLCENTERWHMCS as main;

class ClientRecheckCertificateDetails extends Ajax
{
    private $parameters;

    function __construct(&$params) 
    {
        $this->parameters = &$params;
    }
    
    public function run()
    {
        try
        {
            return $this->getCertificateDetails();
        }
        catch (\Exception $ex)
        {
            return json_encode(
                [
                    'success' => 0,
                    'msg'     => main\mgLibs\Lang::getInstance()->T($ex->getMessage()),
                ]
            );
        }
    }
    
    public function getCertificateDetails()
    {
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

        $return['configuration_status'] = $sslService->status;
        $return['order_status'] = ucfirst($orderStatus['status']);  
        
        if($orderStatus['status'] == "active")
        {
            $return['valid_from'] = $orderStatus['valid_from'];
            $return['valid_till'] = $orderStatus['valid_till'];
        }
        
        $return['domain'] = $orderStatus['domain'];
        $return['partner_order_id'] = $orderStatus['partner_order_id']?:"-"; 
        $return['crt'] = $orderStatus['crt_code'];  
        $return['ca'] = $orderStatus['ca_code'];  
        $return['csr'] = $orderStatus['csr_code'];  

        foreach ($orderStatus['san'] as $san) {
            $return['sans'][$san['san_name']]['method'] = $san['validation_method'];
            switch ($san['validation_method']) {
                case 'dns':
                    $return['sans'][$san['san_name']]['san_validation'] = $san['validation']['dns']['record'];
                    break;
                case 'http':
                    $return['sans'][$san['san_name']]['san_validation'] = $san['validation']['http'];
                    $return['sans'][$san['san_name']]['san_validation']['content'] = explode(PHP_EOL, $san['validation']['http']['content']);
                    break;
                case 'https':
                    $return['sans'][$san['san_name']]['san_validation'] = $san['validation']['https'];                                    
                    $return['sans'][$san['san_name']]['san_validation']['content'] = explode(PHP_EOL, $san['validation']['https']['content']);
                    break;
                default:
                    $return['sans'][$san['san_name']]['san_validation'] = $san['validation']['email'];
                    break;
            }
        }  
        
        if(!empty($orderStatus['approver_method'])) {                        
            $return['approver_method'] = $orderStatus['approver_method'];
        }
        else
        {
            $return['approver_method'] = $orderStatus['approver_email'];
        }

        $dcv_method = array_keys($return['approver_method']);

        if($dcv_method[0] != null) 
        {
            $return['dcv_method'] = $dcv_method[0];
            if($dcv_method[0] == 'http' || $dcv_method[0] == 'https'){
               $return['approver_method'][$dcv_method[0]] = $return['approver_method'][$dcv_method[0]];
               $return['approver_method'][$dcv_method[0]]['content'] = explode(PHP_EOL, $return['approver_method'][$dcv_method[0]]['content']);
            }
        } 
        else 
            {
            $return['dcv_method'] = 'email';
        }

        $this->response(true, 'Details', $return);  
    }
}
