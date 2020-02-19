<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

class UpdateConfigs
{
    private $sslService;
    private $sslRepo = null;
    private $processingOnly = false;
    public function __construct($cids, $processingOnly)
    {
        $this->cids = $cids;
        $this->sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $this->processingOnly = $processingOnly;
    }
    
    public function run()
    {
        try {
            return $this->updateConfigData();
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
        return 'success';
    }
    private function writeNewConfigdata($apiRepo, $order)
    {
        $orderDetails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($order['order_id']);
        $apiProduct = $apiRepo->getProduct($orderDetails['product_id']);
        
        $sslOrder             = \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL::whereRemoteId($order['order_id'])->first();
        $sslOrder->setCa($orderDetails['ca_code']);
        $sslOrder->setCrt($orderDetails['crt_code']);
        $sslOrder->setPartnerOrderId($orderDetails['partner_order_id']);
        
        $sslOrder->setValidFrom($orderDetails['valid_from']);
        $sslOrder->setValidTill($orderDetails['valid_till']);
        
        $sslOrder->setDomain($orderDetails['domain']);
        $sslOrder->setOrderStatusDescription($orderDetails['status_description']);
        
        $sslOrder->setApproverMethod($orderDetails['approver_method']);
        $sslOrder->setDcvMethod($orderDetails['dcv_method']);
        $sslOrder->setProductId($orderDetails['product_id']);
        $sslOrder->setProductBrand($apiProduct->brand);
        $sslOrder->setSanDetails($orderDetails['san']);
        $sslOrder->setConfigdataKey("approveremail", $orderDetails['approver_email']);
        $sslOrder->setConfigdataKey('ssl_status', $order['status']);
        $sslOrder->save();
        logActivity($orderDetails['order_id'].':'.$orderDetails['status']);
    }
    public function updateConfigData()
    {
        if (!isset($this->cids) || empty($this->cids)) {
            return;
        }
        
        $orders = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatuses(['cids' => $this->cids]);
        $apiRepo = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        if (!$this->processingOnly) {
            foreach ($orders['certificates'] as $order)
            {
                $sslService = $this->sslRepo->getByRemoteId((int) $order['order_id']);

                if ($order['status'] === $sslService->getConfigdataKey('ssl_status')) {
                    continue;
                } else {
                    $this->writeNewConfigdata($apiRepo, $order);
                }
            }
        }
        else
        {
            foreach ($orders['certificates'] as $order) {
                $sslService = $this->sslRepo->getByRemoteId((int) $order['order_id']);
                if ($order['status'] === 'active') {
                    $this->writeNewConfigdata($apiRepo, $order);
                }
            }
        }
    }
}