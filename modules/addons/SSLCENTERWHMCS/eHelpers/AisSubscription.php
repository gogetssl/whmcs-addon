<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;

class AisSubscription
{
    // GoGetSSL API product IDs of AutoInstall SSL (+ Automate) products:
    // 400 RapidSSL Plan, 401 RapidSSL Wildcard Plan, 402 GeoTrust DV Plan, 403 GeoTrust DV Wildcard Plan
    const PRODUCT_IDS = [400, 401, 402, 403];

    public static function getProductIds()
    {
        return self::PRODUCT_IDS;
    }

    public static function isAisByApiProductId($apiProductId)
    {
        return in_array((int) $apiProductId, self::PRODUCT_IDS, true);
    }

    public static function isAisByServiceParams(array $params)
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

        return $apiProductId ? static::isAisByApiProductId((int) $apiProductId) : false;
    }

    public static function isAisByServiceId($serviceId)
    {
        $service = Capsule::table('tblhosting')
            ->select(['packageid'])
            ->where('id', (int) $serviceId)
            ->first();

        if (!isset($service->packageid))
        {
            return false;
        }

        $product = Capsule::table('tblproducts')
            ->select(['configoption1'])
            ->where('id', (int) $service->packageid)
            ->first();

        return $product ? static::isAisByApiProductId((int) $product->configoption1) : false;
    }
}
