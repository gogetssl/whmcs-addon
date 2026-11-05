<?php

namespace MGModule\SSLCENTERWHMCS\eServices;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct;
use MGModule\SSLCENTERWHMCS\mgLibs\Lang;

/**
 * S/MIME certificate orders through GoGetSSL API v2.
 *
 * tblsslorders: remoteid = API order ID (order.id), status Awaiting Configuration ->
 * Configuration Submitted -> Completed (certificate active). configdata keys:
 * smime_item_id (certificate ID used by /certificates/smime/{id}), smime_order_id, ssl_status,
 * smime_emails [{email, approved}], smime_term, smime_csr_provided, valid_from, valid_till, domain (first email).
 */
class SmimeService
{
    /**
     * Places the S/MIME order.
     *
     * @param array $params WHMCS service params (serviceid, userid, pid, configoption*, model...)
     * @param array $input  emails (array), csr (string, optional), organization / administrator (arrays, OV products)
     * @return \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL
     */
    public static function create(array $params, array $input)
    {
        $serviceId = (int) $params['serviceid'];

        $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);

        if (is_null($sslService))
        {
            throw new Exception(Lang::absoluteT('createNotInitialized'));
        }

        if ((int) $sslService->getConfigdataKey('smime_item_id') > 0 || (!empty($sslService->remoteid) && $sslService->status !== 'Awaiting Configuration'))
        {
            throw new Exception(Lang::absoluteT('smimeAlreadyOrdered'));
        }

        $apiProductId = isset($params['configoption1']) ? (int) $params['configoption1'] : 0;
        if (!SmimeProduct::isSmimeByApiProductId($apiProductId))
        {
            throw new Exception(Lang::absoluteT('smimeNotSmimeProduct'));
        }

        $emails = static::validateEmails(isset($input['emails']) ? $input['emails'] : []);

        $apiProduct = (new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products())->getProduct($apiProductId);
        $availableTerms = [];
        if ($apiProduct && (int) $apiProduct->id === $apiProductId && method_exists($apiProduct, 'getPeriods') && isset($apiProduct->prices['vendor']))
        {
            $availableTerms = $apiProduct->getPeriods();
        }

        $billingCycle = isset($params['model']->billingcycle) ? $params['model']->billingcycle : (isset($params['templatevars']['billingcycle']) ? $params['templatevars']['billingcycle'] : 'Annually');
        $oneTimeMonths = !empty($params['configoption7']) ? (int) $params['configoption7'] : (!empty($params['configoption2']) ? (int) $params['configoption2'] : 12);
        $term = SmimeProduct::getTermForBillingCycle($billingCycle, $availableTerms, $oneTimeMonths);

        $payload = [
            'product' => [
                'id'   => (string) $apiProductId,
                'term' => (int) $term,
            ],
            'emails' => $emails,
        ];

        $csr = isset($input['csr']) ? trim((string) $input['csr']) : '';
        if ($csr !== '')
        {
            if (strpos($csr, '-----BEGIN CERTIFICATE REQUEST-----') === false || strpos($csr, '-----END CERTIFICATE REQUEST-----') === false)
            {
                throw new Exception(Lang::absoluteT('smimeInvalidCsr'));
            }
            $payload['csr'] = $csr;
        }

        if (SmimeProduct::requiresOrganization($apiProductId, $apiProduct && (int) $apiProduct->id === $apiProductId ? $apiProduct : null))
        {
            $payload['contacts'] = static::validateContacts(
                isset($input['organization']) ? (array) $input['organization'] : [],
                isset($input['administrator']) ? (array) $input['administrator'] : []
            );
        }

        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->createSmime($payload);

        $orderId = isset($response['order']['id']) ? (int) $response['order']['id'] : 0;
        $item    = isset($response['items'][0]) ? (array) $response['items'][0] : [];
        $itemId  = isset($item['id']) ? (int) $item['id'] : 0;

        if ($orderId <= 0 || $itemId <= 0)
        {
            throw new Exception(Lang::absoluteT('smimeMissingOrderId'));
        }

