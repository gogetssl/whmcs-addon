<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

class UpdateConfigData
{
    private $sslService;
    
    public function __construct($sslService)
    {
        $this->sslService = $sslService;
    }
    
    public function run() {
        try {
            return $this->updateConfigData();
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
        return 'success';
    }
    
    public function updateConfigData()
    {
        if(!isset($this->sslService->remoteid) || empty($this->sslService->remoteid))
        {
            return;
        }

        $order = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($this->sslService->remoteid);
      
        $apiRepo = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $apiProduct = $apiRepo->getProduct($order['product_id']);

        if (($order['status'] != 'expired') && ($order['status'] != 'cancelled'))
        {
            $sslOrder = $this->sslService;

            $sslOrder->setCa($order['ca_code']);
            $sslOrder->setCrt($order['crt_code']);    
            $sslOrder->setPartnerOrderId($order['partner_order_id']);

            $sslOrder->setValidFrom($order['valid_from']);
            $sslOrder->setValidTill($order['valid_till']);

            $sslOrder->setDomain($order['domain']);
            $sslOrder->setOrderStatusDescription($order['status_description']);

            $sslOrder->setApproverMethod($order['approver_method']);
            $sslOrder->setDcvMethod($order['dcv_method']);
            $sslOrder->setProductId($order['product_id']);
            $sslOrder->setProductBrand($apiProduct->brand);
            $sslOrder->setSanDetails($order['san']);
            $sslOrder->setConfigdataKey("approveremail", $order['approver_email']);

            $sslOrder->save();
        }

        return $order;
    }
}