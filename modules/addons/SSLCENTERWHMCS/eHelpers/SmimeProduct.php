<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * S/MIME (email signing) certificates, ordered through GoGetSSL API v2 (/certificates/smime).
 */
class SmimeProduct
{
    // S/MIME products are recognised by the GoGetSSL API v2 product list ("category": "smime");
    // their IDs are remembered in tblconfiguration (SETTING) whenever the list is read.
    const SETTING = 'sslcenter_smime_product_ids';

    // Known GoGetSSL API product IDs of S/MIME products. Used when GET /products does not list them
    // (the list returns no S/MIME products yet). "validation" OV = organization + administrator contacts.
    const KNOWN_PRODUCTS = [
        90  => ['label' => 'SMIME Mailbox Validation Multipurpose (MVM)',      'brand' => 'comodo',   'vendor' => 'SECTIGO',  'validation' => null],
        87  => ['label' => 'SMIME Mailbox Validation Strict (MVS)',            'brand' => 'comodo',   'vendor' => 'SECTIGO',  'validation' => null],
        88  => ['label' => 'SMIME Organization Validation Multipurpose (OVM)', 'brand' => 'comodo',   'vendor' => 'SECTIGO',  'validation' => 'OV'],
        171 => ['label' => 'Secure Email for Individual',                      'brand' => 'digicert', 'vendor' => 'DIGICERT', 'validation' => null],
        172 => ['label' => 'Secure Email for Business',                        'brand' => 'digicert', 'vendor' => 'DIGICERT', 'validation' => 'OV'],
    ];

    // Terms offered for known products without API prices (months); the API rejects a term the product does not have.
    const DEFAULT_TERMS = [12, 24, 36];

    /**
     * Known S/MIME products in the API v2 product list shape (no prices: set them in WHMCS).
     *
     * @return array[]
     */
    public static function getKnownV2Products()
    {
        $products = [];
        foreach (self::KNOWN_PRODUCTS as $id => $data)
        {
            $products[] = [
                'id'          => $id,
                'label'       => $data['label'],
                'description' => null,
                'type'        => 'certificate',
                'category'    => 'smime',
                'validation'  => $data['validation'],
                'vendor'      => $data['vendor'],
                'brand'       => $data['brand'],
                'prices'      => [],
                'known'       => true,
            ];
        }

        return $products;
    }

    /**
     * API v2 S/MIME products: the API list entries plus the known products the list does not have.
     *
     * @param array|null $v2Products GET /products response (null when it could not be read)
     */
    public static function mergeWithKnownProducts($v2Products)
    {
        $result = [];
        $ids    = [];
        if (is_array($v2Products))
        {
            foreach ($v2Products as $v2Product)
            {
                if (is_array($v2Product) && isset($v2Product['id'], $v2Product['category']) && $v2Product['category'] === 'smime')
                {
                    $result[] = $v2Product;
                    $ids[(int) $v2Product['id']] = true;
                }
            }
        }

        foreach (static::getKnownV2Products() as $known)
        {
            if (!isset($ids[$known['id']]))
            {
                $result[] = $known;
            }
        }

        return $result;
    }

    // Most emails (common name + alternative names) the order form accepts.
    // The API enforces the real per-product limit and returns an error above it.
    const MAX_EMAILS = 10;

    const STATUSES_FINAL = ['active', 'expired', 'cancelled', 'failed', 'rejected'];

    private static $productIds = null;

    public static function isSmimeByApiProductId($apiProductId)
    {
        $apiProductId = (int) $apiProductId;
        if ($apiProductId <= 0)
        {
            return false;
        }

        return in_array($apiProductId, static::getAllProductIds(), true);
    }

    /**
     * IDs of S/MIME products: remembered IDs plus the products marked "smime" in the cached product list.
     */
    public static function getAllProductIds()
    {
        if (self::$productIds !== null)
        {
            return self::$productIds;
        }

        $ids = array_merge(array_keys(self::KNOWN_PRODUCTS), static::getRememberedProductIds());
        try
        {
            if (Capsule::schema()->hasTable('mgfw_SSLCENTER_product_brand'))
            {
                $cached = Capsule::table('mgfw_SSLCENTER_product_brand')
                    ->where('data', 'like', '%"category":"smime"%')
                    ->pluck('pid')
                    ->toArray();
                foreach ($cached as $pid)
                {
                    $ids[] = (int) $pid;
                }
            }
        }
        catch (\Exception $e)
        {
        }

        self::$productIds = array_values(array_unique(array_filter($ids)));
        return self::$productIds;
    }

    /**
     * Saves the S/MIME product IDs found in the API v2 product list (kept even if the product cache is cleared).
     *
     * @param int[] $ids
     */
    public static function rememberProductIds(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', array_merge(static::getRememberedProductIds(), $ids)))));
        sort($ids);

        try
        {
            $value = implode(',', $ids);
            $row   = Capsule::table('tblconfiguration')->where('setting', self::SETTING)->first();
            if ($row)
            {
                if ((string) $row->value !== $value)
                {
                    Capsule::table('tblconfiguration')->where('setting', self::SETTING)->update(['value' => $value]);
                }
            }
            else
            {
                Capsule::table('tblconfiguration')->insert(['setting' => self::SETTING, 'value' => $value]);
            }
        }
        catch (\Exception $e)
        {
        }

        self::$productIds = null;
    }

