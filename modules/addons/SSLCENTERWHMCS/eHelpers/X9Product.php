<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * DigiCert X9 PKI for TLS: an OV TLS certificate that exists in GoGetSSL API v2 only. It is ordered with
 * POST /certificates/tls (with the key usage choices "usages"); everything after the order (status, files,
 * validation, reissue, cancel) uses the API v1 calls of the regular SSL flow with the same order ID.
 */
class X9Product
{
    const SUPPORTED_PRODUCTS = [
        192 => 'X9 PKI',
    ];

    /** configdata key of the key usage choices of a service */
    const CONFIGDATA_USAGES = 'x9_usages';

    /** form values of the extended key usage choice */
    const USAGE_SERVER_CLIENT = 'server_client';
    const USAGE_SERVER        = 'server';
    const USAGE_CLIENT        = 'client';

    public static function isX9ByApiProductId($apiProductId)
    {
        return isset(self::SUPPORTED_PRODUCTS[(int) $apiProductId]);
    }

    public static function getAllProductIds()
    {
        return array_keys(self::SUPPORTED_PRODUCTS);
    }

    public static function isX9ByServiceParams(array $params)
    {
        return isset($params['configoption1']) && static::isX9ByApiProductId($params['configoption1']);
    }

    public static function isX9ByServiceId($serviceId)
    {
        try
        {
            $apiProductId = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->where('tblhosting.id', (int) $serviceId)
                ->where('tblproducts.servertype', 'SSLCENTERWHMCS')
                ->value('tblproducts.configoption1');
        }
        catch (\Exception $e)
        {
            return false;
        }

        return static::isX9ByApiProductId($apiProductId);
    }

    /**
     * Key usage choices from the configuration form: the extended key usage choice (server_client / server /
     * client) and the key encipherment flag. Default: server and client authentication, no key encipherment
     * (the product defaults).
     *
     * @return array ['server_authentication' => bool, 'client_authentication' => bool, 'key_encipherment' => bool]
     */
    public static function usagesFromInput($usageChoice, $keyEncipherment)
    {
        $usageChoice = strtolower(trim((string) $usageChoice));
        if (!in_array($usageChoice, [self::USAGE_SERVER_CLIENT, self::USAGE_SERVER, self::USAGE_CLIENT], true))
        {
            $usageChoice = self::USAGE_SERVER_CLIENT;
        }

        return [
            'server_authentication' => $usageChoice !== self::USAGE_CLIENT,
            'client_authentication' => $usageChoice !== self::USAGE_SERVER,
            'key_encipherment'      => static::toBool($keyEncipherment),
        ];
    }

    /**
     * Usages as stored (configdata object or array) or returned by the API, normalized; null when none.
     */
    public static function normalizeUsages($usages)
    {
        if (is_object($usages))
        {
            $usages = json_decode(json_encode($usages), true);
        }
        if (!is_array($usages) || empty($usages))
        {
            return null;
        }

        $normalized = [
            'server_authentication' => !empty($usages['server_authentication']),
            'client_authentication' => !empty($usages['client_authentication']),
            'key_encipherment'      => !empty($usages['key_encipherment']),
        ];
        if (!$normalized['server_authentication'] && !$normalized['client_authentication'])
        {
            // the API requires one of them: the product defaults
            $normalized['server_authentication'] = true;
            $normalized['client_authentication'] = true;
        }

        return $normalized;
    }

    public static function getUsagesOfService($sslService)
    {
        if (!$sslService || !method_exists($sslService, 'getConfigdataKey'))
        {
            return null;
        }

        return static::normalizeUsages($sslService->getConfigdataKey(self::CONFIGDATA_USAGES));
    }

    /**
     * True when the certificate can secure a web server (server authentication).
     */
    public static function allowsServerInstallation($usages)
    {
        $usages = static::normalizeUsages($usages);

        return $usages === null || $usages['server_authentication'];
    }

