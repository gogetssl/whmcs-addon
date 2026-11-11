<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Hardware code signing certificates, ordered through GoGetSSL API v2 (/certificates/cs).
 * Cloud code signing (provisioning method "keylocker") is not supported.
 */
class CodeSigningProduct
{
    // GoGetSSL API product IDs supported by the module (API v2 category "cs")
    const SUPPORTED_PRODUCTS = [
        39  => 'DigiCert Code Signing SSL',
        89  => 'DigiCert EV Code Signing Certificate',
        137 => 'GoGetSSL Code Signing',
        138 => 'GoGetSSL EV Code Signing',
    ];

    const METHOD_SHIP_TOKEN     = 'ship-token';
    const METHOD_EXISTING_TOKEN = 'existing-token';
    const METHOD_HSM            = 'hsm';

    const METHODS = [self::METHOD_SHIP_TOKEN, self::METHOD_EXISTING_TOKEN, self::METHOD_HSM];

    // token models accepted for the "existing-token" method (values the API accepts; the API v2
    // documentation shows TOKEN_5110_*, the API itself requires SAFENET_5110_*)
    const TOKEN_MODELS = [
        'SAFENET_5110_FIPS'      => 'SafeNet eToken 5110 FIPS',
        'SAFENET_5110_CC'        => 'SafeNet eToken 5110 CC',
        'SAFENET_5110_PLUS_FIPS' => 'SafeNet eToken 5110+ FIPS',
    ];

    const DEFAULT_SHIPPING = 'STANDARD';

    // configurable option "Certificate Delivery" created by the Products Creator;
    // sub-option values: "ship-token:<SHIPPING ID>", "existing-token", "hsm"
    const OPTION_PROVISIONING = 'cs_provisioning';

    const STATUSES_FINAL = ['active', 'expired', 'cancelled', 'failed', 'rejected'];

    public static function isCodeSigningByApiProductId($apiProductId)
    {
        return isset(self::SUPPORTED_PRODUCTS[(int) $apiProductId]);
    }

    public static function getAllProductIds()
    {
        return array_keys(self::SUPPORTED_PRODUCTS);
    }

    public static function isCodeSigningByServiceParams(array $params)
    {
        $apiProductId = isset($params['configoption1']) ? $params['configoption1'] : null;

        if (!$apiProductId && isset($params['pid']))
        {
            $product = Capsule::table('tblproducts')
                ->select(['configoption1'])
                ->where('id', (int) $params['pid'])
                ->first();

            $apiProductId = $product ? $product->configoption1 : null;
        }

        if (!$apiProductId || !static::isCodeSigningByApiProductId((int) $apiProductId))
        {
            return false;
        }

        return empty($params['serviceid']) || !static::isLegacyV1Order((int) $params['serviceid']);
    }

    public static function isCodeSigningByServiceId($serviceId)
    {
        $product = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->select(['tblproducts.configoption1', 'tblproducts.servertype'])
            ->where('tblhosting.id', (int) $serviceId)
            ->first();

        if (!$product || $product->servertype !== 'SSLCENTERWHMCS')
        {
            return false;
        }

        return static::isCodeSigningByApiProductId((int) $product->configoption1) && !static::isLegacyV1Order((int) $serviceId);
    }

    /**
     * DigiCert code signing ordered through API v1 before API v2 support: it stays on the SSL (API v1) flow.
     */
    public static function isLegacyV1Order($serviceId)
    {
        $sslOrder = Capsule::table('tblsslorders')
            ->select(['remoteid', 'status', 'configdata'])
            ->where('serviceid', (int) $serviceId)
            ->first();

        return $sslOrder
            && !empty($sslOrder->remoteid)
            && $sslOrder->status !== 'Awaiting Configuration'
            && strpos((string) $sslOrder->configdata, '"cs_item_id"') === false;
    }

