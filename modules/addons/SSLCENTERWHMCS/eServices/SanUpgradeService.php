<?php

namespace MGModule\SSLCENTERWHMCS\eServices;

use WHMCS\Database\Capsule;
use MGModule\SSLCENTERWHMCS\eHelpers\SanList;
use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions;

/**
 * "Buy more SANs": after a paid configurable options upgrade of an SSL service, the new SAN slots are bought at
 * GoGetSSL right away and the client is told to add the new names (a reissue with the full SAN list).
 */
class SanUpgradeService
{
    /**
     * @param int $upgradeId tblupgrades.id (AfterConfigOptionsUpgrade hook)
     * @return array result for logs/tests: ['status' => ..., 'single' => int, 'wildcard' => int, 'message' => string]
     */
    public static function afterConfigOptionsUpgrade($upgradeId)
    {
        $upgrade = Capsule::table('tblupgrades')->where('id', (int) $upgradeId)->first();
        if (!$upgrade || $upgrade->type !== 'configoptions') {
            return ['status' => 'skipped', 'message' => 'not a configurable options upgrade'];
        }
        return self::syncSanSlots((int) $upgrade->relid);
    }

    /**
     * Buys the SAN slots the service paid for in WHMCS but the GoGetSSL order does not have yet, and emails the
     * client when the certificate has unused SAN slots.
     */
    public static function syncSanSlots($serviceId)
    {
        $hosting = Capsule::table('tblhosting')->where('id', $serviceId)->first();
        if (!$hosting) {
            return ['status' => 'skipped', 'message' => 'service not found'];
        }
        $product = Capsule::table('tblproducts')->where('id', $hosting->packageid)->first();
        if (!$product || $product->servertype !== 'SSLCENTERWHMCS') {
            return ['status' => 'skipped', 'message' => 'not an SSL service of this module'];
        }
        if (self::isNotStandardSsl($serviceId)) {
            return ['status' => 'skipped', 'message' => 'product without SAN slots'];
        }

        $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);
        if (!$sslService || empty($sslService->remoteid)) {
            // not ordered yet: the configuration form orders the SANs the client paid for
            return ['status' => 'skipped', 'message' => 'certificate not ordered yet'];
        }

        $params = self::buildParams($product, $serviceId);
        $limits = SanList::getProductLimits($params);
        $logs   = new \MGModule\SSLCENTERWHMCS\models\logs\Repository();

        try {
            $api    = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi();
            $status = $api->getOrderStatus($sslService->remoteid);
        } catch (\Exception $e) {
            $logs->addLog($hosting->userid, $serviceId, 'error', '[SAN upgrade] Order status could not be read: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        $purchasedSingle   = isset($status['single_san_count']) ? (int) $status['single_san_count'] : 0;
        $purchasedWildcard = isset($status['wildcard_san_count']) ? (int) $status['wildcard_san_count'] : 0;
        $buySingle         = max(0, $limits['single'] - $purchasedSingle);
        $buyWildcard       = max(0, $limits['wildcard'] - $purchasedWildcard);

        if (!isset($status['status']) || $status['status'] !== 'active') {
            // SAN slots can be added to issued certificates only: the next reissue buys them
            $logs->addLog($hosting->userid, $serviceId, 'info', sprintf(
                '[SAN upgrade] Certificate is not issued yet (%s): %d single / %d wildcard SAN slot(s) will be added with the next reissue.',
                isset($status['status']) ? $status['status'] : 'unknown', $buySingle, $buyWildcard
            ));
            return ['status' => 'pending', 'single' => $buySingle, 'wildcard' => $buyWildcard, 'message' => 'certificate not active'];
        }

        if ($buySingle + $buyWildcard > 0) {
            try {
                SanList::buySlots($api, $sslService->remoteid, $buySingle, $buyWildcard);
                $logs->addLog($hosting->userid, $serviceId, 'success', sprintf(
                    '[SAN upgrade] Added %d single / %d wildcard SAN slot(s) to GoGetSSL order #%s.',
                    $buySingle, $buyWildcard, $sslService->remoteid
                ));
                $status = $api->getOrderStatus($sslService->remoteid);
            } catch (\Exception $e) {
                $logs->addLog($hosting->userid, $serviceId, 'error', '[SAN upgrade] SAN slots could not be added at GoGetSSL (the next reissue adds them): ' . $e->getMessage());
                return ['status' => 'error', 'single' => $buySingle, 'wildcard' => $buyWildcard, 'message' => $e->getMessage()];
            }
        }

        SanList::storePurchased($sslService, $status);

        $free = SanList::getFreeSlots($params, $sslService);
        if ($free['single'] + $free['wildcard'] > 0) {
            self::sendAddSansEmail($serviceId, (string) $hosting->domain, $buySingle + $buyWildcard, $free['single'] + $free['wildcard']);
        }

        return ['status' => 'done', 'single' => $buySingle, 'wildcard' => $buyWildcard, 'free' => $free, 'message' => 'ok'];
    }

    /**
     * Client area link to the reissue page, where the client adds the new names.
     */
    public static function getAddSansUrl($serviceId)
    {
        $systemUrl = (string) Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value');
        return rtrim($systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId
            . '&modop=custom&a=ClientAreaCustomReissueCertificate';
    }

    public static function sendAddSansEmail($serviceId, $domain, $added, $free)
    {
        $result = localAPI('SendEmail', [
            'messagename' => EmailTemplateService::ADD_SANS_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'       => $domain,
                'san_added'    => (int) $added,
                'san_free'     => (int) $free,
                'add_sans_url' => self::getAddSansUrl($serviceId),
            ])),
        ]);
        return isset($result['result']) && $result['result'] === 'success';
    }

    private static function buildParams($product, $serviceId)
    {
        $params = [
            ConfigOptions::PRODUCT_ENABLE_SAN             => $product->configoption3,
            ConfigOptions::PRODUCT_INCLUDED_SANS          => $product->configoption4,
            ConfigOptions::PRODUCT_ENABLE_SAN_WILDCARD    => $product->configoption13,
            ConfigOptions::PRODUCT_INCLUDED_SANS_WILDCARD => $product->configoption8,
            'configoptions'                               => [],
        ];
        $options = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\configOptions\Repository($serviceId);
        foreach ([ConfigOptions::OPTION_SANS_COUNT, ConfigOptions::OPTION_SANS_WILDCARD_COUNT] as $name) {
            if (isset($options->{$name})) {
                $params['configoptions'][$name] = (int) $options->{$name};
            }
        }
        return $params;
    }

    private static function isNotStandardSsl($serviceId)
    {
        $checks = [
            ['\MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct', 'isSmimeByServiceId'],
            ['\MGModule\SSLCENTERWHMCS\eHelpers\CodeSigningProduct', 'isCodeSigningByServiceId'],
            ['\MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription', 'isAcmeByServiceId'],
            ['\MGModule\SSLCENTERWHMCS\eHelpers\AisSubscription', 'isAisByServiceId'],
        ];
        foreach ($checks as $check) {
            if (method_exists($check[0], $check[1]) && call_user_func($check, $serviceId)) {
                return true;
            }
        }
        return false;
    }
}
