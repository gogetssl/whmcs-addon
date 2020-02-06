<?php

namespace MGModule\SSLCENTERWHMCS\eRepository\sslcenter;

use Illuminate\Database\Capsule\Manager as Capsule;
use Exception;

class Products {

    /**
     *
     * @var Products 
     */
    private static $instance;
    
    /**
     *
     * @var \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product[] 
     */
    private $products;
    
    /**
     * 
     * @return Products
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Products();
        }
        return self::$instance;
    }

    public function getAllProducts() {
        $this->fetchAllProducts();
        return $this->products;
    }

    /**
     * 
     * @param type $id
     * @return \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product
     */
    public function getProduct($id) {
        $this->fetchAllProducts();
        if (isset($this->products[$id])) {
            return $this->products[$id];
        }
        throw new Exception('Invalid API product id.');
    }

    private function fetchAllProducts() {
        if ($this->products !== null) {
            return $this->products;
        }
        
        $checkTable = Capsule::schema()->hasTable('mgfw_SSLCENTER_product_brand');
        if($checkTable)
        {
            if (Capsule::schema()->hasColumn('mgfw_SSLCENTER_product_brand', 'data'))
            {
                $products = Capsule::table('mgfw_SSLCENTER_product_brand')->get();
                
                $this->products = [];
                foreach ($products as $apiProduct) {
                    
                    $apiProduct = json_decode($apiProduct->data, true);
                    $p = new \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product();
                    \MGModule\SSLCENTERWHMCS\eHelpers\Fill::fill($p, $apiProduct);
                    $this->products[$p->id] = $p;
                }

                return $this->products;
                
            }
        }
        
        $apiProducts = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getProducts();
        $this->products = [];
        foreach ($apiProducts['products'] as $apiProduct) {
            $p = new \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product();
            \MGModule\SSLCENTERWHMCS\eHelpers\Fill::fill($p, $apiProduct);
            $this->products[$p->id] = $p;
        }
        
        return $this->products;
    }
}