    /**
     * Human readable key usage, e.g. "Server and client authentication, key encipherment".
     *
     * @param callable|null $translate function ($langKey, $default) for translated labels
     */
    public static function describeUsages($usages, $translate = null)
    {
        $usages = static::normalizeUsages($usages);
        if ($usages === null)
        {
            return '';
        }

        $t = function ($key, $default) use ($translate) {
            if (is_callable($translate))
            {
                $value = call_user_func($translate, $key, $default);
                if (is_string($value) && $value !== '' && $value !== $key)
                {
                    return $value;
                }
            }
            return $default;
        };

        if ($usages['server_authentication'] && $usages['client_authentication'])
        {
            $text = $t('x9UsageServerClient', 'Server and client authentication');
        }
        elseif ($usages['server_authentication'])
        {
            $text = $t('x9UsageServer', 'Server authentication only');
        }
        else
        {
            $text = $t('x9UsageClient', 'Client authentication only');
        }

        $text .= ', ' . $t('x9DigitalSignature', 'digital signature');
        if ($usages['key_encipherment'])
        {
            $text .= ', ' . $t('x9KeyEncipherment', 'key encipherment / key agreement');
        }

        return $text;
    }

    /**
     * Maps the GoGetSSL API v2 X9 product to the shape of an API v1 product list entry (DigiCert OV,
     * single-name SANs, no wildcards), so the Products Creator, Products Configuration, price updater and
     * the SSL configuration forms can use it.
     */
    public static function toV1Product(array $v2Product)
    {
        $vendorPrices = [];
        $v2Prices     = [];
        $sanPrices    = [];
        foreach ((isset($v2Product['prices']) && is_array($v2Product['prices']) ? $v2Product['prices'] : []) as $price)
        {
            $term = isset($price['term']) ? (int) $price['term'] : 0;
            $base = isset($price['base']['single']['selling']) && is_numeric($price['base']['single']['selling'])
                ? (float) $price['base']['single']['selling'] : null;
            if ($term <= 0 || $base === null)
            {
                continue;
            }

            $vendorPrices[$term] = $base;
            $v2Prices[] = [
                'term'    => $term,
                'selling' => $base,
                'retail'  => isset($price['base']['single']['retail']) && is_numeric($price['base']['single']['retail'])
                    ? (float) $price['base']['single']['retail'] : null,
            ];
            if (isset($price['san']['single']['selling']) && is_numeric($price['san']['single']['selling']))
            {
                $sanPrices[] = [
                    'term'    => $term,
                    'selling' => (float) $price['san']['single']['selling'],
                    'retail'  => isset($price['san']['single']['retail']) && is_numeric($price['san']['single']['retail'])
                        ? (float) $price['san']['single']['retail'] : null,
                ];
            }
        }
        ksort($vendorPrices);
        if (empty($vendorPrices))
        {
            $vendorPrices = [12 => 0];
        }

        $id  = (int) $v2Product['id'];
        $dcv = isset($v2Product['dcv']) && is_array($v2Product['dcv']) ? array_map('strtolower', $v2Product['dcv']) : ['email', 'dns-txt', 'http', 'https'];
        $san = isset($v2Product['san']) && is_array($v2Product['san']) ? $v2Product['san'] : [];

        $sanVendor = [];
        foreach ($sanPrices as $sanPrice)
        {
            $sanVendor[$sanPrice['term']] = $sanPrice['selling'];
        }

        return [
            'id'                   => $id,
            'product'              => !empty($v2Product['label']) ? (string) $v2Product['label'] : self::SUPPORTED_PRODUCTS[$id],
            'product_type'         => 'business_validation',
            'category'             => 'tls',
            'x9'                   => 1,
            'brand'                => isset($v2Product['brand']) ? strtolower((string) $v2Product['brand']) : 'digicert',
            'vendor'               => isset($v2Product['vendor']) ? (string) $v2Product['vendor'] : 'DIGICERT',
            'validation'           => isset($v2Product['validation']) ? $v2Product['validation'] : 'OV',
            'description'          => isset($v2Product['description']) ? $v2Product['description'] : null,
            'currency'             => isset($v2Product['currency']) ? $v2Product['currency'] : null,
            'max_period'           => max(array_keys($vendorPrices)),
            'org_required'         => 1,
            'san_enabled'          => (!isset($san['single_allowed']) || !empty($san['single_allowed'])) ? 1 : 0,
            'wildcard_san_enabled' => 0,
            'wildcard_enabled'     => 0,
            'unlimited_licencing'  => 1,
            'claim_ev_enabled'     => 0,
            'trust_logo'           => 0,
            'multi_domain'         => 1,
            'dcv_email'            => in_array('email', $dcv, true),
            'dcv_dns'              => in_array('dns-txt', $dcv, true) || in_array('dns-cname', $dcv, true) || in_array('dns', $dcv, true),
            'dcv_http'             => in_array('http', $dcv, true),
            'dcv_https'            => in_array('https', $dcv, true),
            'recheck_caa'          => false,
            'san_max'              => isset($san['max']) ? (int) $san['max'] : 249,
            'ip_allowed'           => !empty($v2Product['common_name']['ip_allowed']) || !empty($san['ip_allowed']),
            'prices'               => ['vendor' => $vendorPrices, 'san' => $sanVendor],
            'v2_prices'            => $v2Prices,
            'v2_san_prices'        => $sanPrices,
        ];
    }

