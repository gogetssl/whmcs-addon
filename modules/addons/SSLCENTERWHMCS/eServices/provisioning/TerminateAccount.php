<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class TerminateAccount {

    private $p;

    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        try {
            $this->terminateAccount();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        return 'success';
    }

    private function terminateAccount() {
        $ssl = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $ssl->getByServiceId($this->p['serviceid']);
        
        if (is_null($serviceSSL)) {
            throw new Exception('Create has not been initialized.');
        }
        
        if(empty($serviceSSL->remoteid)) {
            $serviceSSL->delete();
            return;
        }
       
        $reason = $this->getCancellationReason();

        if (\MGModule\SSLCENTERWHMCS\eServices\SmimeService::isSmimeOrder($serviceSSL)) {
            $status = strtolower((string) $serviceSSL->getConfigdataKey('ssl_status'));
            if (!in_array($status, ['cancelled', 'expired', 'failed', 'rejected'], true)) {
                \MGModule\SSLCENTERWHMCS\eServices\SmimeService::cancel($serviceSSL, $reason);
            }
            $serviceSSL->delete();
            return;
        }

        if (\MGModule\SSLCENTERWHMCS\eServices\CodeSigningService::isCodeSigningOrder($serviceSSL)) {
            $status = strtolower((string) $serviceSSL->getConfigdataKey('ssl_status'));
            if (!in_array($status, ['cancelled', 'expired', 'failed', 'rejected'], true)) {
                \MGModule\SSLCENTERWHMCS\eServices\CodeSigningService::cancel($serviceSSL, $reason);
            }
            $serviceSSL->delete();
            return;
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->cancelSSLOrder($serviceSSL->remoteid, $reason);
        $serviceSSL->delete();
    }

    /**
     * Reason sent to GoGetSSL: a cancellation request of the client (Request Cancellation in the client area,
     * processed by the WHMCS cron or the administrator), otherwise the automatic termination of an unpaid service.
     */
    private function getCancellationReason() {
        try {
            $hasRequest = \WHMCS\Database\Capsule::table('tblcancelrequests')
                ->where('relid', (int) $this->p['serviceid'])
                ->exists();
        } catch (\Exception $e) {
            $hasRequest = false;
        }

        return $hasRequest ? 'Order cancelled via WHMCS by the client' : 'Order canceled for non-payment.';
    }
}
