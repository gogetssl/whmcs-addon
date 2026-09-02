<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;

class AisSubscription
{
    const NAME_SUFFIX = ' + Automate';

    public static function isAisByApiProductName($apiProductName)
    {
        return substr(rtrim((string) $apiProductName), -strlen(self::NAME_SUFFIX)) === self::NAME_SUFFIX;
    }

    public static function isAisByApiProductId($apiProductId)
    {
        $row = Capsule::table('mgfw_SSLCENTER_product_brand')
            ->where('pid', (int) $apiProductId)
            ->first();

        if ($row && !empty($row->data))
        {
            $data = json_decode($row->data, true);
            $apiProductName = isset($data['product']) ? $data['product'] : '';
            return static::isAisByApiProductName($apiProductName);
        }

        return false;
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
