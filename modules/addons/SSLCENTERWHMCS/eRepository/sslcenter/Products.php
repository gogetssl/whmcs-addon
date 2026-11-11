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
        return reset($this->products);
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
                    $hasSmime = false;
                    $hasCodeSigning = false;
                    foreach ($products as $apiProduct) {

                        $apiProduct = json_decode($apiProduct->data, true);
                        $p = new \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product();
                        \MGModule\SSLCENTERWHMCS\eHelpers\Fill::fill($p, $apiProduct);
                        $this->products[$p->id] = $p;
                        if (isset($apiProduct['category']) && $apiProduct['category'] === 'smime') {
                            $hasSmime = true;
                        }
                        if (isset($apiProduct['category']) && $apiProduct['category'] === 'cs') {
                            $hasCodeSigning = true;
                        }
                    }

                    if (!$hasSmime) {
                        $this->appendSmimeProductsToCache();
                    }
                    if (!$hasCodeSigning) {
                        $this->appendCodeSigningProductsToCache();
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

    /**
     * Product lists cached before S/MIME support have no S/MIME products. In the admin area
     * they are read once from API v2 and added to the cache (no API call in the client area).
     */
    private function appendSmimeProductsToCache() {
        static $attempted = false;
        if ($attempted || !defined('ADMINAREA')) {
            return;
        }
        $attempted = true;

        // at most once an hour (accounts without S/MIME products, API errors)
        $checkedSetting = 'sslcenter_smime_products_checked';
        $checked = Capsule::table('tblconfiguration')->where('setting', $checkedSetting)->first();
        if ($checked && (int) $checked->value > time() - 3600) {
            return;
        }
        if ($checked) {
            Capsule::table('tblconfiguration')->where('setting', $checkedSetting)->update(['value' => (string) time()]);
        } else {
            Capsule::table('tblconfiguration')->insert(['setting' => $checkedSetting, 'value' => (string) time()]);
        }

        try {
            $v2Products = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getV2Products();
        } catch (\Exception $e) {
            return;
        }

        if (!is_array($v2Products)) {
            return;
        }

        foreach (\MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct::mergeWithKnownProducts($v2Products) as $v2Product) {
            if (!is_array($v2Product) || !isset($v2Product['id'], $v2Product['category']) || $v2Product['category'] !== 'smime') {
                continue;
            }

            $smimeProduct = \MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct::toV1Product($v2Product);
            \MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct::rememberProductIds([$smimeProduct['id']]);

            if (isset($this->products[$smimeProduct['id']])) {
                $merged = \MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct::mergeIntoV1Product(
                    json_decode(json_encode(get_object_vars($this->products[$smimeProduct['id']])), true),
                    $smimeProduct
                );
                Capsule::table('mgfw_SSLCENTER_product_brand')->where('pid', $smimeProduct['id'])->update([
                    'data' => json_encode($merged),
                ]);
                $p = new \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product();
                \MGModule\SSLCENTERWHMCS\eHelpers\Fill::fill($p, $merged);
                $this->products[$p->id] = $p;
                continue;
            }

            Capsule::table('mgfw_SSLCENTER_product_brand')->insert([
                'pid'   => $smimeProduct['id'],
                'brand' => $smimeProduct['brand'],
                'data'  => json_encode($smimeProduct),
            ]);

            $p = new \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product();
            \MGModule\SSLCENTERWHMCS\eHelpers\Fill::fill($p, $smimeProduct);
            $this->products[$p->id] = $p;
        }
    }

    /**
     * Product lists cached before code signing support have no code signing data. In the admin area
     * the supported code signing products are read once from API v2 and added to / merged into the cache.
     */
    private function appendCodeSigningProductsToCache() {
        static $attempted = false;
        if ($attempted || !defined('ADMINAREA')) {
            return;
        }
        $attempted = true;

        // at most once an hour (API errors)
        $checkedSetting = 'sslcenter_cs_products_checked';
        $checked = Capsule::table('tblconfiguration')->where('setting', $checkedSetting)->first();
        if ($checked && (int) $checked->value > time() - 3600) {
            return;
        }
        if ($checked) {
            Capsule::table('tblconfiguration')->where('setting', $checkedSetting)->update(['value' => (string) time()]);
        } else {
            Capsule::table('tblconfiguration')->insert(['setting' => $checkedSetting, 'value' => (string) time()]);
        }

        try {
            $v2Products = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getV2Products();
        } catch (\Exception $e) {
            return;
        }

        if (!is_array($v2Products)) {
            return;
        }

        foreach ($v2Products as $v2Product) {
            if (!is_array($v2Product) || !isset($v2Product['id'], $v2Product['category']) || $v2Product['category'] !== 'cs'
                || !\MGModule\SSLCENTERWHMCS\eHelpers\CodeSigningProduct::isCodeSigningByApiProductId($v2Product['id'])) {
                continue;
            }

            $csProduct = \MGModule\SSLCENTERWHMCS\eHelpers\CodeSigningProduct::toV1Product($v2Product);

            if (isset($this->products[$csProduct['id']])) {
                $csProduct = \MGModule\SSLCENTERWHMCS\eHelpers\CodeSigningProduct::mergeIntoV1Product(
                    json_decode(json_encode(get_object_vars($this->products[$csProduct['id']])), true),
                    $csProduct
                );
                Capsule::table('mgfw_SSLCENTER_product_brand')->where('pid', $csProduct['id'])->update([
                    'data' => json_encode($csProduct),
                ]);
            } else {
                Capsule::table('mgfw_SSLCENTER_product_brand')->insert([
                    'pid'   => $csProduct['id'],
                    'brand' => $csProduct['brand'],
                    'data'  => json_encode($csProduct),
                ]);
            }

            $p = new \MGModule\SSLCENTERWHMCS\eModels\sslcenter\Product();
            \MGModule\SSLCENTERWHMCS\eHelpers\Fill::fill($p, $csProduct);
            $this->products[$p->id] = $p;
        }
    }
}
