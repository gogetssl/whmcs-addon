<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

/**
 * Types:
 * 
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 */
class ConfigOptions
{
    private $p;
    const API_PRODUCT_ID        = 'configoption1';
    const API_PRODUCT_MONTHS    = 'configoption2';
    const PRODUCT_ENABLE_SAN    = 'configoption3';
    const PRODUCT_INCLUDED_SANS = 'configoption4';
    const PRICE_AUTO_DOWNLOAD   = 'configoption5';
    const COMMISSION            = 'configoption6';
    const OPTION_SANS_COUNT     = 'sans_count'; // sans_count|SANs http://puu.sh/vXXx3/d08fdb2c2f.png

    function __construct(&$params = null)
    {
        $this->p = &$params;
    }

    public function run()
    {
        try
        {
            return $this->getConfigOptions();
        }
        catch (Exception $ex)
        {
            return $this->getErrorOptions($ex->getMessage());
        }
    }

    private function getConfigOptions()
    {
        $apiProducts = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products::getInstance()->getAllProducts();
        $products    = [];

        foreach ($apiProducts as $apiProduct)
        {
            $products[$apiProduct->id] = $apiProduct->product;
        }

        return $this->getFields($products);
    }

    private function getFields($products)
    {
        return [
            'Certificate Type'    => [
                'Type'    => 'dropdown',
                'Options' => $products
            ],
            'Months'              => [
                'Type' => 'text'
            ],
            'Enable SANs'         => [
                'Type' => 'yesno',
            ],
            'Included SANs'       => [
                'Type' => 'text',
            ],
        ];
    }

    private function getErrorOptions($error)
    {
        return [
            'An Error Occurred:' => [
                'Type'        => 'text',
                'Description' => \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getConfigOptionErrorScript($error)
            ]
        ];
    }

    public function validateAndSanitizeQuantityConfigOptions($configOption, array $configOptionsMinMaxQuantities)
    {
        $whmcs        = \WHMCS\Application::getInstance();
        $errorMessage = '';
        foreach ($configOption as $configid => $optionvalue)
        {
            if (!key_exists($configid, $configOptionsMinMaxQuantities))
                continue;
            $data       = get_query_vals("tblproductconfigoptions", "", array("id" => $configid));
            $optionname = $data["optionname"];
            $qtyminimum = ($configOptionsMinMaxQuantities[$configid]['min'] != NULL) ? $configOptionsMinMaxQuantities[$configid]['min'] : $data["qtyminimum"];
            $qtymaximum = ($configOptionsMinMaxQuantities[$configid]['max'] != NULL) ? $configOptionsMinMaxQuantities[$configid]['max'] : $data["qtymaximum"];
            if (strpos($optionname, "|"))
            {
                $optionname = explode("|", $optionname);
                $optionname = trim($optionname[1]);
            }
            $optionvalue = (int) $optionvalue;
            if ($qtyminimum < 0)
            {
                $qtyminimum = 0;
            }
            if ($optionvalue < 0 || $optionvalue < $qtyminimum && 0 < $qtyminimum || 0 < $qtymaximum && $qtymaximum < $optionvalue)
            {
                if ($qtymaximum <= 0)
                {
                    $qtymaximum = $whmcs->get_lang("clientareaunlimited");
                }

                $errorMessage .= "<li>" . sprintf($whmcs->get_lang("configoptionqtyminmax"), $optionname, $qtyminimum, $qtymaximum);
            }
        }

        return $errorMessage;
    }
}
