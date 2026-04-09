<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\admin;

use MGModule\SSLCENTERWHMCS as main;
use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions as C;

/*
 * Base example
 */

class ProductsConfiguration extends main\mgLibs\process\AbstractController {

    /**
     * This is default page. 
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function indexHTML($input = [], $vars = []) {
        try {

            if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($input['createConfOptions'])) {
                $apiProduct = $this->getApiProductForWhmcsProduct($input['productId']);
                main\eServices\ConfigurableOptionService::createForProduct($input['productId'], $input['productName'], $apiProduct);
                $vars['success'] = main\mgLibs\Lang::T('messages', 'configurable_generated');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($input['createConfOptionsWildcard'])) {
                $apiProduct = $this->getApiProductForWhmcsProduct($input['productId']);
                main\eServices\ConfigurableOptionService::createForProductWildcard($input['productId'], $input['productName'], $apiProduct);
                $vars['success'] = main\mgLibs\Lang::T('messages', 'configurable_generated');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($input['saveProduct'])) {  
                
                $ajax = false;
                
                if(isset($input['ajax']) && $input['ajax'] == '1')
                {
                    $ajax = true;
                    $tempArray = array();
                    parse_str($input['field'], $output);
                    foreach($output as $key => $value)
                    {
                        $tempArray[str_replace('amp;', '', $key)] = $value;
                    }
                    $tempArray['saveProduct'] = 'Save';
                    
                    unset($input['field']);
                    unset($input['ajax']);
                    
                    $input = array_merge($input, $tempArray);
                }
                
                $this->saveProducts($input, $vars);
                
                if($ajax)
                {
                    die('ok');
                }
                
                $vars['success'] = main\mgLibs\Lang::T('messages', 'product_saved');
            }
            $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
            $products = $productModel->getModuleProducts(); 
            foreach ($products as $key => $product) {
                try
                {				
                    $apiProduct = main\eRepository\sslcenter\Products::getInstance()->getProduct($product->{C::API_PRODUCT_ID});
                }
                catch(\Exception $e)
                {
                    unset($products[$key]);
                    continue;
                }
                
                $apiConfig                          = (object) null;
                $apiConfig->name                    = $apiProduct->product;
                $apiConfig->peroids                 = $apiProduct->max_period;
                $apiConfig->availablePeriods        = $apiProduct->getPeriods();                
                $apiConfig->isSanEnabled            = $apiProduct->isSanEnabled();
                $apiConfig->isWildcardSanEnabled    = $apiProduct->wildcard_san_enabled;
                $apiConfig->isAcme                  = \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId((int) $product->{C::API_PRODUCT_ID});
                $products[$key]->apiConfig  = $apiConfig;
                $products[$key]->confOption = main\eServices\ConfigurableOptionService::getForProduct($product->id);  
                $products[$key]->confOptionWildcard = main\eServices\ConfigurableOptionService::getForProductWildcard($product->id);   
            }
            
            $vars['products'] = $products;
            $vars['products_count'] = count($vars['products']);

            if (!empty($apiProducts['products']) && is_array($apiProducts['products'])) {
                $vars['apiProducts'] = $apiProducts['products'];
            }

            $vars['form'] = '';
        } catch (\Exception $e) {
            $vars['formError'] = main\mgLibs\Lang::T('messages', $e->getMessage());
        }


        return array
            (
            'tpl' => 'products_configuration',
            'vars' => $vars
        );
    }

    public function saveProducts($input = array(), $vars = array()) {
        
        $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
          
        if(isset($input['many-products']) && $input['many-products'] == '1')
        {
            
            $products = array();
            
            switch ($input['type']) {
                
                case 'all':
                    
                    $products = $productModel->getModuleProducts();
                    
                break;
                
                case 'selected':
                    
                    $products = $productModel->getSelectedProducts($input['products']);
                    
                break;
            
            }
                        
            foreach ($products as $product)
            {
                if(isset($input['autosetup']) && $input['autosetup'] != 'donot')
                {
                    $productModel->updateProductParam($product->id, 'autosetup', $input['autosetup']);
                }
                if(isset($input['configoption6']) && !empty($input['configoption6']))
                {
                    $productModel->updateProductParam($product->id, 'configoption6', $input['configoption6']/100);
                }
                if(isset($input['hidden']) && $input['hidden'] == '1')
                {
                    $productModel->updateProductParam($product->id, 'hidden', '0');
                }
                if(isset($input['configoption5']) && $input['configoption5'] == '1')
                {
                    $productModel->updateProductParam($product->id, 'configoption5', $input['configoption5']);
                    if (\MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId((int) $product->configoption1)) {
                        $this->applyApiPricingForWhmcsProduct($product->id, $product->configoption1);
                    }
                }
                
                if(isset($input['configoption8']) && !empty($input['configoption8']))
                {
                    $productModel->updateProductParam($product->id, 'configoption8', $input['configoption8']);
                }

                if(isset($input['issued_ssl_message']) && !empty($input['issued_ssl_message']))
                {
                    $productModel->updateProductParam($product->id, 'configoption23', $input['issued_ssl_message']);
                }
                
                if(isset($input['custom_guide']) && !empty($input['custom_guide']))
                {
                    $productModel->updateProductParam($product->id, 'configoption24', $input['custom_guide']);
                }
            }
            
            return true;
        }
        
        $productsForApiPricingSync = [];
        foreach ($input['product'] as $key => $value) {
            $productModel->updateProducDetails($key, $value);
            if (
                !empty($value[C::API_PRODUCT_ID]) &&
                \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId((int) $value[C::API_PRODUCT_ID])
            ) {
                $productModel->updateProductParam($key, 'paytype', 'onetime');
                $productModel->updateProductParam($key, C::API_PRODUCT_MONTHS, 12);
            }
            if (
                !empty($value[C::PRICE_AUTO_DOWNLOAD]) &&
                (string) $value[C::PRICE_AUTO_DOWNLOAD] === '1' &&
                !empty($value[C::API_PRODUCT_ID]) &&
                \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId((int) $value[C::API_PRODUCT_ID])
            ) {
                $productsForApiPricingSync[(int) $key] = isset($value[C::API_PRODUCT_ID]) ? (int) $value[C::API_PRODUCT_ID] : null;
            }
        }

        foreach ($input['currency'] as $key => $value) {
            $productModel->updateProductPricing($key, $value);
        }

        foreach ($productsForApiPricingSync as $productId => $apiProductId) {
            $this->applyApiPricingForWhmcsProduct($productId, $apiProductId);
        }
        
        return true;
    }

    private function getApiProductForWhmcsProduct($productId)
    {
        $product = \Illuminate\Database\Capsule\Manager::table('tblproducts')
            ->select(C::API_PRODUCT_ID)
            ->where('id', (int) $productId)
            ->first();

        if (!$product || empty($product->{C::API_PRODUCT_ID})) {
            return null;
        }

        return main\eRepository\sslcenter\Products::getInstance()->getProduct((int) $product->{C::API_PRODUCT_ID});
    }

    private function applyApiPricingForWhmcsProduct($productId, $apiProductId = null)
    {
        $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
        $productPricing = $productModel->getProductPricing($productId);
        if (!$productPricing || count($productPricing) === 0) {
            return;
        }

        $apiProduct = null;
        if (!empty($apiProductId)) {
            $apiProduct = main\eRepository\sslcenter\Products::getInstance()->getProduct((int) $apiProductId);
        } else {
            $apiProduct = $this->getApiProductForWhmcsProduct($productId);
        }

        if ($apiProduct === null) {
            return;
        }

        $isAcme = \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId((int) $this->readValue($apiProduct, ['id']));
        if ($isAcme) {
            $apiProduct = $this->getAcmePricingApiProduct((int) $this->readValue($apiProduct, ['id']), $apiProduct);
        }

        $pricingByCurrency = $this->buildProductPricingByCurrency($apiProduct, $productModel->getAllCurrencies());
        foreach ($productPricing as $pricing) {
            if (!isset($pricingByCurrency[$pricing->currency])) {
                continue;
            }
            $productModel->updateProductPricing($pricing->pricing_id, $pricingByCurrency[$pricing->currency]);
        }
    }

    private function buildProductPricingByCurrency($apiProduct, $currencies)
    {
        $termPrices = $this->extractBasePricesByTerm($apiProduct);
        $annual = isset($termPrices[12]) ? $termPrices[12] : 0.00;
        $globalRate = $this->getGlobalRate();
        $pricingByCurrency = [];
        $isAcme = \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId((int) $this->readValue($apiProduct, ['id']));

        foreach ($currencies as $currency) {
            $currencyRate = ($currency->default == '1') ? 1 : (float) $currency->rate;
            if ($currencyRate <= 0) {
                $currencyRate = 1;
            }

            if ($isAcme) {
                $value = number_format((float) $annual * $currencyRate * $globalRate, 2, '.', '');
                $pricingByCurrency[$currency->id] = [
                    'monthly'      => $value,
                    'quarterly'    => '-1.00',
                    'semiannually' => '-1.00',
                    'annually'     => '-1.00',
                    'biennially'   => '-1.00',
                    'triennially'  => '-1.00',
                ];
                continue;
            }

            $pricing = [];
            $periodMap = [
                'monthly'      => 12,
                'quarterly'    => 3,
                'semiannually' => 6,
                'annually'     => 12,
                'biennially'   => 24,
                'triennially'  => 36,
            ];

            foreach ($periodMap as $cycle => $term) {
                $basePrice = isset($termPrices[$term]) ? $termPrices[$term] : $annual;
                $value = (float) $basePrice * $currencyRate * $globalRate;
                $pricing[$cycle] = number_format($value, 2, '.', '');
            }

            $pricingByCurrency[$currency->id] = $pricing;
        }

        return $pricingByCurrency;
    }

    private function extractBasePricesByTerm($apiProduct)
    {
        $prices = $this->toArray($this->readValue($apiProduct, ['prices']));
        if (empty($prices)) {
            return [];
        }

        $results = [];
        foreach ($prices as $entry) {
            $term = (int) $this->readValue($entry, ['term', 'period']);
            if ($term <= 0) {
                continue;
            }

            $baseNode = $this->readValue($entry, ['base']);
            $price = $this->resolveBasePriceFromNode($baseNode);
            if ($price === null) {
                $price = $this->readMonetaryValue($entry, ['price', 'selling', 'retail']);
            }

            if ($price !== null) {
                $results[$term] = $price;
            }
        }

        if (empty($results)) {
            foreach ($prices as $term => $price) {
                if (!is_numeric($term) || !is_numeric($price)) {
                    continue;
                }
                $termInt = (int) $term;
                if ($termInt <= 0) {
                    continue;
                }
                $results[$termInt] = (float) $price;
            }
        }

        return $results;
    }

    private function getAcmePricingApiProduct($apiProductId, $fallbackApiProduct)
    {
        try {
            $apiResponse = main\eProviders\ApiProvider::getInstance()->getApi()->getProductPrice((int) $apiProductId);
            $response = $this->toArray($apiResponse);
            $prices = $this->toArray($this->readValue($response, ['prices']));
            if (empty($prices)) {
                $productNode = $this->toArray($this->readValue($response, ['product']));
                $prices = $this->toArray($this->readValue($productNode, ['prices']));
            }
            if (empty($prices)) {
                return $fallbackApiProduct;
            }

            $merged = $this->toArray($fallbackApiProduct);
            $merged['id'] = (int) $apiProductId;
            $merged['prices'] = $prices;

            return $merged;
        } catch (\Exception $e) {
            return $fallbackApiProduct;
        }
    }

    private function resolveBasePriceFromNode($baseNode)
    {
        if (is_numeric($baseNode)) {
            return (float) $baseNode;
        }

        $baseArray = $this->toArray($baseNode);
        if (empty($baseArray)) {
            return null;
        }

        foreach (['single', 'wildcard'] as $type) {
            $node = $this->readValue($baseArray, [$type]);
            $price = $this->readMonetaryValue($node, ['selling', 'retail', 'price']);
            if ($price !== null) {
                return $price;
            }
        }

        return $this->readMonetaryValue($baseArray, ['selling', 'retail', 'price']);
    }

    private function readMonetaryValue($node, array $keys)
    {
        foreach ($keys as $key) {
            $value = $this->readValue($node, [$key]);
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        if (is_numeric($node)) {
            return (float) $node;
        }

        return null;
    }

    private function readValue($source, array $keys)
    {
        foreach ($keys as $key) {
            if (is_array($source) && array_key_exists($key, $source)) {
                return $source[$key];
            }
            if (is_object($source) && isset($source->{$key})) {
                return $source->{$key};
            }
        }

        return null;
    }

    private function toArray($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        return [];
    }

    private function getGlobalRate()
    {
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $rate = isset($apiConf->rate) ? (float) $apiConf->rate : 1;
        return ($rate > 0) ? $rate : 1;
    }

    public function enableProductJSON($input, $vars = array()) {
        $productId = trim($input['productId']);
        if (!empty($productId)) {

            $productId = trim($input['productId']);

            $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
            if ($productModel->enableProduct($productId)) {
                return [
                    'success' => main\mgLibs\Lang::T('messages', '')
                ];
            }
        }

        return [
            'error' => main\mgLibs\Lang::T('messages', '')
        ];
    }

    public function disableProductJSON($input, $vars = array()) {
        $productId = trim($input['productId']);
        if (!empty($productId)) {

            $productId = trim($input['productId']);

            $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
            if ($productModel->disableProduct($productId)) {
                return [
                    'success' => main\mgLibs\Lang::T('messages', '')
                ];
            }
        }

        return [
            'error' => main\mgLibs\Lang::T('messages', '')
        ];
    }

    function saveItemHTML($input, $vars = array()) {

        if ($this->checkToken()) {
            try {
                $login = trim($input['login']);
                $password = trim($input['password']);
                if (empty($login) || empty($password))
                    throw new \Exception('empty_fields');

                $login = $input['login'];
                $password = $input['password'];

                $apiConfigRepo = new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository();
                $apiConfigRepo->setConfiguration($login, $password);
            } catch (\Exception $ex) {
                $vars['formError'] = main\mgLibs\Lang::T('messages', $ex->getMessage());
            }
        }

        return $this->indexHTML($input, $vars);
    }

    public function testConnectionJSON($input = [], $vars = []) {
        $login = trim($input['login']);
        $password = trim($input['password']);
        if (!empty($login) && !empty($password)) {

            $login = trim($input['login']);
            $password = trim($input['password']);

            $api = new \MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterApi();
            $authKey = $api->auth($login, $password);

            if (!empty($authKey['key'])) {
                return [
                    'success' => main\mgLibs\Lang::T('messages', 'api_connection_success')
                ];
            }
        }

        return [
            'error' => main\mgLibs\Lang::T('messages', 'api_connection_failed')
        ];
    }

    /**
     * This is custom page. 
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function pageHTML() {
        $vars = array();

        return array
            (
            //You have to create tpl file  /modules/addons/SSLCENTERWHMCS/templates/admin/pages/example1/page.1tpl
            'tpl' => 'page',
            'vars' => $vars
        );
    }

    /*     * ************************************************************************
     * AJAX USING ARRAY
     * ************************************************************************ */

