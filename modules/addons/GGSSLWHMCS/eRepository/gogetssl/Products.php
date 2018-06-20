<?php

namespace MGModule\GGSSLWHMCS\eRepository\gogetssl;

use Exception;

class Products {

    /**
     *
     * @var Products 
     */
    private static $instance;
    
    /**
     *
     * @var \MGModule\GGSSLWHMCS\eModels\gogetssl\Product[] 
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
     * @return \MGModule\GGSSLWHMCS\eModels\gogetssl\Product
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
        $apiProducts = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getProducts();
        $this->products = [];
        foreach ($apiProducts['products'] as $apiProduct) {
            $p = new \MGModule\GGSSLWHMCS\eModels\gogetssl\Product();
            \MGModule\GGSSLWHMCS\eHelpers\Fill::fill($p, $apiProduct);
            $this->products[$p->id] = $p;
        }
        return $this->products;
    }
}