    /**
     * Builds the API v2 "Create TLS Certificate" request from the API v1 order array the SSL configuration
     * forms and the renewal build (add_ssl_order fields). Keys used besides the API v1 fields:
     * x9_usages (key usage choices) and x9_renewal (['prior_certificate_id' => int, 'remaining_days' => int]).
     */
    public static function toV2Order(array $order)
    {
        $csr        = isset($order['csr']) ? trim((string) $order['csr']) : '';
        $commonName = static::getCommonNameFromCsr($csr);
        if ($commonName === '')
        {
            throw new \Exception('Invalid CSR: unable to extract the domain name (CN).');
        }

        $approverEmail = isset($order['approver_email']) ? trim((string) $order['approver_email']) : '';
        $mainMethod    = isset($order['dcv_method']) && $order['dcv_method'] !== '' ? $order['dcv_method']
            : (isset($order['approver_method']) ? $order['approver_method'] : 'email');

        $request = [
            'product'     => [
                'id'   => (string) (int) $order['product_id'],
                'term' => isset($order['period']) && (int) $order['period'] > 0 ? (int) $order['period'] : 12,
            ],
            'csr'         => $csr,
            'common_name' => [
                'name'              => $commonName,
                'validation_method' => static::toV2ValidationMethod($mainMethod, $approverEmail, $commonName),
            ],
        ];

        $sanNames   = isset($order['dns_names']) && $order['dns_names'] !== '' ? array_map('trim', explode(',', (string) $order['dns_names'])) : [];
        $sanMethods = isset($order['approver_emails']) && $order['approver_emails'] !== '' ? array_map('trim', explode(',', (string) $order['approver_emails'])) : [];
        $alternativeNames = [];
        $seen = [strtolower($commonName) => true];
        foreach ($sanNames as $i => $name)
        {
            $name = strtolower($name);
            if ($name === '' || isset($seen[$name]))
            {
                continue;
            }
            if (strpos($name, '*') !== false)
            {
                throw new \Exception('Wildcard names are not available for this certificate: ' . $name);
            }
            $seen[$name] = true;
            $method = isset($sanMethods[$i]) && $sanMethods[$i] !== '' ? $sanMethods[$i] : $mainMethod;
            $alternativeNames[] = [
                'name'              => $name,
                'validation_method' => static::toV2ValidationMethod($method, $approverEmail, $name),
            ];
        }
        if (!empty($alternativeNames))
        {
            $request['alternative_names'] = $alternativeNames;
        }

        if (!empty($order['x9_renewal']['prior_certificate_id']))
        {
            $request['renewal'] = [
                'prior_certificate_id' => (int) $order['x9_renewal']['prior_certificate_id'],
                'remaining_days'       => max(0, (int) $order['x9_renewal']['remaining_days']),
            ];
        }

        $usages = static::normalizeUsages(isset($order['x9_usages']) ? $order['x9_usages'] : null);
        $request['usages'] = $usages !== null ? $usages : static::usagesFromInput(self::USAGE_SERVER_CLIENT, false);

        $request['contacts'] = [
            'organization'  => [
                'name'           => static::value($order, 'org_name', static::value($order, 'admin_organization')),
                'address_line_1' => static::value($order, 'org_addressline1', static::value($order, 'admin_addressline1')),
                'address_line_2' => '',
                'city'           => static::value($order, 'org_city', static::value($order, 'admin_city')),
                'region'         => static::value($order, 'org_region', static::value($order, 'admin_region')),
                'country'        => static::countryCode(static::value($order, 'org_country', static::value($order, 'admin_country'))),
                'postal_code'    => static::value($order, 'org_postalcode', static::value($order, 'admin_postalcode')),
                'phone'          => static::value($order, 'org_phone', static::value($order, 'admin_phone')),
            ],
            'administrator' => static::person($order, 'admin'),
            'technical'     => static::person($order, 'tech'),
        ];

        return $request;
    }