    /**
     * Display custom page for ajax errors
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function ajaxErrorHTML() {
        return array
            (
            'tpl' => 'ajaxError'
        );
    }

    /**
     * Return error message using array
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function getErrorArrayJSON() {
        return array
            (
            'error' => 'Custom error'
        );
    }

    /**
     * Return success message using array
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function getSuccessArrayJSON() {
        return array
            (
            'success' => 'Custom success'
        );
    }

    /*     * ************************************************************************
     * AJAX USING DATA-ACT
     * *********************************************************************** */

    public function ajaxErrorDataActHTML() {
        return array
            (
            'tpl' => 'ajaxErrorDataAct'
        );
    }

    /*     * ************************************************************************
     * AJAX CONTENT
     * *********************************************************************** */

    public function ajaxContentHTML() {
        return array
            (
            'tpl' => 'ajaxContent'
        );
    }

    public function ajaxContentJSON() {
        return array
            (
            'html' => main\mgLibs\Smarty::I()->view('ajaxContentJSON')
        );
    }

    /*     * ******************************************************
     * CREATOR
     * ***************************************************** */

    public function getCreatorJSON() {
        $creator = new main\mgLibs\forms\Popup('mymodal');
        $creator->addField(new main\mgLibs\forms\TextField(array(
            'name' => 'customTextField',
            'value' => 'empty_value',
            'placeholder' => 'placeholder!'
        )));
        ;

        return array(
            'modal' => $creator->getHTML()
        );
    }

}
