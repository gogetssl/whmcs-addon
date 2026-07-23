<?php

use WHMCS\Database\Capsule as DB;
use MGModule\SSLCENTERWHMCS\models\logs\Repository as LogsRepo;
use MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService;

define('DS', DIRECTORY_SEPARATOR);
define('WHMCS_MAIN_DIR', substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), 'modules' . DS . 'addons')));
define('ADDON_DIR', substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), DS . 'cron')));

require_once WHMCS_MAIN_DIR . DS . 'init.php';
require_once ADDON_DIR . DS . 'Loader.php';
new \MGModule\SSLCENTERWHMCS\Loader();

$logsRepo = new LogsRepo();
$logsRepo->createLogsTable();

$apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
$syncDates = isset($apiConf->automatic_processing_of_renewal_orders) && $apiConf->automatic_processing_of_renewal_orders == '1';

// Find all SSL services with an API remote order ID and an Active WHMCS service
$activeOrders = DB::table('tblsslorders')
    ->join('tblhosting', 'tblhosting.id', '=', 'tblsslorders.serviceid')
    ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
    ->select([
        'tblsslorders.remoteid',
        'tblsslorders.serviceid',
        'tblsslorders.id as ssl_order_id',
        'tblhosting.userid',
        'tblhosting.domain',
        'tblhosting.domainstatus',
    ])
    ->where('tblproducts.servertype', 'SSLCENTERWHMCS')
    ->whereIn('tblhosting.domainstatus', ['Active'])
    ->whereNotNull('tblsslorders.remoteid')
    ->where('tblsslorders.remoteid', '!=', '')
    ->where('tblsslorders.remoteid', '!=', '0')
    ->get();

if (empty($activeOrders) || count($activeOrders) === 0) {
    echo 'No active SSL orders to check.' . PHP_EOL;
    die();
}

echo 'Checking ' . count($activeOrders) . ' active SSL order(s)...' . PHP_EOL;

