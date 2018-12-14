<?php

namespace MGModule\SSLCENTERWHMCS\models\productConfiguration;

use Illuminate\Database\Capsule\Manager as Capsule;

use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions as C;
use MGModule\SSLCENTERWHMCS as main;

class Repository extends \MGModule\SSLCENTERWHMCS\mgLibs\models\Repository {

    public function getModelClass() {
        return __NAMESPACE__ . '\ProductConfigurationItem';
    }

    public function get() {
        return Capsule::table($this->tableName)->first();
    }

    public function getModuleProducts($moduleName = "SSLCENTERWHMCS", $gid = 0) {
        if (empty($moduleName)) {
            return false;
        }

        $products = Capsule::table("tblproducts")
                ->where("tblproducts.servertype", "=", $moduleName);
           
        if($gid) {
            $products = $products->where("tblproducts.gid", "=", $gid);
        }
        
        $products = $products->get();

        
        foreach ($products as $key => $value) {
            $products[$key]->pricing = $this->getProductPricing($value->id);
            //get price with commission
            $commissonValue = $value->{C::COMMISSION};
            foreach($products[$key]->pricing as &$price)
            {
                $price->commission_monthly = (!in_array($price->monthly, ['-1.00', '0.00'])) ? (string)number_format(((float)$price->monthly + (float)$price->monthly * (float)$commissonValue), 2) : '0.00';
                $price->commission_quarterly = (!in_array($price->quarterly, ['-1.00', '0.00'])) ? (string)number_format(((float)$price->quarterly + (float)$price->quarterly * (float)$commissonValue), 2) : '0.00';
                $price->commission_semiannually = (!in_array($price->semiannually, ['-1.00', '0.00'])) ? (string)number_format(((float)$price->semiannually + (float)$price->semiannually * (float)$commissonValue), 2) : '0.00';
                $price->commission_annually = (!in_array($price->annually, ['-1.00', '0.00'])) ? (string)number_format(((float)$price->annually + (float)$price->annually * (float)$commissonValue), 2) : '0.00';
                $price->commission_biennially = (!in_array($price->biennially, ['-1.00', '0.00'])) ? (string)number_format(((float)$price->biennially + (float)$price->biennially * (float)$commissonValue), 2) : '0.00';
                $price->commission_triennially = (!in_array($price->triennially, ['-1.00', '0.00'])) ? (string)number_format(((float)$price->triennially + (float)$price->triennially * (float)$commissonValue), 2) : '0.00';                
            }
         }

        return $products;
    }

    public function getAllCurrencies() {
        return Capsule::table("tblcurrencies")->get();
    }

    public function getProductPricing($productId) {
        return Capsule::table("tblpricing")
                ->select('*', 'tblpricing.id as pricing_id')
                ->join('tblcurrencies', 'tblcurrencies.id', '=', 'tblpricing.currency')
                ->where("tblpricing.relid", "=", $productId)
                ->where("tblpricing.type", "=", 'product')  
                ->orderBy('tblcurrencies.code', 'ASC')
                ->get();
    }
    
    public function enableProduct($productId) {
        return Capsule::table('tblproducts')->where('id', $productId)
                        ->update(
                                [
                                    'hidden' => 0
                                ]
        );
    }

    public function disableProduct($productId) {
        return Capsule::table('tblproducts')->where('id', $productId)
                        ->update(
                                [
                                    'hidden' => 1
                                ]
        );
    }

    public function updateProductName($productId, $name) {
        return Capsule::table('tblproducts')->where('id', $productId)
                        ->update(
                                [
                                    'name' => $name,
                                    'paytype' => 'recurring'
                                ]
        );
    }
    
    public function updateProducDetails($productId, $params) {
        $update                           = [];
        $update['name']                   = $params['name'];
        $update[C::API_PRODUCT_MONTHS]    = $params[C::API_PRODUCT_MONTHS];
        $update[C::PRODUCT_ENABLE_SAN]    = $params[C::PRODUCT_ENABLE_SAN] ? $params[C::PRODUCT_ENABLE_SAN] : '';
        $update[C::PRODUCT_INCLUDED_SANS] = $params[C::PRODUCT_INCLUDED_SANS] ? $params[C::PRODUCT_INCLUDED_SANS] : '0';
        $update['paytype']                = $params['paytype'];
        $update['autosetup']              = $params['autosetup'];
        $update[C::PRICE_AUTO_DOWNLOAD]   = $params[C::PRICE_AUTO_DOWNLOAD] ? $params[C::PRICE_AUTO_DOWNLOAD] : '0';
        $update[C::COMMISSION]            = $params[C::COMMISSION] ? $params[C::COMMISSION] / 100 : '0';
        
        //if san disabled unassign sans config options
        if($update[C::PRODUCT_ENABLE_SAN] !== 'on') {
            main\eServices\ConfigurableOptionService::unassignFromProduct($productId, $update['name']);
        }
        else
        {
            main\eServices\ConfigurableOptionService::assignToProduct($productId, $update['name']);
        }
        
        return Capsule::table('tblproducts')->where('id', $productId)->update($update);
    }

    public function updateProductPricing($pricingId, $data) {
        return Capsule::table('tblpricing')->where('id', $pricingId)
                        ->update($data);
    }

    public function createNewProduct($productData) {
        return Capsule::table('tblproducts')->insertGetId(
                        $productData
        );
    }

    public function createPricing($pricingData) {
        return Capsule::table('tblpricing')->insertGetId(
                        $pricingData
        );
    }

    public function parseProductsForTable($products) {
        
    }

}
