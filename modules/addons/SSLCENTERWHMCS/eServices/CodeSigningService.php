<?php

namespace MGModule\SSLCENTERWHMCS\eServices;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use MGModule\SSLCENTERWHMCS\eHelpers\CodeSigningProduct;
use MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct;
use MGModule\SSLCENTERWHMCS\mgLibs\Lang;

/**
 * Hardware code signing certificate orders through GoGetSSL API v2 (/certificates/cs).
 *
 * tblsslorders: remoteid = API order ID (order.id), status Awaiting Configuration ->
 * Configuration Submitted -> Completed (certificate active). configdata keys:
 * cs_item_id (certificate ID used by /certificates/cs/{id}), cs_order_id, ssl_status, cs_term,
 * cs_method (ship-token / existing-token / hsm), cs_token_model, cs_shipping, cs_csr_provided,
 * cs_organization, partner_order_id, valid_from, valid_till, domain (organization name).
 */
class CodeSigningService
{
    /**
     * Places the code signing order.
     *
     * @param array $params WHMCS service params (serviceid, userid, pid, configoption*, configoptions, model...)
     * @param array $input  method, shipping, model, csr, organization, administrator, shipping_contact (arrays)
     * @return \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL
     */
    public static function create(array $params, array $input)
    {
        $serviceId = (int) $params['serviceid'];

        $sslService = (new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL())->getByServiceId($serviceId);
        if (is_null($sslService))
        {
            throw new Exception(Lang::absoluteT('createNotInitialized'));
        }

        if ((int) $sslService->getConfigdataKey('cs_item_id') > 0 || (!empty($sslService->remoteid) && $sslService->status !== 'Awaiting Configuration'))
        {
            throw new Exception(Lang::absoluteT('csAlreadyOrdered'));
        }

        $apiProductId = isset($params['configoption1']) ? (int) $params['configoption1'] : 0;
        if (!CodeSigningProduct::isCodeSigningByApiProductId($apiProductId))
        {
            throw new Exception(Lang::absoluteT('csNotCodeSigningProduct'));
        }

        $apiProduct = (new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products())->getProduct($apiProductId);
        $apiProduct = ($apiProduct && (int) $apiProduct->id === $apiProductId) ? $apiProduct : null;

        $provisioning = static::buildProvisioning($params, $apiProduct, $input);

        $contacts = static::validateContacts(
            isset($input['organization']) ? (array) $input['organization'] : [],
            isset($input['administrator']) ? (array) $input['administrator'] : []
        );

        if ($provisioning['method'] === CodeSigningProduct::METHOD_SHIP_TOKEN)
        {
            $provisioning['shipping']['contact'] = static::validateShippingContact(
                isset($input['shipping_contact']) ? (array) $input['shipping_contact'] : [],
                $contacts
            );
        }

        $availableTerms = [];
        if ($apiProduct && method_exists($apiProduct, 'getPeriods') && isset($apiProduct->prices['vendor']))
        {
            $availableTerms = $apiProduct->getPeriods();
        }
        $billingCycle  = isset($params['model']->billingcycle) ? $params['model']->billingcycle : (isset($params['templatevars']['billingcycle']) ? $params['templatevars']['billingcycle'] : 'Annually');
        $oneTimeMonths = !empty($params['configoption7']) ? (int) $params['configoption7'] : (!empty($params['configoption2']) ? (int) $params['configoption2'] : 12);
        $term          = SmimeProduct::getTermForBillingCycle($billingCycle, $availableTerms, $oneTimeMonths);

        $payload = [
            'product'      => [
                'id'   => (string) $apiProductId,
                'term' => (int) $term,
            ],
            'provisioning' => $provisioning,
            'contacts'     => $contacts,
        ];

        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->createCodeSigning($payload);

        $orderId = isset($response['order']['id']) ? (int) $response['order']['id'] : 0;
        $item    = isset($response['items'][0]) ? (array) $response['items'][0] : [];
        $itemId  = isset($item['id']) ? (int) $item['id'] : 0;

        if ($orderId <= 0 || $itemId <= 0)
        {
            throw new Exception(Lang::absoluteT('csMissingOrderId'));
        }

        $organizationName = $contacts['organization']['name'];

        $sslService->setRemoteId($orderId);
        $sslService->status = 'Configuration Submitted';
        $sslService->setConfigdataKey('cs_order_id', $orderId);
        $sslService->setConfigdataKey('cs_item_id', $itemId);
        $sslService->setConfigdataKey('cs_term', (int) $term);
        $sslService->setConfigdataKey('cs_method', $provisioning['method']);
        $sslService->setConfigdataKey('cs_token_model', isset($provisioning['model']) ? $provisioning['model'] : '');
        $sslService->setConfigdataKey('cs_shipping', isset($provisioning['shipping']['id']) ? $provisioning['shipping']['id'] : '');
        $sslService->setConfigdataKey('cs_csr_provided', isset($provisioning['csr']));
        $sslService->setConfigdataKey('cs_organization', $organizationName);
        $sslService->setConfigdataKey('product_id', $apiProductId);
        $sslService->setConfigdataKey('domain', $organizationName);
        $sslService->setConfigdataKey('ordered_at', date('Y-m-d H:i:s'));
        static::applyDetails($sslService, $response);
        $sslService->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domain' => mb_substr($organizationName, 0, 255)]);

        static::log($sslService, 'info', 'Code signing certificate ordered for ' . $organizationName
            . ' (order #' . $orderId . ', ' . static::describeProvisioning($provisioning) . ').');

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
        $itemId = (int) $sslService->getConfigdataKey('cs_item_id');
        if ($itemId <= 0)
        {
            throw new Exception(Lang::absoluteT('csNotOrderedYet'));
        }

        $previousStatus = (string) $sslService->getConfigdataKey('ssl_status');

        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getCodeSigningDetails($itemId);
        static::applyDetails($sslService, $response);
        $sslService->setConfigdataKey('cs_refreshed_at', date('Y-m-d H:i:s'));
        $sslService->save();

        $status = (string) $sslService->getConfigdataKey('ssl_status');
        if ($status !== '' && $status !== $previousStatus)
        {
            static::log($sslService, in_array($status, ['failed', 'rejected'], true) ? 'error' : 'info',
                'Code signing certificate status changed to ' . $status . '.');

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
        $itemId = (int) $sslService->getConfigdataKey('cs_item_id');
        if ($itemId <= 0)
        {
            throw new Exception(Lang::absoluteT('csNotOrderedYet'));
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->cancelCodeSigning($itemId, $reason);

        $sslService->setSSLStatus('cancelled');
        $sslService->save();

        static::log($sslService, 'info', 'Code signing certificate cancelled.');
    }

    public static function isCodeSigningOrder($sslService)
    {
        return $sslService && (int) $sslService->getConfigdataKey('cs_item_id') > 0;
    }

    /**
     * Provisioning methods the client may choose for the service: the method of the "Certificate Delivery"
     * configurable option when the product has one, otherwise every method the API product offers.
     *
     * @return array ['methods' => string[], 'fixed' => ['method' => .., 'shipping' => ..] | null, 'shipping_options' => array]
     */
    public static function getAvailableProvisioning(array $params, $apiProduct)
    {
        $apiMethods = ($apiProduct && !empty($apiProduct->cs_methods)) ? array_values((array) $apiProduct->cs_methods) : CodeSigningProduct::METHODS;
        $apiMethods = array_values(array_intersect(CodeSigningProduct::METHODS, $apiMethods));

        $shippingOptions = [];
        foreach (($apiProduct && !empty($apiProduct->cs_shipping_options)) ? (array) $apiProduct->cs_shipping_options : [] as $option)
        {
            $option = (array) $option;
            if (!empty($option['id']))
            {
                $shippingOptions[] = $option;
            }
        }

        $fixed = CodeSigningProduct::getProvisioningFromConfigOptions($params);
        if ($fixed !== null && in_array($fixed['method'], $apiMethods, true))
        {
            return ['methods' => [$fixed['method']], 'fixed' => $fixed, 'shipping_options' => $shippingOptions];
        }

        return ['methods' => $apiMethods, 'fixed' => null, 'shipping_options' => $shippingOptions];
    }

    private static function buildProvisioning(array $params, $apiProduct, array $input)
    {
        $available = static::getAvailableProvisioning($params, $apiProduct);

        if ($available['fixed'] !== null)
        {
            $method   = $available['fixed']['method'];
            $shipping = $available['fixed']['shipping'];
        }
        else
        {
            $method   = isset($input['method']) ? strtolower(trim((string) $input['method'])) : '';
            $shipping = CodeSigningProduct::DEFAULT_SHIPPING;
        }

        if (!in_array($method, $available['methods'], true))
        {
            throw new Exception(Lang::absoluteT('csInvalidMethod'));
        }

        $provisioning = ['method' => $method];

        if ($method === CodeSigningProduct::METHOD_EXISTING_TOKEN)
        {
            $model = isset($input['model']) ? strtoupper(trim((string) $input['model'])) : '';
            if (!isset(CodeSigningProduct::TOKEN_MODELS[$model]))
            {
                throw new Exception(Lang::absoluteT('csTokenModelRequired'));
            }
            $provisioning['model'] = $model;
        }
        else if ($method === CodeSigningProduct::METHOD_HSM)
        {
            $csr = isset($input['csr']) ? trim((string) $input['csr']) : '';
            if (strpos($csr, '-----BEGIN CERTIFICATE REQUEST-----') === false || strpos($csr, '-----END CERTIFICATE REQUEST-----') === false
                || @openssl_csr_get_subject($csr) === false)
            {
                throw new Exception(Lang::absoluteT('csInvalidCsr'));
            }
            $provisioning['csr'] = $csr;
        }
        else
        {
            $shippingIds = array_map(function ($o) { return strtoupper((string) $o['id']); }, $available['shipping_options']);
            if (!empty($shippingIds) && !in_array($shipping, $shippingIds, true))
            {
                throw new Exception(sprintf(Lang::absoluteT('csShippingNotAvailable'), htmlspecialchars($shipping, ENT_QUOTES)));
            }
            $provisioning['shipping'] = ['id' => $shipping];
        }

        return $provisioning;
    }

    /**
     * Organization + administrator contacts (all fields the API marks as required).
     */
    public static function validateContacts(array $organization, array $administrator)
    {
        $addressFields = ['address_line_1', 'address_line_2', 'city', 'region', 'country', 'postal_code'];

        $org = [];
        foreach (array_merge(['name', 'phone'], $addressFields) as $field)
        {
            $org[$field] = isset($organization[$field]) ? trim((string) $organization[$field]) : '';
        }

        $admin = [];
        foreach (array_merge(['first_name', 'last_name', 'title', 'email', 'phone'], $addressFields) as $field)
        {
            $admin[$field] = isset($administrator[$field]) ? trim((string) $administrator[$field]) : '';
        }

        // administrator at the organization address unless an own address is given
        if (!empty($administrator['same_address']) || $admin['address_line_1'] === '')
        {
            foreach ($addressFields as $field)
            {
                $admin[$field] = $org[$field];
            }
        }

        foreach (['name', 'phone', 'address_line_1', 'city', 'region', 'country', 'postal_code'] as $field)
        {
            if ($org[$field] === '')
            {
                throw new Exception(sprintf(Lang::absoluteT('csFieldRequired'), Lang::absoluteT('csOrg_' . $field)));
            }
        }

        foreach (['first_name', 'last_name', 'title', 'email', 'phone', 'address_line_1', 'city', 'region', 'country', 'postal_code'] as $field)
        {
            if ($admin[$field] === '')
            {
                throw new Exception(sprintf(Lang::absoluteT('csFieldRequired'), Lang::absoluteT('csAdmin_' . $field)));
            }
        }

        if (filter_var($admin['email'], FILTER_VALIDATE_EMAIL) === false)
        {
            throw new Exception(sprintf(Lang::absoluteT('csInvalidEmail'), htmlspecialchars($admin['email'], ENT_QUOTES)));
        }

        $org['country']   = static::normalizeCountry($org['country']);
        $admin['country'] = static::normalizeCountry($admin['country']);

        return [
            'organization'  => $org,
            'administrator' => $admin,
        ];
    }

    /**
     * Token recipient: the administrator at the organization address unless another address is given.
     */
    public static function validateShippingContact(array $contact, array $contacts)
    {
        $fields = ['first_name', 'last_name', 'address_line_1', 'address_line_2', 'city', 'region', 'country', 'postal_code'];

        $result = [];
        foreach ($fields as $field)
        {
            $result[$field] = isset($contact[$field]) ? trim((string) $contact[$field]) : '';
        }

        if (!empty($contact['same_address']) || $result['address_line_1'] === '')
        {
            $result = [
                'first_name'     => $contacts['administrator']['first_name'],
                'last_name'      => $contacts['administrator']['last_name'],
                'address_line_1' => $contacts['organization']['address_line_1'],
                'address_line_2' => $contacts['organization']['address_line_2'],
                'city'           => $contacts['organization']['city'],
                'region'         => $contacts['organization']['region'],
                'country'        => $contacts['organization']['country'],
                'postal_code'    => $contacts['organization']['postal_code'],
            ];
        }

        foreach (['first_name', 'last_name', 'address_line_1', 'city', 'region', 'country', 'postal_code'] as $field)
        {
            if ($result[$field] === '')
            {
                throw new Exception(sprintf(Lang::absoluteT('csFieldRequired'), Lang::absoluteT('csShip_' . $field)));
            }
        }

        $result['country'] = static::normalizeCountry($result['country']);

        return $result;
    }

    public static function describeProvisioning(array $provisioning)
    {
        switch ($provisioning['method'])
        {
            case CodeSigningProduct::METHOD_SHIP_TOKEN:
                return 'new token, ' . strtolower(isset($provisioning['shipping']['id']) ? $provisioning['shipping']['id'] : CodeSigningProduct::DEFAULT_SHIPPING) . ' shipping';
            case CodeSigningProduct::METHOD_EXISTING_TOKEN:
                return 'existing token ' . (isset($provisioning['model']) ? $provisioning['model'] : '');
            case CodeSigningProduct::METHOD_HSM:
                return 'own HSM (CSR)';
        }

        return (string) $provisioning['method'];
    }

    private static function normalizeCountry($country)
    {
        $country = strtoupper(trim((string) $country));
        if (!preg_match('/^[A-Z]{2}$/', $country))
        {
            throw new Exception(sprintf(Lang::absoluteT('csInvalidCountry'), htmlspecialchars($country, ENT_QUOTES)));
        }

        return $country;
    }

    /**
     * Saves status, CA order ID and validity from an API create / details response.
     */
    private static function applyDetails($sslService, $response)
    {
        $status = isset($response['order']['status']) ? strtolower((string) $response['order']['status']) : '';
        $item   = isset($response['items'][0]) ? (array) $response['items'][0] : [];

        if ($status !== '')
        {
            $sslService->setSSLStatus($status);
        }

        if (!empty($item['vendor']['id']))
        {
            $sslService->setConfigdataKey('partner_order_id', (string) $item['vendor']['id']);
        }
        if (!empty($item['vendor']['status']))
        {
            $sslService->setConfigdataKey('cs_vendor_status', (string) $item['vendor']['status']);
        }

        $subscription = isset($item['subscription']) ? (array) $item['subscription'] : [];
        if (!empty($subscription['begin']))
        {
            $sslService->setConfigdataKey('valid_from', SmimeService::formatApiDate($subscription['begin']));
        }
        if (!empty($subscription['end']))
        {
            $sslService->setConfigdataKey('valid_till', SmimeService::formatApiDate($subscription['end']));
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
