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
        if($checkTable === false)
        {
            Capsule::schema()->create('mgfw_SSLCENTER_product_brand', function ($table) {
                $table->increments('id');
                $table->integer('pid');
                $table->string('brand');
                $table->text('data');
            });
        }
        $checkTable = Capsule::schema()->hasTable('mgfw_SSLCENTER_product_brand');
        if($checkTable)
        {
            if (Capsule::schema()->hasColumn('mgfw_SSLCENTER_product_brand', 'data'))
            {
                $products = Capsule::table('mgfw_SSLCENTER_product_brand')->get();
                if(isset($products[0]->id))
                {
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
        }
        
        $apiProducts = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getProducts();
        $this->products = [];
        Capsule::table('mgfw_SSLCENTER_product_brand')->truncate();
        foreach ($apiProducts['products'] as $apiProduct) {
            
            
            Capsule::table('mgfw_SSLCENTER_product_brand')->insert([
                'pid' => $apiProduct['id'],
                'brand' => $apiProduct['brand'],
                'data' => json_encode($apiProduct)
            ]);
            
            $p = new \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product();
            \MGModule\SSLCENTERWHMCS\eHelpers\Fill::fill($p, $apiProduct);
            
            $this->products[$p->id] = $p;
        }
        
        return $this->products;
    }
}