    /**
     * API v2 create response as the API v1 add_ssl_order response the SSL forms read (order_id, items).
     */
    public static function toV1OrderResponse($response)
    {
        $response = is_array($response) ? $response : [];
        $orderId  = isset($response['order']['id']) ? (int) $response['order']['id'] : 0;
        if ($orderId <= 0)
        {
            throw new \Exception('The certificate order was not confirmed by the API. Please contact support.');
        }

        $items = isset($response['items']) && is_array($response['items']) ? $response['items'] : [];

        return [
            'success'   => true,
            'order_id'  => $orderId,
            'items'     => $items,
            'x9_usages' => isset($items[0]['usages']) ? $items[0]['usages'] : null,
            'api'       => 'v2',
        ];
    }

    /**
     * Renewal data for API v2 ("renewal" object): the previous certificate's item ID and the days it has left.
     * The API takes a renewal within the last 30 days of the previous certificate; otherwise (earlier, or
     * already expired) null: a plain new order.
     */
    public static function getRenewalData($sslService)
    {
        if (!$sslService || !method_exists($sslService, 'getConfigdataKey'))
        {
            return null;
        }

        $itemId = (int) $sslService->getConfigdataKey('tls_item_id');
        if ($itemId <= 0)
        {
            return null;
        }

        $validTill = (string) $sslService->getConfigdataKey('valid_till');
        $remaining = 0;
        if ($validTill !== '' && strpos($validTill, '0000') !== 0)
        {
            $end = strtotime($validTill);
            if ($end !== false)
            {
                $remaining = max(0, (int) ceil(($end - time()) / 86400));
            }
        }

        if ($remaining <= 0 || $remaining > 30)
        {
            return null;
        }

        return ['prior_certificate_id' => $itemId, 'remaining_days' => $remaining];
    }

    /**
     * "Additional Single domain SAN's" option of a WHMCS X9 product: maximum from the API (249) and the
     * price per SAN for each billing cycle from the API v2 SAN prices (term = cycle) x currency rate x price
     * rate. Only for products with automatic price download, unless $force.
     */
    public static function syncSanOption($productId, $apiProduct, $force = false)
    {
        try
        {
            $product = Capsule::table('tblproducts')->where('id', (int) $productId)->first(['configoption1', 'configoption5']);
            if (!$product || !static::isX9ByApiProductId($product->configoption1))
            {
                return;
            }

            $option = \MGModule\SSLCENTERWHMCS\eServices\ConfigurableOptionService::getForProduct((int) $productId);
            if (!$option || empty($option->id))
            {
                return;
            }

            $apiProduct = json_decode(json_encode($apiProduct), true);
            $sanMax     = isset($apiProduct['san_max']) && (int) $apiProduct['san_max'] > 0 ? (int) $apiProduct['san_max'] : 249;
            Capsule::table('tblproductconfigoptions')->where('id', (int) $option->id)->update(['qtymaximum' => $sanMax]);

            $autoPrices = (string) $product->configoption5 === '1';
            $sanPrices  = isset($apiProduct['v2_san_prices']) && is_array($apiProduct['v2_san_prices']) ? $apiProduct['v2_san_prices'] : [];
            if ((!$autoPrices && !$force) || empty($sanPrices))
            {
                return;
            }

            $byTerm = [];
            foreach ($sanPrices as $price)
            {
                if (isset($price['term'], $price['selling']) && is_numeric($price['selling']))
                {
                    $byTerm[(int) $price['term']] = (float) $price['selling'];
                }
            }

            $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
            $rate    = isset($apiConf->rate) && (float) $apiConf->rate > 0 ? (float) $apiConf->rate : 1;
            $cycles  = ['monthly' => 12, 'quarterly' => 3, 'semiannually' => 6, 'annually' => 12, 'biennially' => 24, 'triennially' => 36];

            $subIds = Capsule::table('tblproductconfigoptionssub')->where('configid', (int) $option->id)->pluck('id');
            foreach (Capsule::table('tblcurrencies')->get() as $currency)
            {
                $currencyRate = ((string) $currency->default === '1' || (float) $currency->rate <= 0) ? 1 : (float) $currency->rate;
                $pricing = [];
                foreach ($cycles as $cycle => $term)
                {
                    $pricing[$cycle] = isset($byTerm[$term]) ? number_format($byTerm[$term] * $currencyRate * $rate, 2, '.', '') : '0.00';
                }
                foreach ($subIds as $subId)
                {
                    Capsule::table('tblpricing')
                        ->where('type', 'configoptions')
                        ->where('relid', (int) $subId)
                        ->where('currency', (int) $currency->id)
                        ->update($pricing);
                }
            }
        }
        catch (\Exception $e)
        {
        }
    }

