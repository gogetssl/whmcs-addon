<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;

class AisSubscription
{
    const NAME_SUFFIX = ' + Automate';

    public static function isAisByProductName($productName)
    {
        return substr(rtrim((string) $productName), -strlen(self::NAME_SUFFIX)) === self::NAME_SUFFIX;
    }

    public static function isAisByServiceParams(array $params)
    {
        if (isset($params['pid']))
        {
            $product = Capsule::table('tblproducts')
                ->select(['name'])
                ->where('id', (int) $params['pid'])
                ->first();

            return $product && self::isAisByProductName($product->name);
        }

        return false;
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
            ->select(['name'])
            ->where('id', (int) $service->packageid)
            ->first();

        return $product && self::isAisByProductName($product->name);
    }
}
