<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;
use MGModule\SSLCENTERWHMCS\eModels\cpanelservices\Service as CpanelService;

/**
 * "Install certificate on" setting of a standard SSL service: which of the client's cPanel hosting
 * services the certificate is installed on, and whether installation is automatic.
 *
 * Stored in tblsslorders.configdata:
 * - cpanel_service_id: tblhosting.id of the chosen cPanel service (0 = find by domain)
 * - install_type:      "autoinstall" (automatic installation, validation file upload and reissue) or "manual"
 */
class InstallTarget
{
    const ERROR_NOT_CONFIGURED   = 'notConfigured';
    const ERROR_INVALID_SERVICE  = 'invalidService';
    const ERROR_NO_PRIVATE_KEY   = 'noPrivateKey';

    /**
     * The client's cPanel hosting services as [tblhosting.id => "domain - Product name"].
     */
    public static function getOptions($userid)
    {
        $options = [];
        foreach ((new CpanelService())->getcPanelServices($userid) as $service) {
            $options[(int) $service->id] = trim($service->domain . ' - ' . $service->name, ' -');
        }
        return $options;
    }

    /**
     * Current setting of an SSL service (eRepository\whmcs\service\SSL model).
     */
    public static function getState($sslService)
    {
        $configdata = $sslService ? (array) $sslService->configdata : [];

        return [
            'linked_id'    => !empty($configdata[CpanelService::LINKED_SERVICE_KEY]) ? (int) $configdata[CpanelService::LINKED_SERVICE_KEY] : 0,
            'automatic'    => isset($configdata['install_type']) && $configdata['install_type'] === 'autoinstall',
            'can_automate' => !empty($configdata['private_key']) && !empty($configdata['csr']),
        ];
    }

    /**
     * Saves the setting. Turning automatic installation on for an issued certificate also queues its installation.
     *
     * @param int  $hostingId 0 = find the hosting account by domain
     * @return array ['success' => bool, 'error' => error code or '', 'queued' => bool]
     */
    public static function save($sslService, $hostingId, $automatic)
    {
        $result = ['success' => false, 'error' => '', 'queued' => false];

        if (!$sslService || empty($sslService->remoteid) || $sslService->status === 'Awaiting Configuration') {
            $result['error'] = self::ERROR_NOT_CONFIGURED;
            return $result;
        }

        $hostingId = (int) $hostingId;
        if ($hostingId > 0 && (new CpanelService())->getLinkedService($sslService->userid, $hostingId) === false) {
            $result['error'] = self::ERROR_INVALID_SERVICE;
            return $result;
        }

        $state = self::getState($sslService);
        if ($automatic && !$state['can_automate']) {
            $result['error'] = self::ERROR_NO_PRIVATE_KEY;
            return $result;
        }

        $sslService->setConfigdataKey(CpanelService::LINKED_SERVICE_KEY, $hostingId);
        $sslService->setConfigdataKey('install_type', $automatic ? 'autoinstall' : 'manual');
        if ($automatic) {
            // Automatic reissue and renewal handling only run for orders marked as configured by the module
            $sslService->setConfigdataKey('configssl', 1);
        }
        $sslService->save();

        $configdata = (array) $sslService->configdata;
        if ($automatic && !$state['automatic'] && !empty($configdata['crt']) && !empty($configdata['ca'])) {
            $result['queued'] = self::queueInstallation($sslService, $configdata);
        }

        $result['success'] = true;
        return $result;
    }

    private static function queueInstallation($sslService, array $configdata)
    {
        $cronQueue = new \MGModule\SSLCENTERWHMCS\models\cronQueue\Repository();

        $existing = Capsule::table($cronQueue->tableName)
            ->where('service_id', (int) $sslService->serviceid)
            ->where('type', 'install')
            ->whereIn('status', ['pending', 'processing'])
            ->first();
        if ($existing) {
            return false;
        }

        $domain = !empty($configdata['domain']) ? $configdata['domain'] : '';
        $cronQueue->addTask($sslService->userid, $sslService->serviceid, 'install', [
            'domain'       => $domain,
            'api_order_id' => (int) $sslService->remoteid,
            'is_renewal'   => false,
        ]);

        (new \MGModule\SSLCENTERWHMCS\models\logs\Repository())->addLog($sslService->userid, $sslService->serviceid, 'info',
            '[Install] Automatic cPanel installation turned on — installation queued for domain: ' . $domain);

        return true;
    }
}
