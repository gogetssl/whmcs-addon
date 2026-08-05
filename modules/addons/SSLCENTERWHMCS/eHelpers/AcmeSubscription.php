<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use Illuminate\Database\Capsule\Manager as Capsule;
use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions;

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

    /**
     * Number of SANs the product itself covers, before any configurable option is bought.
     *
     * @param int    $pid     tblproducts.id
     * @param string $sanType 'single' or 'wildcard'
     *
     * @return int
     */
    public static function getIncludedSansByPid($pid, $sanType = 'single')
    {
        $column = ($sanType === 'wildcard')
            ? ConfigOptions::PRODUCT_INCLUDED_SANS_WILDCARD
            : ConfigOptions::PRODUCT_INCLUDED_SANS;

        $value = Capsule::table('tblproducts')
            ->where('id', (int) $pid)
            ->value($column);

        return is_numeric($value) ? max(0, (int) $value) : 0;
    }

    /**
     * Id of a SAN configurable option linked to the product, or null when the product does
     * not offer it (PRODUCT_ENABLE_SAN / PRODUCT_ENABLE_SAN_WILDCARD disabled).
     *
     * @param int    $pid        tblproducts.id
     * @param string $optionName ConfigOptions::OPTION_SANS_COUNT or OPTION_SANS_WILDCARD_COUNT
     *
     * @return int|null
     */
    public static function getSanConfigOptionIdByPid($pid, $optionName)
    {
        $id = Capsule::table('tblproductconfigoptions')
            ->join('tblproductconfiglinks', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
            ->where('tblproductconfiglinks.pid', (int) $pid)
            ->where('tblproductconfigoptions.optionname', 'LIKE', $optionName . '%')
            ->value('tblproductconfigoptions.id');

        return $id ? (int) $id : null;
    }

    /**
     * Total certificate capacity for an ACME product: the SANs included with the product
     * plus the SANs bought through the configurable options. This is the same
     * included + bought formula the client area enforces in getAcmeDomainLimits(), so
     * order-time validation and post-order domain management agree.
     *
     * @param int   $pid                tblproducts.id
     * @param array $configOptionValues values keyed by tblproductconfigoptions.id
     *
     * @return array 'single' and 'wildcard' totals, plus 'offered' - whether the product
     *               exposes either SAN configurable option to the customer.
     */
    public static function getSanCapacityByPid($pid, array $configOptionValues)
    {
        $singleId   = self::getSanConfigOptionIdByPid($pid, ConfigOptions::OPTION_SANS_COUNT);
        $wildcardId = self::getSanConfigOptionIdByPid($pid, ConfigOptions::OPTION_SANS_WILDCARD_COUNT);

        $singleBought = ($singleId !== null && isset($configOptionValues[$singleId]))
            ? max(0, (int) $configOptionValues[$singleId])
            : 0;

        $wildcardBought = ($wildcardId !== null && isset($configOptionValues[$wildcardId]))
            ? max(0, (int) $configOptionValues[$wildcardId])
            : 0;

        return [
            'single'   => self::getIncludedSansByPid($pid, 'single') + $singleBought,
            'wildcard' => self::getIncludedSansByPid($pid, 'wildcard') + $wildcardBought,
            'offered'  => ($singleId !== null || $wildcardId !== null),
        ];
    }
}
