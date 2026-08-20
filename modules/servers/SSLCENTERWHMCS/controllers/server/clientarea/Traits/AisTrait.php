<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits;

use MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule;

trait AisTrait
{
    private function indexAisHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];
        $userId    = (int) $input['params']['userid'];

        $product = Capsule::table('tblproducts')
            ->select(['name'])
            ->where('id', (int) $input['params']['pid'])
            ->first();

        $aisRepo     = new \MGModule\SSLCENTERWHMCS\models\aisSubscription\Repository();
        $subscription = $aisRepo->getByServiceId($serviceId);

        if ($subscription && (int) $subscription->api_order_id > 0
            && strtolower((string) $subscription->status) !== 'active')
        {
            try
            {
                $this->refreshAisDetails($serviceId);
                $subscription = $aisRepo->getByServiceId($serviceId);
            }
            catch (\Exception $e)
            {
                main\eHelpers\Whmcs::savelogActivitySSLCenter(
                    'SSLCENTER WHMCS: Unable to auto-refresh AIS details for service #' . $serviceId . '. Error: ' . $e->getMessage()
                );
            }
        }

        $sslRepo   = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);

        $vars['allOk']              = true;
        $vars['serviceid']          = $serviceId;
        $vars['userid']             = $userId;
        $vars['assetsURL']          = main\Server::I()->getAssetsURL();
        $vars['isAisSubscription']  = true;
        $vars['productName']        = isset($product->name) ? $product->name : '';
        $vars['subscription']       = $subscription;
        $vars['configurationStatus'] = $sslService ? $sslService->status : null;
        $vars['configurationURL']   = 'clientarea.php?action=productdetails&id=' . $serviceId . '&aisconfig=1';

        if ($vars['configurationStatus'] === 'Awaiting Configuration')
        {
            return [
                'tpl'  => 'home_ais_awaiting_configuration',
                'vars' => $vars,
            ];
        }

        return [
            'tpl'  => 'home_ais',
            'vars' => $vars,
        ];
    }

    private function aisConfigurationHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];
        $userId    = (int) $input['params']['userid'];

        $product = Capsule::table('tblproducts')
            ->select(['name'])
            ->where('id', (int) $input['params']['pid'])
            ->first();

        $service = Capsule::table('tblhosting')
            ->select(['domain'])
            ->where('id', $serviceId)
            ->first();

        $vars['allOk']             = true;
        $vars['serviceid']         = $serviceId;
        $vars['userid']            = $userId;
        $vars['assetsURL']         = main\Server::I()->getAssetsURL();
        $vars['isAisSubscription'] = true;
        $vars['productName']       = isset($product->name) ? $product->name : '';
        $vars['serviceDomain']     = $service ? $service->domain : '';

        return [
            'tpl'  => 'home_ais_configuration',
            'vars' => $vars,
        ];
    }

    public function createAisJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $domain    = isset($input['domain']) ? strtolower(trim($input['domain'])) : '';

        if (empty($domain))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('aisDomainRequired'));
        }

        if (\MGModule\SSLCENTERWHMCS\eHelpers\Domains::validateDomain(ltrim($domain, '*.')) !== true)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('aisInvalidDomain'));
        }

        $aisRepo     = new \MGModule\SSLCENTERWHMCS\models\aisSubscription\Repository();
        $subscription = $aisRepo->getByServiceId($serviceId);

        if ($subscription && (int) $subscription->api_order_id > 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('aisAlreadyCreated'));
        }

        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();

        $clientDetails = Capsule::table('tblclients')
            ->select(['email'])
            ->where('id', (int) $input['params']['userid'])
            ->first();

        $clientEmail = $clientDetails ? $clientDetails->email : '';

        $useAdminContact = $apiConf && $apiConf->use_admin_contact;
        $techEmail       = ($useAdminContact && $apiConf->tech_email)
            ? $apiConf->tech_email
            : $clientEmail;

        $product = Capsule::table('tblproducts')
            ->select(['configoption1'])
            ->where('id', (int) $input['params']['pid'])
            ->first();

        $apiProductId = $product && !empty($product->configoption1) ? (int) $product->configoption1 : 0;

        if ($apiProductId <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('aisMissingProductId'));
        }

        $api = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi();

        $response = $api->createAis([
            'product'  => ['id' => $apiProductId],
            'domains'  => [$domain],
            'contacts' => [
                'primary'   => ['email' => $clientEmail],
                'technical' => ['email' => $techEmail],
            ],
        ]);

        $apiOrderId = isset($response['order']['id'])  ? (int) $response['order']['id']  : 0;
        $orderStatus = isset($response['order']['status']) ? $response['order']['status'] : 'pending';
        $items       = isset($response['items']) && is_array($response['items']) ? $response['items'] : [];
        $firstItem   = isset($items[0]) ? (array) $items[0] : [];
        $aisItemId   = isset($firstItem['id']) ? (int) $firstItem['id'] : 0;

        if ($apiOrderId <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('aisMissingOrderId'));
        }

        $aisRepo->upsertByServiceId($serviceId, [
            'client_id'   => (int) $input['params']['userid'],
            'api_order_id' => $apiOrderId,
            'ais_item_id'  => $aisItemId > 0 ? $aisItemId : null,
            'status'       => $orderStatus,
        ]);

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domain' => $domain]);

        $sslRepo   = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);
        if ($sslService)
        {
            $sslService->setRemoteId($apiOrderId);
            $sslService->status = 'Configuration Submitted';
            $sslService->setSSLStatus($orderStatus);
            $sslService->save();
        }

        // Immediately try to fetch SSO links from the details endpoint
        if ($aisItemId > 0)
        {
            try
            {
                $this->refreshAisDetails($serviceId);
            }
            catch (\Exception $e)
            {
                // Non-fatal — links will be fetched on next page load
            }
        }

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('aisCreatedSuccessfully'),
        ];
    }

    public function refreshAisDetailsJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $this->refreshAisDetails($serviceId);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('aisDetailsRefreshed'),
        ];
    }

    private function refreshAisDetails($serviceId)
    {
        $aisRepo     = new \MGModule\SSLCENTERWHMCS\models\aisSubscription\Repository();
        $subscription = $aisRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->ais_item_id <= 0)
        {
            return;
        }

        $details = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()
            ->getApi()
            ->getAisDetails((int) $subscription->ais_item_id);

        $orderStatus  = isset($details['order']['status'])  ? $details['order']['status']  : $subscription->status;
        $items        = isset($details['items']) && is_array($details['items']) ? $details['items'] : [];
        $firstItem    = isset($items[0]) ? (array) $items[0] : [];
        $autoinstall  = isset($firstItem['autoinstall']) ? (array) $firstItem['autoinstall'] : [];
        $subData      = isset($firstItem['subscription']) ? (array) $firstItem['subscription'] : [];

        $loginLink   = isset($autoinstall['login_sso_link'])  ? $autoinstall['login_sso_link']  : $subscription->login_sso_link;
        $manageLink  = isset($autoinstall['manage_sso_link']) ? $autoinstall['manage_sso_link'] : $subscription->manage_sso_link;
        $aiStatus    = isset($autoinstall['status'])          ? $autoinstall['status']          : $subscription->autoinstall_status;
        $periodStart = isset($subData['begin'])        ? $subData['begin']        : $subscription->period_start;
        $periodEnd   = isset($subData['next_renewal']) ? $subData['next_renewal'] : $subscription->period_end;
        $renewalDate = isset($subData['next_renewal']) ? $subData['next_renewal'] : $subscription->renewal_date;

        $aisRepo->upsertByServiceId($serviceId, [
            'status'              => $orderStatus,
            'login_sso_link'      => $loginLink,
            'manage_sso_link'     => $manageLink,
            'autoinstall_status'  => $aiStatus,
            'period_start'        => $periodStart ?: null,
            'period_end'          => $periodEnd   ?: null,
            'renewal_date'        => $renewalDate ?: null,
        ]);

        $sslRepo   = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);
        if ($sslService)
        {
            $sslService->status = ($orderStatus === 'active') ? 'Completed' : 'Configuration Submitted';
            $sslService->setSSLStatus($orderStatus);
            $sslService->save();
        }
    }
}
