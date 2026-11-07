<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use MGModule\SSLCENTERWHMCS\models\cronQueue\Repository as CronQueueRepo;
use MGModule\SSLCENTERWHMCS\models\logs\Repository as LogsRepo;

/**
 * Automatic cPanel installation after a manual reissue (client area or admin area).
 *
 * New orders, renewals and the automatic reissue queue a "validate" task: the cron
 * uploads the HTTP/HTTPS validation file to cPanel, waits for the certificate and
 * queues its installation. A reissue started by hand did not, so the validation file
 * was never uploaded and the reissue could not be validated.
 */
class ReissueValidation
{
    /**
     * Queues the validate task for a reissued certificate of an automatic-installation order.
     *
     * Also used by "Revalidate" on automatic-installation orders ($isReissue = false).
     *
     * @param object $sslService SSL service (configdata, remoteid) as saved after the reissue
     * @param string $previousCrt certificate before the reissue: not taken for the reissued one
     * @return bool true when a task was queued
     */
    public static function queue($sslService, $userId, $serviceId, $domain, $previousCrt = '', $isReissue = true)
    {
        $configdata = (array) $sslService->configdata;
        if (!isset($configdata['install_type']) || $configdata['install_type'] !== 'autoinstall') {
            return false;
        }
        if (empty($sslService->remoteid)) {
            return false;
        }

        $cronQueueRepo = new CronQueueRepo();

        $isWildcard = !empty($configdata['is_wildcard']) || strpos((string) $domain, '*.') === 0;
        $dcvMethod  = isset($configdata['dcv_method']) ? strtolower((string) $configdata['dcv_method']) : '';

        $taskData = [
            'domain'       => $domain,
            'install_type' => 'autoinstall',
            'api_order_id' => (int) $sslService->remoteid,
            'is_wildcard'  => $isWildcard,
        ];
        if ($isReissue) {
            $taskData['is_reissue'] = true;
        }
        if (!empty($configdata['tls_item_id'])) {
            $taskData['tls_item_id'] = (int) $configdata['tls_item_id'];
        }
        if ($isWildcard && $dcvMethod === 'dns') {
            $taskData['dns_check_start'] = time();
        }
        if (!empty($previousCrt)) {
            $taskData['previous_crt_md5'] = md5(trim($previousCrt));
        }

        // A validate task still waiting for this service (e.g. of the first order) starts over
        // for the reissued certificate instead of running twice
        $activeTask = $cronQueueRepo->getActiveTask($serviceId, 'validate');
        if ($activeTask && $activeTask->status === 'pending') {
            $cronQueueRepo->restartTask($activeTask->id, $taskData);
        } else {
            $cronQueueRepo->addTask($userId, $serviceId, 'validate', $taskData);
        }

        (new LogsRepo())->addLog($userId, $serviceId, 'info', $isReissue
            ? '[Reissue] Reissue submitted for domain: ' . $domain . ' — validation and automatic installation queued'
            : '[Validate] Revalidation requested for domain: ' . $domain . ' — validation and automatic installation queued');

        return true;
    }

    /**
     * True while the API still returns the certificate from before the reissue.
     */
    public static function isPreviousCertificate(array $taskData, $crtCode)
    {
        return !empty($taskData['previous_crt_md5']) && !empty($crtCode)
            && md5(trim($crtCode)) === $taskData['previous_crt_md5'];
    }
}
