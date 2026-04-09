<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;

class AcmeSubscription
{
    const PRODUCT_IDS = [300];

    public static function getProductIds()
    {
        return self::PRODUCT_IDS;
    }

    public static function isAcmeProductId($productId)
    {
        return in_array((int) $productId, self::PRODUCT_IDS, true);
    }

    public static function isAcmeByServiceParams(array $params)
    {
        if (isset($params['configoption1']))
        {
            return self::isAcmeProductId($params['configoption1']);
        }

        if (isset($params['pid']))
        {
            $product = Capsule::table('tblproducts')
                ->select(['configoption1'])
                ->where('id', (int) $params['pid'])
                ->first();

            return isset($product->configoption1) && self::isAcmeProductId($product->configoption1);
        }

        return false;
    }

    public static function isAcmeByServiceId($serviceId)
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

        return isset($product->configoption1) && self::isAcmeProductId($product->configoption1);
    }
}