    private static function getRememberedProductIds()
    {
        try
        {
            $row = Capsule::table('tblconfiguration')->where('setting', self::SETTING)->first();
        }
        catch (\Exception $e)
        {
            return [];
        }

        if (!$row || trim((string) $row->value) === '')
        {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $row->value))));
    }

    public static function isSmimeByServiceParams(array $params)
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

        return $apiProductId ? static::isSmimeByApiProductId((int) $apiProductId) : false;
    }

    public static function isSmimeByServiceId($serviceId)
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

        return static::isSmimeByApiProductId((int) $product->configoption1);
    }

    /**
     * Service IDs of all S/MIME services of this module (to skip them in the API v1 order jobs).
     */
    public static function getServiceIds()
    {
        return Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblproducts.servertype', 'SSLCENTERWHMCS')
            ->whereIn('tblproducts.configoption1', array_map('strval', static::getAllProductIds()))
            ->pluck('tblhosting.id')
            ->map(function ($id) { return (int) $id; })
            ->toArray();
    }

    /**
     * True when the product needs organization and administrator contacts.
     *
     * @param int   $apiProductId
     * @param mixed $apiProduct API product (object or array) - its "validation" is used when known
     */
    public static function requiresOrganization($apiProductId, $apiProduct = null)
    {
        $validation = null;
        if (is_array($apiProduct) && isset($apiProduct['validation']))
        {
            $validation = $apiProduct['validation'];
        }
        else if (is_object($apiProduct) && isset($apiProduct->validation))
        {
            $validation = $apiProduct->validation;
        }

        if (is_string($validation) && $validation !== '')
        {
            return in_array(strtoupper($validation), ['OV', 'EV'], true);
        }

        // no validation in the product list: decide by the product name
        // (e.g. "SMIME Organization Validation Multipurpose (OVM)", "Secure Email for Business")
        $name = '';
        foreach (['label', 'product', 'name'] as $key)
        {
            if (is_array($apiProduct) && !empty($apiProduct[$key]))
            {
                $name = (string) $apiProduct[$key];
                break;
            }
            if (is_object($apiProduct) && !empty($apiProduct->{$key}))
            {
                $name = (string) $apiProduct->{$key};
                break;
            }
        }

        return (bool) preg_match('/organi[sz]ation|business|\bOV[A-Z]?\b|sponsor/i', $name);
    }

    /**
     * Certificate term in months for the WHMCS billing cycle.
     *
     * @param string $billingCycle  WHMCS billing cycle
     * @param array  $availableTerms terms the API product offers (months)
     * @param int    $oneTimeMonths  product "months" setting, used for One Time / Free Account
     */
    public static function getTermForBillingCycle($billingCycle, array $availableTerms, $oneTimeMonths = 12)
    {
        $map = [
            'Monthly'       => 12,
            'Quarterly'     => 12,
            'Semi-Annually' => 12,
            'Annually'      => 12,
            'Biennially'    => 24,
            'Triennially'   => 36,
        ];

        $term = isset($map[$billingCycle]) ? $map[$billingCycle] : (int) $oneTimeMonths;
        if ($term <= 0)
        {
            $term = 12;
        }

        $availableTerms = array_values(array_filter(array_map('intval', $availableTerms)));
        if (empty($availableTerms) || in_array($term, $availableTerms, true))
        {
            return $term;
        }

        if (in_array(12, $availableTerms, true))
        {
            return 12;
        }

        sort($availableTerms);
        return $availableTerms[0];
    }

    /**
     * API v2 products (S/MIME, code signing) are sold only for the terms the API offers: the WHMCS billing
     * cycles without a matching term (monthly, quarterly, semi-annually, and biennially / triennially when the
     * product has no 24 / 36 month term) are disabled (-1.00) in a tblpricing row (array keyed by cycle).
     *
     * @param array $pricing      tblpricing values keyed by cycle
     * @param array $v2Prices     API v2 prices [{term, selling, retail}]
     */
    public static function restrictPricingToTerms(array $pricing, $v2Prices)
    {
        $terms = [];
        foreach ((array) $v2Prices as $price)
        {
            $price = (array) $price;
            if (isset($price['term']) && (int) $price['term'] > 0)
            {
                $terms[] = (int) $price['term'];
            }
        }
        if (empty($terms))
        {
            return $pricing;
        }

        $cycleTerms = [
            'monthly'      => 1,
            'quarterly'    => 3,
            'semiannually' => 6,
            'annually'     => 12,
            'biennially'   => 24,
            'triennially'  => 36,
        ];
        foreach ($cycleTerms as $cycle => $term)
        {
            if (!in_array($term, $terms, true))
            {
                $pricing[$cycle] = '-1.00';
            }
        }

        return $pricing;
    }

    /**
     * Disables (-1.00) the billing cycles of a WHMCS product that have no matching API term, for S/MIME and
     * code signing products with API v2 prices. Only recurring products: a One Time product keeps its price
     * in the "monthly" column, free products have none.
     */
    public static function restrictProductBillingCycles($productId)
    {
        try
        {
            $product = Capsule::table('tblproducts')
                ->select(['configoption1', 'paytype', 'servertype'])
                ->where('id', (int) $productId)
                ->first();
            if (!$product || $product->servertype !== 'SSLCENTERWHMCS' || $product->paytype !== 'recurring')
            {
                return;
            }

            $apiProductId = (int) $product->configoption1;
            if (!static::isSmimeByApiProductId($apiProductId) && !CodeSigningProduct::isCodeSigningByApiProductId($apiProductId))
            {
                return;
            }

            $apiProduct = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products::getInstance()->getProduct($apiProductId);
            if (!$apiProduct || (int) $apiProduct->id !== $apiProductId || empty($apiProduct->v2_prices))
            {
                return;
            }

            $cycles   = ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'];
            $result   = static::restrictPricingToTerms(array_fill_keys($cycles, ''), $apiProduct->v2_prices);
            $disabled = array_filter($result, function ($value) { return $value === '-1.00'; });
            if (empty($disabled))
            {
                return;
            }

            Capsule::table('tblpricing')
                ->where('type', 'product')
                ->where('relid', (int) $productId)
                ->update($disabled);
        }
        catch (\Exception $e)
        {
        }
    }

    /**
     * Maps a GoGetSSL API v2 S/MIME product to the shape of an API v1 product list entry,
     * so the Products Creator, Products Configuration and price updater can use it.
     */
    public static function toV1Product(array $v2Product)
    {
        $vendorPrices = [];
        $v2Prices     = [];
        $prices       = isset($v2Product['prices']) && is_array($v2Product['prices']) ? $v2Product['prices'] : [];

        foreach ($prices as $price)
        {
            $term = isset($price['term']) ? (int) $price['term'] : 0;
            if ($term <= 0)
            {
                continue;
            }

            $selling = isset($price['selling']) && is_numeric($price['selling']) ? (float) $price['selling'] : null;
            if ($selling === null)
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
        $terms = array_keys($vendorPrices);

        // no API prices (known product not in the API list): offer the default terms, prices are set in WHMCS
        if (empty($terms))
        {
            $terms = self::DEFAULT_TERMS;
            $vendorPrices = array_fill_keys($terms, 0);
        }

        return [
            'id'                   => (int) $v2Product['id'],
            'product'              => isset($v2Product['label']) ? (string) $v2Product['label'] : ('S/MIME #' . (int) $v2Product['id']),
            'product_type'         => 'smime',
            'category'             => 'smime',
            'brand'                => isset($v2Product['brand']) ? strtolower((string) $v2Product['brand']) : '',
            'vendor'               => isset($v2Product['vendor']) ? (string) $v2Product['vendor'] : '',
            'validation'           => isset($v2Product['validation']) ? $v2Product['validation'] : null,
            'description'          => isset($v2Product['description']) ? $v2Product['description'] : null,
            'currency'             => isset($v2Product['currency']) ? $v2Product['currency'] : null,
            'max_period'           => !empty($terms) ? max($terms) : 12,
            'org_required'         => static::requiresOrganization((int) $v2Product['id'], $v2Product) ? 1 : 0,
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
        ];
    }

    /**
     * S/MIME product that is also in the API v1 list: the API v1 entry keeps its other fields, the
     * S/MIME data comes from API v2 (terms + prices when API v2 has them, no SANs, validation).
     */
    public static function mergeIntoV1Product(array $v1Product, array $smimeProduct)
    {
        $merged = array_merge($v1Product, [
            'category'             => 'smime',
            'product_type'         => 'smime',
            'validation'           => $smimeProduct['validation'],
            'org_required'         => $smimeProduct['org_required'],
            'san_enabled'          => 0,
            'wildcard_san_enabled' => 0,
            'v2_prices'            => $smimeProduct['v2_prices'],
        ]);

        if (!empty($smimeProduct['v2_prices']))
        {
            $merged['prices']     = $smimeProduct['prices'];
            $merged['max_period'] = $smimeProduct['max_period'];
        }

        return $merged;
    }

    /**
     * Normalizes the emails of an API v2 S/MIME item to [['email' => .., 'approved' => bool], ...].
     */
    public static function normalizeEmails($emails)
    {
        $result = [];
        if (!is_array($emails))
        {
            return $result;
        }

        foreach ($emails as $entry)
        {
            if (is_string($entry))
            {
                $result[] = ['email' => $entry, 'approved' => false];
                continue;
            }

            $entry = (array) $entry;
            if (empty($entry['email']))
            {
                continue;
            }

            $result[] = [
                'email'    => (string) $entry['email'],
                'approved' => !empty($entry['approved']),
            ];
        }

        return $result;
    }
}