    public static function getCommonNameFromCsr($csr)
    {
        if ($csr === '' || !function_exists('openssl_csr_get_subject'))
        {
            return '';
        }
        $subject = @openssl_csr_get_subject($csr, true);

        return is_array($subject) && !empty($subject['CN']) ? strtolower(trim((string) (is_array($subject['CN']) ? reset($subject['CN']) : $subject['CN']))) : '';
    }

    /**
     * API v1 validation value (email / dns / http / https, or an approver email address) as an API v2
     * validation_method: http, https, dns-txt, dns-cname or the approver email address. An IP address cannot
     * be validated by email or DNS: it gets http.
     */
    public static function toV2ValidationMethod($method, $approverEmail, $name)
    {
        $method = trim((string) $method);
        if (strpos($method, '@') !== false)
        {
            $result = $method;
        }
        else
        {
            switch (strtolower($method))
            {
                case 'http':
                    $result = 'http';
                    break;
                case 'https':
                    $result = 'https';
                    break;
                case 'cname':
                case 'dns-cname':
                    $result = 'dns-cname';
                    break;
                case 'dns':
                case 'dns-txt':
                    $result = 'dns-txt';
                    break;
                default:
                    $result = $approverEmail !== '' ? $approverEmail : 'dns-txt';
            }
        }

        if (filter_var($name, FILTER_VALIDATE_IP) && ($result === 'dns-txt' || $result === 'dns-cname' || strpos($result, '@') !== false))
        {
            $result = 'http';
        }

        return $result;
    }

    private static function person(array $order, $prefix)
    {
        return [
            'first_name'     => static::value($order, $prefix . '_firstname'),
            'last_name'      => static::value($order, $prefix . '_lastname'),
            'title'          => static::value($order, $prefix . '_title', static::value($order, 'admin_title')),
            'email'          => static::value($order, $prefix . '_email'),
            'phone'          => static::value($order, $prefix . '_phone'),
            'address_line_1' => static::value($order, $prefix . '_addressline1', static::value($order, 'org_addressline1')),
            'address_line_2' => '',
            'city'           => static::value($order, $prefix . '_city', static::value($order, 'org_city')),
            'region'         => static::value($order, $prefix . '_region', static::value($order, 'org_region')),
            'country'        => static::countryCode(static::value($order, $prefix . '_country', static::value($order, 'org_country'))),
            'postal_code'    => static::value($order, $prefix . '_postalcode', static::value($order, 'org_postalcode')),
        ];
    }

    private static function value(array $order, $key, $default = '')
    {
        $value = isset($order[$key]) && is_scalar($order[$key]) ? trim((string) $order[$key]) : '';

        return $value !== '' ? $value : (string) $default;
    }

    private static function countryCode($country)
    {
        $country = trim((string) $country);
        if (strlen($country) > 2)
        {
            try
            {
                $code = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountryCodeByName($country);
                if (!empty($code))
                {
                    $country = $code;
                }
            }
            catch (\Exception $e)
            {
            }
        }

        return strtoupper($country);
    }

    private static function toBool($value)
    {
        if (is_bool($value))
        {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'on', 'yes', 'true'], true);
    }
}