        $sslService->setRemoteId($orderId);
        $sslService->status = 'Configuration Submitted';
        $sslService->setConfigdataKey('smime_order_id', $orderId);
        $sslService->setConfigdataKey('smime_item_id', $itemId);
        $sslService->setConfigdataKey('smime_term', (int) $term);
        $sslService->setConfigdataKey('smime_csr_provided', $csr !== '');
        $sslService->setConfigdataKey('product_id', $apiProductId);
        $sslService->setConfigdataKey('domain', $emails[0]);
        $sslService->setConfigdataKey('ordered_at', date('Y-m-d H:i:s'));
        static::applyDetails($sslService, $response);
        $sslService->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domain' => $emails[0]]);

        static::log($sslService, 'info', 'S/MIME certificate ordered for ' . implode(', ', $emails) . ' (order #' . $orderId . ').');

        return $sslService;
    }

    /**
     * Reads the certificate details from the API and saves them.
     *
     * @param \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL $sslService
     * @return array API response
     */
    public static function refresh($sslService)
    {
        $itemId = (int) $sslService->getConfigdataKey('smime_item_id');
        if ($itemId <= 0)
        {
            throw new Exception(Lang::absoluteT('smimeNotOrderedYet'));
        }

        $previousStatus = (string) $sslService->getConfigdataKey('ssl_status');

        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getSmimeDetails($itemId);
        static::applyDetails($sslService, $response);
        $sslService->setConfigdataKey('smime_refreshed_at', date('Y-m-d H:i:s'));
        $sslService->save();

        $status = (string) $sslService->getConfigdataKey('ssl_status');
        if ($status !== '' && $status !== $previousStatus)
        {
            static::log($sslService, in_array($status, ['failed', 'rejected'], true) ? 'error' : 'info',
                'S/MIME certificate status changed to ' . $status . '.');

            if ($status === 'active')
            {
                Capsule::table('tblsslorders')->where('id', $sslService->id)->update(['completiondate' => date('Y-m-d H:i:s')]);
            }
        }

        return $response;
    }

    /**
     * Cancels the certificate at GoGetSSL.
     *
     * @param \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL $sslService
     */
    public static function cancel($sslService, $reason)
    {
        $itemId = (int) $sslService->getConfigdataKey('smime_item_id');
        if ($itemId <= 0)
        {
            throw new Exception(Lang::absoluteT('smimeNotOrderedYet'));
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->cancelSmime($itemId, $reason);

        $sslService->setSSLStatus('cancelled');
        $sslService->save();

        static::log($sslService, 'info', 'S/MIME certificate cancelled.');
    }

    public static function isSmimeOrder($sslService)
    {
        return $sslService && (int) $sslService->getConfigdataKey('smime_item_id') > 0;
    }

    /**
     * Saves status, emails and validity from an API create / details response.
     */
    private static function applyDetails($sslService, $response)
    {
        $status = isset($response['order']['status']) ? strtolower((string) $response['order']['status']) : '';
        $item   = isset($response['items'][0]) ? (array) $response['items'][0] : [];

        if ($status !== '')
        {
            $sslService->setSSLStatus($status);
        }

        if (isset($item['emails']))
        {
            $sslService->setConfigdataKey('smime_emails', SmimeProduct::normalizeEmails($item['emails']));
        }

        if (!empty($item['vendor']['id']))
        {
            $sslService->setConfigdataKey('partner_order_id', (string) $item['vendor']['id']);
        }

        $subscription = isset($item['subscription']) ? (array) $item['subscription'] : [];
        // dates as the API gives them (its own time zone), not converted to the server time zone
        if (!empty($subscription['begin']))
        {
            $sslService->setConfigdataKey('valid_from', static::formatApiDate($subscription['begin']));
        }
        if (!empty($subscription['end']))
        {
            $sslService->setConfigdataKey('valid_till', static::formatApiDate($subscription['end']));
        }

        if ($status === 'active')
        {
            $sslService->status = 'Completed';
        }
        else if ($status !== '' && $sslService->status === 'Awaiting Configuration')
        {
            $sslService->status = 'Configuration Submitted';
        }
    }

    /**
     * ISO 8601 date from the API (e.g. 2026-11-05T00:00:00+02:00) -> "2026-11-05 00:00:00" in the API's offset.
     */
    public static function formatApiDate($value)
    {
        try
        {
            $date = new \DateTime((string) $value);
            return $date->format('Y-m-d H:i:s');
        }
        catch (\Exception $e)
        {
            return (string) $value;
        }
    }

    /**
     * @return string[] lower-case, unique, valid emails (first = certificate subject)
     */
    public static function validateEmails($emails)
    {
        if (is_string($emails))
        {
            $emails = preg_split('/[\s,;]+/', $emails);
        }

        $result = [];
        foreach ((array) $emails as $email)
        {
            $email = strtolower(trim((string) $email));
            if ($email === '')
            {
                continue;
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
            {
                throw new Exception(sprintf(Lang::absoluteT('smimeInvalidEmail'), htmlspecialchars($email, ENT_QUOTES)));
            }

            if (!in_array($email, $result, true))
            {
                $result[] = $email;
            }
        }

        if (empty($result))
        {
            throw new Exception(Lang::absoluteT('smimeEmailRequired'));
        }

        if (count($result) > SmimeProduct::MAX_EMAILS)
        {
            throw new Exception(sprintf(Lang::absoluteT('smimeTooManyEmails'), SmimeProduct::MAX_EMAILS));
        }

        return $result;
    }

    /**
     * Organization + administrator contacts for organization-validated products.
     */
    public static function validateContacts(array $organization, array $administrator)
    {
        $orgFields   = ['name', 'division', 'duns', 'lei', 'phone', 'country', 'region', 'city', 'postal_code', 'address_line_1', 'address_line_2'];
        $adminFields = ['first_name', 'last_name', 'title', 'organization', 'email', 'phone', 'country', 'region', 'city', 'postal_code', 'address_line_1', 'address_line_2'];

        $orgRequired   = ['name', 'phone', 'country', 'city', 'address_line_1'];
        $adminRequired = ['first_name', 'last_name', 'email', 'phone', 'country'];

        $org = [];
        foreach ($orgFields as $field)
        {
            $value = isset($organization[$field]) ? trim((string) $organization[$field]) : '';
            $org[$field] = $value !== '' ? $value : null;
        }

        $admin = [];
        foreach ($adminFields as $field)
        {
            $value = isset($administrator[$field]) ? trim((string) $administrator[$field]) : '';
            $admin[$field] = $value !== '' ? $value : null;
        }

        if ($admin['organization'] === null && $org['name'] !== null)
        {
            $admin['organization'] = $org['name'];
        }

        foreach ($orgRequired as $field)
        {
            if ($org[$field] === null)
            {
                throw new Exception(sprintf(Lang::absoluteT('smimeFieldRequired'), Lang::absoluteT('smimeOrg_' . $field)));
            }
        }

        foreach ($adminRequired as $field)
        {
            if ($admin[$field] === null)
            {
                throw new Exception(sprintf(Lang::absoluteT('smimeFieldRequired'), Lang::absoluteT('smimeAdmin_' . $field)));
            }
        }

        if (filter_var($admin['email'], FILTER_VALIDATE_EMAIL) === false)
        {
            throw new Exception(sprintf(Lang::absoluteT('smimeInvalidEmail'), htmlspecialchars($admin['email'], ENT_QUOTES)));
        }

        foreach (['country'] as $field)
        {
            $org[$field]   = strtoupper($org[$field]);
            $admin[$field] = strtoupper($admin[$field]);
        }

        return [
            'organization'  => $org,
            'administrator' => $admin,
        ];
    }

    private static function log($sslService, $type, $message)
    {
        try
        {
            (new \MGModule\SSLCENTERWHMCS\models\logs\Repository())->addLog((int) $sslService->userid, (int) $sslService->serviceid, $type, $message);
        }
        catch (\Exception $e)
        {
        }
    }
}