foreach ($activeOrders as $order) {
    $apiOrderId = (int) $order->remoteid;
    $serviceId  = (int) $order->serviceid;
    $clientId   = (int) $order->userid;
    $domain     = $order->domain;

    try {
        $orderStatus = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()
            ->getApi()
            ->getOrderStatus($apiOrderId);

        $apiStatus = strtolower(isset($orderStatus['status']) ? $orderStatus['status'] : '');
        $validTill = isset($orderStatus['valid_till']) ? trim($orderStatus['valid_till']) : '';

        // Detect expiry: explicit API status or valid_till in the past for an active cert
        $isExpired = $apiStatus === 'expired'
            || (in_array($apiStatus, ['active', 'valid', 'issued']) && $validTill && strtotime($validTill) < time());

        if (in_array($apiStatus, ['cancelled', 'revoked', 'fraud'])) {
            $logsRepo->addLog($clientId, $serviceId, 'error',
                '[ActiveCheck] SSL order #' . $apiOrderId . ' for domain ' . $domain .
                ' — status changed to: ' . strtoupper($apiStatus) . '. Service will be suspended.');

            DB::table('tblhosting')->where('id', $serviceId)->update([
                'domainstatus' => 'Suspended',
            ]);

            DB::table('SSLCENTER_orders')->where('service_id', $serviceId)->update([
                'status' => ucfirst($apiStatus),
            ]);

            logActivity('SSL Certificate ' . strtoupper($apiStatus) . ' — Service #' . $serviceId .
                ' domain: ' . $domain . ' (API order #' . $apiOrderId . ')');

            if ($apiStatus === 'revoked') {
                $serialNumber = isset($orderStatus['crt_code'])
                    ? openssl_x509_parse($orderStatus['crt_code'])['serialNumberHex'] ?? ''
                    : '';
                $sent = EmailTemplateService::sendCertRevokedEmail(
                    $serviceId, $domain, $serialNumber, (string) $apiOrderId
                );
                $logsRepo->addLog($clientId, $serviceId, 'info',
                    '[ActiveCheck] Revocation notification ' . ($sent ? 'sent to client.' : 'FAILED to send.'));
            }

            echo '[ALERT] Service #' . $serviceId . ' (' . $domain . ') is ' . strtoupper($apiStatus) . PHP_EOL;

        } elseif ($isExpired) {
            $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
            $sslConfig = $sslRepo->getByServiceId($serviceId);

            // Send expiry notification only once (guard via configdata flag)
            $alreadyNotified = $sslConfig && $sslConfig->getConfigdataKey('expiry_notified');

            if (!$alreadyNotified) {
                $sent = EmailTemplateService::sendCertExpiredEmail($serviceId, $domain, $validTill);

                if ($sslConfig) {
                    $sslConfig->setConfigdataKey('expiry_notified', true);
                    $sslConfig->save();
                }

                $logsRepo->addLog($clientId, $serviceId, 'error',
                    '[ActiveCheck] SSL certificate for domain ' . $domain . ' has expired'
                    . ($validTill ? ' (expired: ' . $validTill . ')' : '')
                    . '. Expiry notification ' . ($sent ? 'sent to client.' : 'FAILED to send.'));

                logActivity('SSL Certificate EXPIRED — Service #' . $serviceId .
                    ' domain: ' . $domain . ' (API order #' . $apiOrderId . ')');
            }

            DB::table('SSLCENTER_orders')->where('service_id', $serviceId)->update([
                'status' => 'Expired',
            ]);

            echo '[EXPIRED] Service #' . $serviceId . ' (' . $domain . ') — expired: ' . $validTill . PHP_EOL;

        } elseif (in_array($apiStatus, ['active', 'valid', 'issued'])) {
            $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
            $sslConfig = $sslRepo->getByServiceId($serviceId);

            // Reset expiry_notified flag if cert was reissued and is valid again
            if ($sslConfig && $sslConfig->getConfigdataKey('expiry_notified')) {
                $sslConfig->setConfigdataKey('expiry_notified', false);
                $sslConfig->save();
            }

            // Sync next due / next invoice dates with end_date from API
            if ($syncDates && $sslConfig) {
                $endDate = (string) $sslConfig->getConfigdataKey('end_date');
                if ($endDate && $endDate !== '0000-00-00') {
                    $autoRenewInvoice = !empty($apiConf->auto_renew_invoice_subscription);
                    $renewInvoiceDays = $autoRenewInvoice ? max(0, (int) $apiConf->renew_invoice_days_subscription) : 0;

                    $dueFormatted = ($autoRenewInvoice && $renewInvoiceDays > 0)
                        ? date('Y-m-d', strtotime($endDate . ' -' . $renewInvoiceDays . ' days'))
                        : date('Y-m-d', strtotime($endDate));

                    DB::table('tblhosting')->where('id', $serviceId)->update([
                        'nextduedate'     => $dueFormatted,
                        'nextinvoicedate' => $dueFormatted,
                    ]);

                    $logsRepo->addLog($clientId, $serviceId, 'info',
                        '[ActiveCheck] Synced dates for service #' . $serviceId
                        . ' — nextduedate/nextinvoicedate: ' . $dueFormatted
                        . ($renewInvoiceDays > 0 ? ' (' . $renewInvoiceDays . ' days before subscription end ' . $endDate . ')' : ''));
                }
            }

            echo '[OK] Service #' . $serviceId . ' (' . $domain . ') — status: ' . $apiStatus . PHP_EOL;

        } else {
            $logsRepo->addLog($clientId, $serviceId, 'info',
                '[ActiveCheck] SSL order #' . $apiOrderId . ' for domain ' . $domain .
                ' — current status: ' . strtoupper($apiStatus));
            echo '[INFO] Service #' . $serviceId . ' (' . $domain . ') — status: ' . $apiStatus . PHP_EOL;
        }
    } catch (\Exception $e) {
        $logsRepo->addLog($clientId, $serviceId, 'error',
            '[ActiveCheck] Failed to check order #' . $apiOrderId . ' for domain ' . $domain .
            ': ' . $e->getMessage());
        echo '[ERROR] Service #' . $serviceId . ' (' . $domain . '): ' . $e->getMessage() . PHP_EOL;
    }
}

echo 'Done.' . PHP_EOL;
die();
