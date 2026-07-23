<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits;

use MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule;
use MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription as AcmeHelper;

trait AcmeTrait
{
    private function indexAcmeHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];
        $userId    = (int) $input['params']['userid'];
        $sslRepo   = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);

        $product = Capsule::table('tblproducts')
            ->select(['name', 'configoption13'])
            ->where('id', (int) $input['params']['pid'])
            ->first();

        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $domainRepo       = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();

        $subscription = $subscriptionRepo->getByServiceId($serviceId);
        if ($subscription && (int) $subscription->api_order_id > 0 && strtolower((string) $subscription->status) !== 'active')
        {
            try
            {
                $this->refreshAcmeCertificateDetails($serviceId);
                $subscription = $subscriptionRepo->getByServiceId($serviceId);
            }
            catch (\Exception $e)
            {
                main\eHelpers\Whmcs::savelogActivitySSLCenter(
                    'SSLCENTER WHMCS: Unable to auto-refresh ACME certificate details for service #' . (int) $serviceId . '. Error: ' . $e->getMessage()
                );
            }
        }
        $domains      = $domainRepo->getByServiceId($serviceId);
        $limits = $this->getAcmeDomainLimits($input['params']);
        $singleIncludedSans = $this->getAcmeIncludedSans($input['params'], 'single');
        $wildcardIncludedSans = $this->getAcmeIncludedSans($input['params'], 'wildcard');
        $singleBoughtSans = isset($input['params']['configoptions']['sans_count']) ? (int) $input['params']['configoptions']['sans_count'] : 0;
        $wildcardBoughtSans = isset($input['params']['configoptions']['sans_wildcard_count']) ? (int) $input['params']['configoptions']['sans_wildcard_count'] : 0;
        $activeCounts = $this->getActiveAcmeDomainsCount($serviceId, $domainRepo->tableName);
        $singleSansCurrent = $activeCounts['single'];
        $wildcardSansCurrent = $activeCounts['wildcard'];
        $totalSansCurrent = $singleSansCurrent + $wildcardSansCurrent;
        $singleSansPurchased = $singleIncludedSans + $singleBoughtSans;
        $wildcardSansPurchased = $wildcardIncludedSans + $wildcardBoughtSans;
        $totalSansPurchased = $singleSansPurchased + $wildcardSansPurchased;
        $availableSingleSlots = max(0, $limits['single'] - $singleSansCurrent);
        $availableWildcardSlots = max(0, $limits['wildcard'] - $wildcardSansCurrent);
        $canAddDomains = $availableSingleSlots > 0 || ($availableWildcardSlots > 0 && isset($product->configoption13) && $product->configoption13 === 'on');

        $vars['allOk']                = true;
        $vars['serviceid']            = $serviceId;
        $vars['userid']               = $userId;
        $vars['assetsURL']            = main\Server::I()->getAssetsURL();
        $vars['isAcmeSubscription']   = true;
        $vars['productName']          = isset($product->name) ? $product->name : main\mgLibs\Lang::absoluteT('acmeDefaultProductName');
        $vars['allowWildcard']        = isset($product->configoption13) && $product->configoption13 === 'on';
        $vars['subscription']         = $subscription;
        $vars['domains']              = $domains;
        $vars['singleSansCurrent']    = $singleSansCurrent;
        $vars['singleSansPurchased']  = $singleSansPurchased;
        $vars['wildcardSansCurrent']  = $wildcardSansCurrent;
        $vars['wildcardSansPurchased']= $wildcardSansPurchased;
        $vars['totalSansCurrent']     = $totalSansCurrent;
        $vars['totalSansPurchased']   = $totalSansPurchased;
        $vars['availableSingleSlots'] = $availableSingleSlots;
        $vars['availableWildcardSlots'] = $availableWildcardSlots;
        $vars['canAddDomains']        = $canAddDomains;
        $vars['configurationStatus']  = $sslService ? $sslService->status : null;
        $vars['configurationURL']     = 'clientarea.php?action=productdetails&id=' . $serviceId . '&acmeconfig=1';
        $vars['nextInvoiceDate']      = '';

        if ($subscription && (int) $subscription->auto_renew === 1 && !empty($subscription->renewal_date))
        {
            $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
            $renewInvoiceDays = isset($apiConf->renew_invoice_days_subscription) && is_numeric($apiConf->renew_invoice_days_subscription)
                ? (int) $apiConf->renew_invoice_days_subscription
                : 0;
            if ($renewInvoiceDays < 0) {
                $renewInvoiceDays = 0;
            }

            $renewalTimestamp = strtotime((string) $subscription->renewal_date);
            if ($renewalTimestamp !== false) {
                $vars['nextInvoiceDate'] = date('Y-m-d', strtotime('-' . $renewInvoiceDays . ' days', $renewalTimestamp));
            }
        }

        if ($vars['configurationStatus'] === 'Awaiting Configuration')
        {
            return array(
                'tpl' => 'home_acme_awaiting_configuration',
                'vars' => $vars
            );
        }

        return array(
            'tpl' => 'home_acme',
            'vars' => $vars
        );
    }

    private function acmeConfigurationHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];
        $userId    = (int) $input['params']['userid'];

        $product = Capsule::table('tblproducts')
            ->select(['name', 'configoption13'])
            ->where('id', (int) $input['params']['pid'])
            ->first();

        $singleLimit = $this->getAcmeIncludedSans($input['params'], 'single');
        $singleOptionCount = isset($input['params']['configoptions']['sans_count']) ? (int) $input['params']['configoptions']['sans_count'] : 0;
        $singleLimit += $singleOptionCount;

        $wildcardLimit = $this->getAcmeIncludedSans($input['params'], 'wildcard');
        $wildcardOptionCount = isset($input['params']['configoptions']['sans_wildcard_count']) ? (int) $input['params']['configoptions']['sans_wildcard_count'] : 0;
        $wildcardLimit += $wildcardOptionCount;

        $vars['allOk']              = true;
        $vars['serviceid']          = $serviceId;
        $vars['userid']             = $userId;
        $vars['assetsURL']          = main\Server::I()->getAssetsURL();
        $vars['isAcmeSubscription'] = true;
        $vars['productName']        = isset($product->name) ? $product->name : main\mgLibs\Lang::absoluteT('acmeDefaultProductName');
        $vars['allowWildcard']      = isset($product->configoption13) && $product->configoption13 === 'on';
        $vars['singleDomainsLimit'] = $singleLimit;
        $vars['wildcardDomainsLimit'] = $wildcardLimit;

        return array(
            'tpl' => 'home_acme_configuration',
            'vars' => $vars
        );
    }

    public function createSubscriptionJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $singleDomains = $this->extractDomainsFromInput(isset($input['single_domains']) ? $input['single_domains'] : '');
        $wildcardDomains = $this->extractDomainsFromInput(isset($input['wildcard_domains']) ? $input['wildcard_domains'] : '');
        $domainsWithType = [];

        if (empty($singleDomains) && empty($wildcardDomains))
        {
            $domains = $this->extractDomainsFromInput(isset($input['domains']) ? $input['domains'] : '');
            if (empty($domains))
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acmePleaseProvideAtLeastOneDomain'));
            }

            foreach ($domains as $domain)
            {
                if (strpos($domain, '*.') === 0)
                {
                    $domainsWithType[$domain] = 'wildcard';
                }
                else
                {
                    $domainsWithType[$domain] = 'single';
                }
            }
        }
        else
        {
            foreach ($singleDomains as $domain)
            {
                $domainsWithType[$domain] = 'single';
            }
            foreach ($wildcardDomains as $domain)
            {
                $domainsWithType[$domain] = 'wildcard';
            }
        }

        if (empty($domainsWithType))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmePleaseProvideAtLeastOneDomain'));
        }

        $limits = $this->getAcmeDomainLimits($input['params']);
        $singleDomainsCount = 0;
        $wildcardDomainsCount = 0;
        foreach ($domainsWithType as $type)
        {
            if ($type === 'wildcard')
            {
                $wildcardDomainsCount++;
            }
            else
            {
                $singleDomainsCount++;
            }
        }

        if ($singleDomainsCount > $limits['single'])
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeExceededSingleDomainsLimit'), $limits['single']));
        }
        if ($wildcardDomainsCount > 0 && $wildcardDomainsCount > $limits['wildcard'])
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeExceededWildcardDomainsLimit'), $limits['wildcard']));
        }

        if (in_array('wildcard', $domainsWithType, true) && (!isset($input['params']['configoption13']) || $input['params']['configoption13'] !== 'on'))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeWildcardDomainsNotEnabled'));
        }

        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $domainRepo       = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();

        $subscription = $subscriptionRepo->getByServiceId($serviceId);
        if ($subscription && (int) $subscription->api_order_id > 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionAlreadyCreated'));
        }

        foreach ($domainsWithType as $domain => $type)
        {
            $this->validateAcmeDomain($domain, $type);
        }

        $domains = array_keys($domainsWithType);

        $api = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi();
        $acmeProductId = isset($input['params']['configoption1']) ? (int) $input['params']['configoption1'] : (int) AcmeHelper::getProductIds()[0];
        $response = $api->createAcmeSubscription([
            'product_id' => $acmeProductId,
            'domains'    => implode(',', $domains),
        ]);

        $apiOrderId = isset($response['order_id']) ? (int) $response['order_id'] : (isset($response['id']) ? (int) $response['id'] : 0);
        $apiItems = isset($response['items']) && is_array($response['items']) ? $response['items'] : array();
        $apiFirstItem = isset($apiItems[0]) && is_array($apiItems[0]) ? $apiItems[0] : array();
        $acmeId = isset($apiFirstItem['id']) ? (int) $apiFirstItem['id'] : (isset($response['item_id']) ? (int) $response['item_id'] : 0);
        if ($apiOrderId <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeMissingSubscriptionOrderId'));
        }

        $primaryDomain = reset($domains);
        if (!empty($primaryDomain))
        {
            Capsule::table('tblhosting')->where('id', $serviceId)->update([
                'domain' => $primaryDomain
            ]);
        }

        $subscription = $subscriptionRepo->upsertByServiceId($serviceId, [
            'client_id'        => (int) $input['params']['userid'],
            'api_order_id'     => $apiOrderId,
            'acme_id'          => $acmeId > 0 ? $acmeId : null,
            'status'           => isset($response['status']) ? $response['status'] : 'active',
            'acme_account_id'  => isset($response['acme_account_id']) ? (string) $response['acme_account_id'] : '',
            'eab_kid'          => isset($response['eab_kid']) ? (string) $response['eab_kid'] : '',
            'eab_hmac_key'     => isset($response['eab_hmac_key']) ? (string) $response['eab_hmac_key'] : '',
            'server_url'       => isset($response['server_url']) ? (string) $response['server_url'] : '',
            'period_start'     => isset($response['begin_date']) ? $response['begin_date'] : null,
            'period_end'       => isset($response['end_date']) ? $response['end_date'] : null,
            'renewal_date'     => isset($response['renewal_date']) ? $response['renewal_date'] : (isset($response['end_date']) ? $response['end_date'] : null),
            'auto_renew'       => 1,
        ]);

        foreach ($domainsWithType as $domain => $type)
        {
            $domainRepo->addDomain($serviceId, $domain, $type);
        }

        $sslRepo = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);
        if ($sslService)
        {
            $sslService->setRemoteId($apiOrderId);
            $sslService->status = 'Configuration Submitted';
            $sslService->setConfigdataKey('subscription', true);
            $sslService->setSSLStatus($subscription->status);
            if (!empty($subscription->period_start))
            {
                $sslService->setConfigdataKey('begin_date', $subscription->period_start);
            }
            if (!empty($subscription->period_end))
            {
                $sslService->setConfigdataKey('end_date', $subscription->period_end);
            }
            if (!empty($subscription->renewal_date))
            {
                $sslService->setConfigdataKey('renewal_date', $subscription->renewal_date);
            }
            $sslService->save();

            $orderRepo = new \MGModule\SSLCENTERWHMCS\models\orders\Repository();
            $usedTypes = array_values(array_unique(array_values($domainsWithType)));
            $verificationMethod = count($usedTypes) > 1 ? 'mixed' : ($usedTypes[0] === 'wildcard' ? 'dns' : 'http');
            $orderRepo->addOrder(
                (int) $input['params']['userid'],
                $serviceId,
                (int) $sslService->id,
                $verificationMethod,
                'Configuration Submitted',
                $response
            );
        }

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeSubscriptionConfigurationSubmittedSuccessfully'),
        ];
    }

    public function buyMoreDomainsJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $singleDomains = $this->extractDomainsFromInput(isset($input['single_domains']) ? $input['single_domains'] : '');
        $wildcardDomains = $this->extractDomainsFromInput(isset($input['wildcard_domains']) ? $input['wildcard_domains'] : '');
        $domainsWithType = [];

        if (empty($singleDomains) && empty($wildcardDomains))
        {
            $domains = $this->extractDomainsFromInput(isset($input['domains']) ? $input['domains'] : '');
            $domainType = isset($input['domain_type']) && $input['domain_type'] === 'wildcard' ? 'wildcard' : 'single';

            foreach ($domains as $domain)
            {
                $domainsWithType[$domain] = $domainType;
            }
        }
        else
        {
            foreach ($singleDomains as $domain)
            {
                $domainsWithType[$domain] = 'single';
            }
            foreach ($wildcardDomains as $domain)
            {
                $domainsWithType[$domain] = 'wildcard';
            }
        }

        if (empty($domainsWithType))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmePleaseProvideAtLeastOneDomain'));
        }

        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $domainRepo       = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        $existingDomains = [];
        foreach ($domainRepo->getByServiceId($serviceId) as $existingDomainRow)
        {
            $existingDomain = strtolower(trim((string) $existingDomainRow->domain));
            if ($existingDomain !== '')
            {
                $existingDomains[$existingDomain] = true;
            }
        }

        $duplicatedDomains = [];
        foreach (array_keys($domainsWithType) as $domain)
        {
            $normalizedDomain = strtolower(trim((string) $domain));
            if (isset($existingDomains[$normalizedDomain]))
            {
                $duplicatedDomains[] = $normalizedDomain;
            }
        }

        if (!empty($duplicatedDomains))
        {
            $duplicatedDomains = array_values(array_unique($duplicatedDomains));
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeDomainsAlreadyExist'), implode(', ', $duplicatedDomains)));
        }

        if (in_array('wildcard', $domainsWithType, true) && (!isset($input['params']['configoption13']) || $input['params']['configoption13'] !== 'on'))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeWildcardDomainsNotEnabled'));
        }

        $limits = $this->getAcmeDomainLimits($input['params']);
        $activeCounts = $this->getActiveAcmeDomainsCount($serviceId, $domainRepo->tableName);
        $remainingSingle = max(0, $limits['single'] - $activeCounts['single']);
        $remainingWildcard = max(0, $limits['wildcard'] - $activeCounts['wildcard']);

        $singleDomainsCount = 0;
        $wildcardDomainsCount = 0;
        foreach ($domainsWithType as $type)
        {
            if ($type === 'wildcard')
            {
                $wildcardDomainsCount++;
            }
            else
            {
                $singleDomainsCount++;
            }
        }

        if ($singleDomainsCount > $remainingSingle)
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeExceededAvailableSingleDomainSlots'), $remainingSingle));
        }

        if ($wildcardDomainsCount > $remainingWildcard)
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeExceededAvailableWildcardDomainSlots'), $remainingWildcard));
        }

        foreach ($domainsWithType as $domain => $type)
        {
            $this->validateAcmeDomain($domain, $type);
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addAcmeDomains((int) $subscription->acme_id, [
            'domains' => implode(',', array_keys($domainsWithType)),
        ]);

        foreach ($domainsWithType as $domain => $type)
        {
            $domainRepo->addDomain($serviceId, $domain, $type);
        }

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeDomainsAddedSuccessfully'),
        ];
    }

    public function upgradeConfigOptionsJSON($input, $vars = array())
    {
        $serviceId    = (int) $input['id'];
        $newSingle    = isset($input['new_sans_count']) ? (int) $input['new_sans_count'] : -1;
        $newWildcard  = isset($input['new_sans_wildcard_count']) ? (int) $input['new_sans_wildcard_count'] : -1;

        if ($newSingle < 0 && $newWildcard < 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeNoValueProvided'));
        }

        $unpaidInvoice = Capsule::table('tblinvoices')
            ->join('tblinvoiceitems', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoiceitems.relid', $serviceId)
            ->whereIn('tblinvoices.status', ['Unpaid', 'Overdue'])
            ->first();

        if ($unpaidInvoice)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeUnpaidInvoiceExists'));
        }

        $domainRepo   = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();
        $activeCounts = $this->getActiveAcmeDomainsCount($serviceId, $domainRepo->tableName);

        $CORepo = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\configOptions\Repository($serviceId);

        $configOptionsPayload = [];

        if ($newSingle >= 0)
        {
            if ($newSingle < $activeCounts['single'])
            {
                throw new \Exception(sprintf(
                    main\mgLibs\Lang::absoluteT('acmeDowngradeNotEnoughSingleSlots'),
                    $activeCounts['single']
                ));
            }
            $singleConfigId = $CORepo->getConfigID('sans_count');
            if (!$singleConfigId)
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeConfigOptionNotFound'));
            }
            $configOptionsPayload[$singleConfigId] = $newSingle;
        }

        if ($newWildcard >= 0)
        {
            if ($newWildcard < $activeCounts['wildcard'])
            {
                throw new \Exception(sprintf(
                    main\mgLibs\Lang::absoluteT('acmeDowngradeNotEnoughWildcardSlots'),
                    $activeCounts['wildcard']
                ));
            }
            $wildcardConfigId = $CORepo->getConfigID('sans_wildcard_count');
            if (!$wildcardConfigId)
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeConfigOptionNotFound'));
            }
            $configOptionsPayload[$wildcardConfigId] = $newWildcard;
        }

        $service = Capsule::table('tblhosting')->where('id', $serviceId)->first();
        if (!$service)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeServiceNotFound'));
        }

        $adminUserName = main\eHelpers\Admin::getAdminUserName();
        $apiParams = [
            'serviceid'     => $serviceId,
            'paymentmethod' => $service->paymentmethod,
            'type'          => 'configoptions',
            'configoptions' => $configOptionsPayload,
        ];

        $result = localAPI('UpgradeProduct', $apiParams, $adminUserName);

        if (!isset($result['result']) || $result['result'] !== 'success')
        {
            $errorMsg = isset($result['message']) ? $result['message'] : 'UpgradeProduct API failed';
            throw new \Exception($errorMsg);
        }

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeUpgradeSuccess'),
        ];
    }

    public function removeDomainJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $domain    = isset($input['domain']) ? strtolower(trim($input['domain'])) : '';
        if (empty($domain))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeDomainIsRequired'));
        }

        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $domainRepo       = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        $domainRow = Capsule::table($domainRepo->tableName)
            ->where('service_id', $serviceId)
            ->where('domain', $domain)
            ->where('status', 'added')
            ->first();

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->removeAcmeDomain((int) $subscription->acme_id, [
            'domain' => $domain,
        ]);

        $addedAt      = $domainRow ? strtotime((string) $domainRow->added_at) : false;
        $withinWindow = $addedAt !== false && (time() - $addedAt) < (30 * 24 * 60 * 60);

        if ($withinWindow)
        {
            Capsule::table($domainRepo->tableName)
                ->where('service_id', $serviceId)
                ->where('domain', $domain)
                ->delete();

            return [
                'success' => true,
                'message' => main\mgLibs\Lang::absoluteT('acmeDomainRemovedAndRefunded'),
            ];
        }

        $domainRepo->removeDomain($serviceId, $domain);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeDomainRemovedSuccessfully'),
        ];
    }

    public function cancelSubscriptionJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->cancelCertificate((int) $subscription->acme_id, 'Cancelled by client');
        $subscriptionRepo->upsertByServiceId($serviceId, [
            'status'       => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'auto_renew'   => 0,
        ]);

        Capsule::table('tblhosting')->where('id', $serviceId)->update([
            'domainstatus' => 'Cancelled'
        ]);

        $refundedCreditsAmount = $this->refundLatestPaidServiceInvoice($serviceId, 'ACME subscription cancelled within refund window');
        $message = main\mgLibs\Lang::absoluteT('acmeSubscriptionCancelled');
        if ($refundedCreditsAmount > 0)
        {
            $message = sprintf(
                main\mgLibs\Lang::absoluteT('acmeSubscriptionCancelledAndCreditsRefunded'),
                number_format((float) $refundedCreditsAmount, 2, '.', '')
            );
        }

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    public function stopAutoRenewalJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->disableSubscriptionAutoRenewal((int) $subscription->acme_id);
        $subscriptionRepo->upsertByServiceId($serviceId, [
            'auto_renew' => 0,
        ]);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeAutoRenewalDisabled'),
        ];
    }

    public function checkCertificateDetailsJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $this->refreshAcmeCertificateDetails($serviceId);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeCertificateDetailsRefreshed'),
        ];
    }

    private function refreshAcmeCertificateDetails($serviceId)
    {
        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        $apiOrderId = (int) $subscription->api_order_id;
        $acmeItemId = (int) $subscription->acme_id;

        if ($acmeItemId > 0)
        {
            $details = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getCertificateDetails('caas', $acmeItemId);
        }
        else
        {
            $details = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getCertificateDetails('acme', $apiOrderId);
        }

        $orderStatus = isset($details['order']['status']) ? $details['order']['status'] : (isset($details['status']) ? $details['status'] : $subscription->status);
        $items = isset($details['items']) && is_array($details['items']) ? $details['items'] : array();
        $firstItem = isset($items[0]) && is_array($items[0]) ? $items[0] : array();
        $acmeId = isset($firstItem['id']) ? (int) $firstItem['id'] : (isset($subscription->acme_id) ? (int) $subscription->acme_id : 0);
        $account = isset($firstItem['account']) && is_array($firstItem['account']) ? $firstItem['account'] : array();
        $subscriptionData = isset($firstItem['subscription']) && is_array($firstItem['subscription']) ? $firstItem['subscription'] : array();

        $periodStart = isset($subscriptionData['begin']) ? $subscriptionData['begin'] : $subscription->period_start;
        $periodEnd = isset($subscriptionData['end']) ? $subscriptionData['end'] : $subscription->period_end;
        $renewalDate = isset($subscriptionData['next_renewal']) ? $subscriptionData['next_renewal'] : $subscription->renewal_date;
        $autoRenew = isset($subscriptionData['next_renewal']) ? 1 : (int) $subscription->auto_renew;

        $subscriptionRepo->upsertByServiceId($serviceId, [
            'status'          => $orderStatus,
            'acme_id'         => $acmeId > 0 ? $acmeId : null,
            'acme_account_id' => isset($account['id']) ? (string) $account['id'] : (string) $subscription->acme_account_id,
            'eab_kid'         => isset($account['eab_mac_id']) ? (string) $account['eab_mac_id'] : (string) $subscription->eab_kid,
            'eab_hmac_key'    => isset($account['eab_mac_key']) ? (string) $account['eab_mac_key'] : (string) $subscription->eab_hmac_key,
            'server_url'      => isset($account['server_url']) ? (string) $account['server_url'] : (string) $subscription->server_url,
            'period_start'    => $periodStart,
            'period_end'      => $periodEnd,
            'renewal_date'    => $renewalDate,
            'auto_renew'      => $autoRenew,
        ]);

        $sslRepo = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);
        if ($sslService)
        {
            $sslService->remoteid = $apiOrderId;
            $sslService->status   = 'Completed';
            $sslService->configdata = json_encode([
                'subscription' => true,
                'ssl_status'   => $orderStatus,
                'begin_date'   => $periodStart,
                'end_date'     => $periodEnd,
                'renewal_date' => $renewalDate,
            ]);
            $sslService->save();
        }
    }

    private function isWithinAcmeRefundWindow($serviceId, $days = 30)
    {
        $latestPaidInvoice = $this->getLatestPaidServiceInvoiceForRefund($serviceId);
        if ($latestPaidInvoice && !empty($latestPaidInvoice->datepaid))
        {
            $paidAt = strtotime($latestPaidInvoice->datepaid);
            if ($paidAt !== false)
            {
                $daysFromPayment = floor((time() - $paidAt) / 86400);
                return $daysFromPayment >= 0 && $daysFromPayment <= (int) $days;
            }
        }

        $service = Capsule::table('tblhosting')
            ->select(['regdate'])
            ->where('id', (int) $serviceId)
            ->first();

        if (!$service || empty($service->regdate))
        {
            return false;
        }

        $regDate = strtotime($service->regdate);
        if ($regDate === false)
        {
            return false;
        }

        $daysFromPurchase = floor((time() - $regDate) / 86400);
        return $daysFromPurchase <= (int) $days;
    }

    private function refundLatestPaidServiceInvoice($serviceId, $reason = '')
    {
        $invoice = $this->getLatestPaidServiceInvoiceForRefund($serviceId);

        if (!$invoice || empty($invoice->invoiceid))
        {
            return 0.00;
        }

        $creditPrefix = 'ACME refund for invoice #' . (int) $invoice->invoiceid;
        $alreadyCreditedAmount = Capsule::table('tblcredit')
            ->where('clientid', (int) $invoice->userid)
            ->where('description', 'LIKE', $creditPrefix . '%')
            ->sum('amount');

        if ((float) $alreadyCreditedAmount > 0)
        {
            return round((float) $alreadyCreditedAmount, 2);
        }

        $creditAmount = $this->getRefundableAcmeInvoiceAmount((int) $invoice->invoiceid, (int) $serviceId);
        if ($creditAmount <= 0)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter(
                'SSLCENTER WHMCS: Skipped ACME refund credit for invoice #' . (int) $invoice->invoiceid
                . ' (service #' . (int) $serviceId . ') because refundable ACME items amount is 0.'
            );
            return 0.00;
        }

        $adminUserName = main\eHelpers\Admin::getAdminUserName();
        $creditDescription = $creditPrefix;
        if (!empty($reason))
        {
            $creditDescription .= '. Reason: ' . $reason;
        }

        $result = localAPI('AddCredit', [
            'clientid'    => (int) $invoice->userid,
            'description' => $creditDescription,
            'amount'      => number_format($creditAmount, 2, '.', ''),
        ], $adminUserName);

        if (!isset($result['result']) || $result['result'] !== 'success')
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: Unable to add refund credit for invoice #' . (int) $invoice->invoiceid . ' (service #' . (int) $serviceId . ').');
            return 0.00;
        }

        main\eHelpers\Whmcs::savelogActivitySSLCenter(
            'SSLCENTER WHMCS: Credit refund added for invoice #' . (int) $invoice->invoiceid
            . ' (service #' . (int) $serviceId . ', amount: ' . number_format($creditAmount, 2, '.', '') . ').'
            . (!empty($reason) ? ' Reason: ' . $reason : '')
        );

        return round((float) $creditAmount, 2);
    }

    private function getLatestPaidServiceInvoiceForRefund($serviceId)
    {
        $baseQuery = Capsule::table('tblinvoiceitems')
            ->select([
                'tblinvoiceitems.invoiceid',
                'tblinvoiceitems.description',
                'tblinvoices.userid',
                'tblinvoices.total',
                'tblinvoices.datepaid',
            ])
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->where('tblinvoices.datepaid', '>', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->where('tblinvoiceitems.relid', (int)$serviceId)
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoices.status', 'Paid');

        $renewalInvoice = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('tblinvoiceitems.description', 'LIKE', '%ACME Subscription Renewal%');
            })
            ->orderBy('tblinvoices.datepaid', 'DESC')
            ->first();

        if ($renewalInvoice)
        {
            return $renewalInvoice;
        }

        return (clone $baseQuery)
            ->orderBy('tblinvoices.datepaid', 'DESC')
            ->first();
    }

    private function getRefundableAcmeInvoiceAmount($invoiceId, $serviceId)
    {
        $acmeProductIds = AcmeHelper::getProductIds();

        if (empty($acmeProductIds))
        {
            return 0.00;
        }

        $invoice     = \WHMCS\Billing\Invoice::find($invoiceId);
        $invoiceItem = $invoice->items()
            ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblinvoiceitems.invoiceid', (int)$invoiceId)
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoiceitems.relid', (int)$serviceId)
            ->where('tblproducts.servertype', 'SSLCENTERWHMCS')
            ->whereIn('tblproducts.configoption1', $acmeProductIds)
            ->select(['tblinvoiceitems.*'])
            ->first();

        $itemAmount     = $invoiceItem ? $invoiceItem->amount : 0.00;
        $clientsDetails = getClientsDetails($invoice->userid);
        $taxEnabled     = \WHMCS\Config\Setting::getValue("TaxEnabled");

        if ($invoiceItem->taxed && $taxEnabled && !$clientsDetails["taxexempt"])
        {
            $taxRate  = $invoice->taxRate1;
            $taxRate2 = $invoice->taxRate2;

            if (round($taxRate, 2) == $taxRate)
            {
                $taxRate = \format_as_currency($taxRate);
            }
            if (round($taxRate2, 2) == $taxRate2)
            {
                $taxRate2 = \format_as_currency($taxRate2);
            }

            $taxCalculator = new Tax();
            $taxCalculator->setIsInclusive(\WHMCS\Config\Setting::getValue("TaxType") == "Inclusive")->setIsCompound(\WHMCS\Config\Setting::getValue("TaxL2Compound"));

            if (is_numeric($taxRate))
            {
                $taxCalculator->setLevel1Percentage($taxRate);
            }
            if (is_numeric($taxRate2))
            {
                $taxCalculator->setLevel2Percentage($taxRate2);
            }

            $tax = $tax2 = 0;

            $taxCalculator->setTaxBase($invoiceItem->amount);
            $tax          += $taxCalculator->getLevel1TaxTotal();
            $tax2         += $taxCalculator->getLevel2TaxTotal();
            $itemSubtotal = $taxCalculator->getTotalBeforeTaxes();
            $itemAmount   = $itemSubtotal + $tax + $tax2;
        }

        return round((float)$itemAmount, 2);
    }

    private function extractDomainsFromInput($domains)
    {
        $items = preg_split('/[\s,;]+/', trim((string) $domains));
        $items = array_filter(array_map('trim', $items));
        $items = array_values(array_unique(array_map('strtolower', $items)));

        return $items;
    }

    private function getAcmeDomainLimits(array $params)
    {
        $singleIncluded = $this->getAcmeIncludedSans($params, 'single');
        $singleBought = isset($params['configoptions']['sans_count']) ? (int) $params['configoptions']['sans_count'] : 0;
        $singleLimit = $singleIncluded + $singleBought;

        $wildcardIncluded = $this->getAcmeIncludedSans($params, 'wildcard');
        $wildcardBought = isset($params['configoptions']['sans_wildcard_count']) ? (int) $params['configoptions']['sans_wildcard_count'] : 0;
        $wildcardLimit = $wildcardIncluded + $wildcardBought;

        return [
            'single' => $singleLimit,
            'wildcard' => $wildcardLimit,
        ];
    }

    private function getAcmeIncludedSans(array $params, $sanType = 'single')
    {
        $key = ($sanType === 'wildcard') ? 'configoption8' : 'configoption4';
        if (isset($params[$key]) && is_numeric($params[$key]))
        {
            return (int) $params[$key];
        }

        $pid = isset($params['pid']) ? (int) $params['pid'] : 0;
        if ($pid <= 0)
        {
            return 0;
        }

        $column = ($sanType === 'wildcard') ? 'configoption8' : 'configoption4';
        $product = Capsule::table('tblproducts')->select([$column])->where('id', $pid)->first();
        if ($product && isset($product->{$column}) && is_numeric($product->{$column}))
        {
            return (int) $product->{$column};
        }

        return 0;
    }

    private function validateAcmeDomain($domain, $domainType = 'single')
    {
        $domain = strtolower(trim((string) $domain));

        if ($domainType === 'single' && strpos($domain, '*.') === 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSingleDomainsOnlyRegularDomains'));
        }

        if ($domainType === 'wildcard')
        {
            if (strpos($domain, '*.') !== 0)
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acmeWildcardDomainsOnlyWildcardDomains'));
            }
            $domain = substr($domain, 2);
        }

        if (\MGModule\SSLCENTERWHMCS\eHelpers\Domains::validateDomain($domain) !== true)
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeInvalidDomain'), $domain));
        }
    }

    private function getActiveAcmeDomainsCount($serviceId, $tableName)
    {
        $activeDomains = Capsule::table($tableName)
            ->select(['domain_type'])
            ->where('service_id', (int) $serviceId)
            ->get();

        $single = 0;
        $wildcard = 0;
        foreach ($activeDomains as $domainRow)
        {
            if (isset($domainRow->domain_type) && strtolower((string) $domainRow->domain_type) === 'wildcard')
            {
                $wildcard++;
            }
            else
            {
                $single++;
            }
        }

        return [
            'single'   => $single,
            'wildcard' => $wildcard,
        ];
    }
}
