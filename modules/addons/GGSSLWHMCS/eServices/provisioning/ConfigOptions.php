<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

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

class ConfigOptions {

    private $p;
    
    const API_PRODUCT_ID        = 'configoption1';
    const API_PRODUCT_MONTHS    = 'configoption2';
    const PRODUCT_ENABLE_SAN    = 'configoption3';
    const PRODUCT_INCLUDED_SANS = 'configoption4';
    const OPTION_SANS_COUNT     = 'sans_count'; // sans_count|SANs http://puu.sh/vXXx3/d08fdb2c2f.png

    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        try {
            return $this->getConfigOptions();
        } catch (Exception $ex) {
            return $this->getErrorOptions($ex->getMessage());
        }
    }


    private function getConfigOptions() {
        $apiProducts = \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products::getInstance()->getAllProducts();
        $products    = [];

        foreach ($apiProducts as $apiProduct) {
            $products[$apiProduct->id] = $apiProduct->product;
        }

        return $this->getFields($products);
    }
    
    private function getFields($products) {
        return [
            'Certificate Type' => [
                'Type'    => 'dropdown',
                'Options' => $products
            ],
            'Months'           => [
                'Type'    => 'text'
            ],
            'Enable SANs'      => [
                'Type' => 'yesno',
            ],
            'Included SANs' => [
                'Type' => 'text',
            ],
        ];
    }

    private function getErrorOptions($error) {
        return [
            'An Error Occurred:' => [
                'Type' => 'text',
                'Description' => \MGModule\GGSSLWHMCS\eServices\ScriptService::getConfigOptionErrorScript($error)
            ]
        ];
    }

}
