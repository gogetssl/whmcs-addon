<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use WHMCS\Database\Capsule;

class UpdateConfigData
{
    private $sslService;
    private $orderdata;
    
    public function __construct($sslService, $orderdata = array())
    {
        $this->sslService = $sslService;
        $this->orderdata = $orderdata;
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

        if(empty($this->orderdata))
        {
            $order = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($this->sslService->remoteid);
        }
        else
        {
            $order = $this->orderdata;
        }
           
        $apiRepo = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        
        if(!isset($this->sslService->configdata->product_brand) || empty($this->sslService->configdata->product_brand))
        {
            $checkTable = Capsule::schema()->hasTable('mgfw_SSLCENTER_product_brand');
            
            $brandName = null;
            if($checkTable !== false)
            {
                $productData = Capsule::table('mgfw_SSLCENTER_product_brand')->where('pid', $order['product_id'])->first();
                
                if(isset($productData->brand) && !empty($productData->brand))
                {
                    $brandName = $productData->brand;
                }
                
            }
            
            if($brandName === null)
            {
                $apiProduct = $apiRepo->getProduct($order['product_id']);
                $apiProduct->brand = $brandName;
            }
        }

//        if (($order['status'] != 'expired') && ($order['status'] != 'cancelled'))
//        {
            $sslOrder = $this->sslService;

            $sslOrder->setCa($order['ca_code']);
            $sslOrder->setCrt($order['crt_code']);    
            $sslOrder->setPartnerOrderId($order['partner_order_id']);

            $sslOrder->setValidFrom($order['valid_from']);
            $sslOrder->setValidTill($order['valid_till']);

            $sslOrder->setSubscriptionStarts($order['begin_date']);
            $sslOrder->setSubscriptionEnds($order['end_date']);

            $sslOrder->setDomain($order['domain']);
            $sslOrder->setSSLStatus($order['status']);
            $sslOrder->setOrderStatusDescription($order['status_description']);

            $sslOrder->setApproverMethod($order['approver_method']);
            $sslOrder->setDcvMethod($order['dcv_method']);
            $sslOrder->setProductId($order['product_id']);
            $sslOrder->setSSLTotalDomains($order['total_domains']);
            
            if(!isset($this->sslService->configdata->product_brand) || empty($this->sslService->configdata->product_brand))
            {
                $sslOrder->setProductBrand($brandName);
            }
            
            $sslOrder->setSanDetails($order['san']);
            $sslOrder->setConfigdataKey("approveremail", $order['approver_email']);

            if (!empty($order['csr_code']) && empty($sslOrder->getCsr())) {
                $sslOrder->setCsr($order['csr_code']);
            }

            if (empty($sslOrder->getConfigdataKey('configssl')) && !empty($sslOrder->getConfigdataKey('install_type'))) {
                $sslOrder->setConfigdataKey('configssl', 1);
            }

            // Backfill is_wildcard: if not set but order has wildcard SANs, mark as wildcard
            if (empty($sslOrder->getConfigdataKey('is_wildcard'))) {
                $hasSanWildcard = false;
                if (!empty($order['san']) && is_array($order['san'])) {
                    foreach ($order['san'] as $san) {
                        $sanName = is_array($san) ? ($san['san_name'] ?? '') : (is_object($san) ? ($san->san_name ?? '') : '');
                        if (strpos((string) $sanName, '*.') === 0) {
                            $hasSanWildcard = true;
                            break;
                        }
                    }
                }
                $mainDomain = (string) $sslOrder->getDomain();
                if ($hasSanWildcard || strpos($mainDomain, '*.') === 0) {
                    $sslOrder->setConfigdataKey('is_wildcard', 1);
                }
            }

            $sslOrder->save();
       // }

        return $order;
    }
}