    /**
     * Service IDs of all API v2 code signing services of this module (to skip them in the API v1 order jobs).
     */
    public static function getServiceIds()
    {
        return Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->leftJoin('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->where('tblproducts.servertype', 'SSLCENTERWHMCS')
            ->whereIn('tblproducts.configoption1', array_map('strval', static::getAllProductIds()))
            ->where(function ($query) {
                // not a legacy API v1 order
                $query->whereNull('tblsslorders.remoteid')
                    ->orWhere('tblsslorders.remoteid', '')
                    ->orWhere('tblsslorders.status', 'Awaiting Configuration')
                    ->orWhere('tblsslorders.configdata', 'like', '%"cs_item_id"%');
            })
            ->pluck('tblhosting.id')
            ->map(function ($id) { return (int) $id; })
            ->toArray();
    }

    /**
     * Maps a GoGetSSL API v2 code signing product to the shape of an API v1 product list entry,
     * so the Products Creator, Products Configuration and price updater can use it.
     */
    public static function toV1Product(array $v2Product)
    {
        $vendorPrices = [];
        $v2Prices     = [];
        foreach ((isset($v2Product['prices']) && is_array($v2Product['prices']) ? $v2Product['prices'] : []) as $price)
        {
            $term    = isset($price['term']) ? (int) $price['term'] : 0;
            $selling = isset($price['selling']) && is_numeric($price['selling']) ? (float) $price['selling'] : null;
            if ($term <= 0 || $selling === null)
            {
                continue;
            }

            $vendorPrices[$term] = $selling;
            $v2Prices[] = [
                'term'    => $term,
                'selling' => $selling,
                'retail'  => isset($price['retail']) && is_numeric($price['retail']) ? (float) $price['retail'] : null,
            ];
        }
        ksort($vendorPrices);
        if (empty($vendorPrices))
        {
            $vendorPrices = [12 => 0];
        }

        $id = (int) $v2Product['id'];

        return [
            'id'                   => $id,
            'product'              => isset($v2Product['label']) ? (string) $v2Product['label'] : (isset(self::SUPPORTED_PRODUCTS[$id]) ? self::SUPPORTED_PRODUCTS[$id] : 'Code Signing #' . $id),
            'product_type'         => 'codesigning',
            'category'             => 'cs',
            'brand'                => isset($v2Product['brand']) ? strtolower((string) $v2Product['brand']) : '',
            'vendor'               => isset($v2Product['vendor']) ? (string) $v2Product['vendor'] : '',
            'validation'           => isset($v2Product['validation']) ? $v2Product['validation'] : null,
            'description'          => isset($v2Product['description']) ? $v2Product['description'] : null,
            'currency'             => isset($v2Product['currency']) ? $v2Product['currency'] : null,
            'max_period'           => max(array_keys($vendorPrices)),
            'org_required'         => 1,
            'san_enabled'          => 0,
            'wildcard_san_enabled' => 0,
            'wildcard_enabled'     => 0,
            'multi_domain'         => 0,
            'dcv_email'            => 0,
            'dcv_dns'              => 0,
            'dcv_http'             => 0,
            'dcv_https'            => 0,
            'prices'               => ['vendor' => $vendorPrices],
            'v2_prices'            => $v2Prices,
            'cs_methods'           => static::extractMethods($v2Product),
            'cs_shipping_options'  => static::extractShippingOptions($v2Product),
        ];
    }

    /**
     * Code signing product that is also in the API v1 list (DigiCert): the API v1 entry keeps its
     * other fields, the code signing data and prices come from API v2.
     */
    public static function mergeIntoV1Product(array $v1Product, array $csProduct)
    {
        $merged = array_merge($v1Product, [
            'category'             => 'cs',
            'product_type'         => 'codesigning',
            'validation'           => $csProduct['validation'],
            'org_required'         => 1,
            'san_enabled'          => 0,
            'wildcard_san_enabled' => 0,
            'v2_prices'            => $csProduct['v2_prices'],
            'cs_methods'           => $csProduct['cs_methods'],
            'cs_shipping_options'  => $csProduct['cs_shipping_options'],
        ]);

        if (!empty($csProduct['v2_prices']))
        {
            $merged['prices']     = $csProduct['prices'];
            $merged['max_period'] = $csProduct['max_period'];
        }

        return $merged;
    }

    /**
     * Supported hardware provisioning methods of an API v2 product (cloud "keylocker" removed).
     */
    public static function extractMethods(array $v2Product)
    {
        $methods = isset($v2Product['provisioning']['methods']) && is_array($v2Product['provisioning']['methods'])
            ? $v2Product['provisioning']['methods'] : self::METHODS;

        return array_values(array_intersect(self::METHODS, array_map('strval', $methods)));
    }

    /**
     * Token shipping options of an API v2 product: [['id' => 'STANDARD', 'label' => .., 'selling' => float], ...]
     */
    public static function extractShippingOptions(array $v2Product)
    {
        $options = [];
        foreach ((isset($v2Product['tokens']) && is_array($v2Product['tokens']) ? $v2Product['tokens'] : []) as $token)
        {
            foreach ((isset($token['shipping_options']) && is_array($token['shipping_options']) ? $token['shipping_options'] : []) as $option)
            {
                if (empty($option['id']))
                {
                    continue;
                }
                $id = strtoupper((string) $option['id']);
                $options[$id] = [
                    'id'      => $id,
                    'label'   => !empty($option['label']) ? (string) $option['label'] : ucfirst(strtolower($id)) . ' Shipping',
                    'selling' => isset($option['selling']) && is_numeric($option['selling']) ? (float) $option['selling'] : 0.0,
                ];
            }
        }

        // STANDARD first, then the others in the API order
        uksort($options, function ($a, $b) {
            if ($a === $b) { return 0; }
            if ($a === self::DEFAULT_SHIPPING) { return -1; }
            if ($b === self::DEFAULT_SHIPPING) { return 1; }
            return 0;
        });

        return array_values($options);
    }

    /**
     * Provisioning choice from the "Certificate Delivery" configurable option of the service:
     * ['method' => .., 'shipping' => .. (ship-token only)] or null when the product has no such option.
     */
    public static function getProvisioningFromConfigOptions(array $params)
    {
        $value = isset($params['configoptions'][self::OPTION_PROVISIONING]) ? trim((string) $params['configoptions'][self::OPTION_PROVISIONING]) : '';
        if ($value === '')
        {
            return null;
        }

        return static::parseProvisioningValue($value);
    }

    /**
     * "ship-token:EXPEDITED" -> ['method' => 'ship-token', 'shipping' => 'EXPEDITED'], null when not valid.
     */
    public static function parseProvisioningValue($value)
    {
        $parts  = explode(':', strtolower(trim((string) $value)), 2);
        $method = $parts[0];
        if (!in_array($method, self::METHODS, true))
        {
            return null;
        }

        return [
            'method'   => $method,
            'shipping' => $method === self::METHOD_SHIP_TOKEN
                ? (isset($parts[1]) && $parts[1] !== '' ? strtoupper($parts[1]) : self::DEFAULT_SHIPPING)
                : null,
        ];
    }
}
