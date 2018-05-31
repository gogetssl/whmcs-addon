<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class AdminServicesTabFields {

    private $p;

    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        try {
            return $this->adminServicesTabFields();
        } catch (Exception $ex) {
            return [];
        }
        return [];
    }

    private function adminServicesTabFields() {
        $return = [];
        $return['JS/HTML'] = \MGModule\GGSSLWHMCS\eServices\ScriptService::getAdminServiceScript($this->getServiceVars());
        return array_merge($return, $this->getCertificateDetails());
    }
    
    private function getCertificateDetails() {
        try {
            $ssl        = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
            $sslService = $ssl->getByServiceId($this->p['serviceid']);
            if (is_null($sslService)) {
                throw new Exception('Create has not been initialized');
            }
            
            if ($sslService->status === 'Awaiting Configuration') {
                return ['Configuration Status' => 'Awaiting Configuration'];
            }
            
            if(empty($sslService->remoteid)) {
                throw new Exception('Order id not exist');
            }
            
            $return = [];
            $return['GoGetSSL API Order ID'] = $sslService->remoteid;
            $return['Configuration Status'] = $sslService->status;
            
            $orderStatus = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
            
            $return['Domain'] = $orderStatus['domain'];
            $return['Order Status'] = ucfirst($orderStatus['status']);
            $return['Order Status Description'] = $orderStatus['status_description'] ? $orderStatus['status_description'] : '-';
            
            foreach ($orderStatus['san'] as $key => $san) {
                $return['SAN ' . ($key + 1)] = sprintf('%s / %s', $san['san_name'], $san['status_description']);
            }
            
            return $return;
            
        } catch (Exception $ex) {
            return ['GoGetSSL Error' => $ex->getMessage()];
        }
    }

    private function getServiceVars() {
        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = (int) $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        $sansLimit = $includedSans + $boughtSans;
        
        return [
            'serviceid' => $this->p['serviceid'],
            'userid'    => $this->p['userid'],
            'sansLimit' => $sansLimit,
        ];
    }
}
