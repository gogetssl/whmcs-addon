<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\admin;

use MGModule\SSLCENTERWHMCS as main;
use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions as C;
use Exception;

class ProductsCreator extends main\mgLibs\process\AbstractController {
    
    private $apiProductsRepo;

    /**
     * This is default page. 
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function indexHTML($input = [], $vars = []) {
        try {
            $this->apiProductsRepo = main\eRepository\sslcenter\Products::getInstance();
            $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
            $vars['currencies'] = $productModel->getAllCurrencies();
            $vars['apiProducts'] = $this->apiProductsRepo->getAllProducts();
            $vars['apiProductsCount'] = count($this->apiProductsRepo->getAllProducts());
            $vars['productGroups'] = \WHMCS\Product\Group::all();
            
            if(count($vars['productGroups']) === 0) {
                throw new Exception('no_product_group_found');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($input['createMass'])) {
                $this->saveProducts($vars['currencies'], $input);
                $vars['success'] = main\mgLibs\Lang::T('messages', 'mass_product_created');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($input['createSingle'])) {
                $this->saveProduct($input, $vars);
                $vars['success'] = main\mgLibs\Lang::T('messages', 'single_product_created');
            }

        } catch (Exception $e) {
            $vars['formError'] = main\mgLibs\Lang::T('messages', $e->getMessage());
        }

        return array
            (
            'tpl' => 'products_creator',
            'vars' => $vars
        );
    }
    
    public function saveProduct($input = array()) {
        if (isset($input[C::API_PRODUCT_ID]) AND $input[C::API_PRODUCT_ID] == 0) {
            throw new Exception('api_product_not_chosen');
        }

        if (!$this->apiProductsRepo) {
            $this->apiProductsRepo = main\eRepository\sslcenter\Products::getInstance();
        }

        $apiProduct = $this->apiProductsRepo->getProduct($input[C::API_PRODUCT_ID]);
        $isAcmeProduct = \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId($input[C::API_PRODUCT_ID]);
        $pricingApiProduct = $isAcmeProduct ? $this->getAcmePricingApiProduct($input[C::API_PRODUCT_ID], $apiProduct) : $apiProduct;
        $sanConfig = $this->extractSanConfig($apiProduct);
        
        $productData = [
            'type'       => 'hostingaccount',
            'gid'        => $input['gid'],
            'name'       => $input['name'],
            'paytype'    => $input['paytype'] ? $input['paytype'] : 'recurring',
            'servertype' => 'SSLCENTERWHMCS',
            'hidden'     => '1',
            'autosetup'  => $input['autosetup'],
            C::API_PRODUCT_ID => $input[C::API_PRODUCT_ID],
            C::API_PRODUCT_MONTHS => $input[C::API_PRODUCT_MONTHS],
            C::PRODUCT_ENABLE_SAN => !empty($input[C::PRODUCT_ENABLE_SAN]) ? $input[C::PRODUCT_ENABLE_SAN] : '',
            C::PRODUCT_ENABLE_SAN_WILDCARD => !empty($input[C::PRODUCT_ENABLE_SAN_WILDCARD]) ? $input[C::PRODUCT_ENABLE_SAN_WILDCARD] : '',
            C::PRODUCT_INCLUDED_SANS => !empty($input[C::PRODUCT_INCLUDED_SANS]) ? $input[C::PRODUCT_INCLUDED_SANS] : 0,
            C::PRODUCT_INCLUDED_SANS_WILDCARD => !empty($input[C::PRODUCT_INCLUDED_SANS_WILDCARD]) ? $input[C::PRODUCT_INCLUDED_SANS_WILDCARD] : 0,
            C::PRICE_AUTO_DOWNLOAD => isset($input[C::PRICE_AUTO_DOWNLOAD]) ? $input[C::PRICE_AUTO_DOWNLOAD] : '0',
        ];

        if ($isAcmeProduct)
        {
            $productData['description']          = $this->readProductDescription($apiProduct);
            $productData['paytype']              = 'onetime';
            $productData[C::API_PRODUCT_MONTHS]  = 12;
            $productData[C::PRICE_AUTO_DOWNLOAD] = '1';
            $productData['configoptionsupgrade'] = 1;
            $productData[C::PRODUCT_ENABLE_SAN] = $sanConfig['single_allowed'] ? 'on' : '';
            $productData[C::PRODUCT_ENABLE_SAN_WILDCARD] = $sanConfig['wildcard_allowed'] ? 'on' : '';
            $productData[C::PRODUCT_INCLUDED_SANS] = ($sanConfig['included_single'] !== null)
                ? $sanConfig['included_single']
                : $this->toInt($this->readValue($input, [C::PRODUCT_INCLUDED_SANS]), 0);
            $productData[C::PRODUCT_INCLUDED_SANS_WILDCARD] = ($sanConfig['included_wildcard'] !== null)
                ? $sanConfig['included_wildcard']
                : $this->toInt($this->readValue($input, [C::PRODUCT_INCLUDED_SANS_WILDCARD]), 0);
        }

        if(isset($input['issued_ssl_message']) && !empty($input['issued_ssl_message']))
        {
            $productData['configoption23'] = $input['issued_ssl_message'];
        }

        if(isset($input['custom_guide']) && !empty($input['custom_guide']))
        {
            $productData['configoption24'] = $input['custom_guide'];
        }
        
        $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
        $newProductId = $productModel->createNewProduct($productData);

        $shouldApplyApiPricing = $isAcmeProduct || (!empty($productData[C::PRICE_AUTO_DOWNLOAD]) && (string) $productData[C::PRICE_AUTO_DOWNLOAD] === '1');
        $apiPricingByCurrency = $shouldApplyApiPricing ? $this->buildProductPricingByCurrency($pricingApiProduct, $productModel->getAllCurrencies()) : [];

        foreach ($input['currency'] as $key => $value) {
            $value['relid'] = $newProductId;
            if (isset($apiPricingByCurrency[$value['currency']])) {
                $value = array_merge($value, $apiPricingByCurrency[$value['currency']]);
            }
            if ($isAcmeProduct)
            {
                $value['monthly']      = isset($value['annually']) ? $value['annually'] : '0.00';
                $value['quarterly']    = '-1.00';
                $value['annually']     = '-1.00';
                $value['semiannually'] = '-1.00';
                $value['biennially']   = '-1.00';
                $value['triennially']  = '-1.00';
            }
            $productModel->createPricing($value);
        }
        
        if($isAcmeProduct && $productData[C::PRODUCT_ENABLE_SAN] === 'on') {
            main\eServices\ConfigurableOptionService::createForProduct($newProductId, $productData['name'], $pricingApiProduct);
        } elseif($apiProduct->isSanEnabled() AND !empty($input[C::PRODUCT_ENABLE_SAN]) && $input[C::PRODUCT_ENABLE_SAN] === 'on') {
            main\eServices\ConfigurableOptionService::createForProduct($newProductId, $productData['name']);
        }

        if($isAcmeProduct && $productData[C::PRODUCT_ENABLE_SAN_WILDCARD] === 'on') {
            main\eServices\ConfigurableOptionService::createForProductWildcard($newProductId, $productData['name'], $pricingApiProduct);
        }
    }
    
    public function saveProducts($currencies, $post) {
        
        $apiProducts = $this->apiProductsRepo->getAllProducts();
        $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
        $moduleProducts = $productModel->getModuleProducts('SSLCENTERWHMCS', $post['gid']);
        foreach ($moduleProducts as $key => $value) {
            $moduleProductId = $value->configoption1;
            foreach ($apiProducts as $key => $value) {
                if ($moduleProductId == $value->id) {
                    unset($apiProducts[$key]);
                    break;
                }
            }
        }
        
        $dummyCurrencies = [];
        foreach ($currencies as $curreny) {
            $temp = [];
            $temp['currency'] = $curreny->id;
            $temp['msetupfee'] = '0.00';
            $temp['qsetupfee'] = '0.00';
            $temp['ssetupfee'] = '0.00';
            $temp['asetupfee'] = '0.00';
            $temp['bsetupfee'] = '0.00';
            $temp['tsetupfee'] = '0.00';
            $temp['monthly'] = '-1.00';
            $temp['quarterly'] = '-1.00';
            $temp['semiannually'] = '-1.00';
            $temp['annually'] = '-1.00';
            $temp['biennially'] = '-1.00';
            $temp['triennially'] = '-1.00';
            $dummyCurrencies[] = $temp;
        }
        
        foreach ($apiProducts as $apiProduct) {
            $input = [];
            $input['name'] = $apiProduct->product;
            $input['gid'] = $post['gid'];
            $input[C::API_PRODUCT_ID] = $apiProduct->id;
            $input[C::API_PRODUCT_MONTHS] = $apiProduct->getMinimalPeriods();
            $input[C::PRODUCT_ENABLE_SAN] = '';
            $input[C::PRODUCT_ENABLE_SAN_WILDCARD] = '';
            $input[C::PRODUCT_INCLUDED_SANS] = '0';
            $input[C::PRODUCT_INCLUDED_SANS_WILDCARD] = '0';
            $input['paytype'] = $apiProduct->getPayType();
            $input['currency'] = $dummyCurrencies;
            $input['autosetup'] = ($apiProduct->getPayType() == 'free') ? 'order' : 'payment' ;

            if (\MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId($apiProduct->id))
            {
                $input['paytype'] = 'recurring';
                $input[C::API_PRODUCT_MONTHS] = 12;
                foreach ($input['currency'] as $currencyKey => $currencyData)
                {
                    $input['currency'][$currencyKey]['annually'] = '0.00';
                }
            }
            $this->saveProduct($input);
        }

    }

    private function readProductDescription($apiProduct)
    {
        $description = $this->readValue($apiProduct, ['description']);
        if (is_string($description) && $description !== '') {
            return $description;
        }

        return '';
    }

    private function extractSanConfig($apiProduct)
    {
        $san = $this->toArray($this->readValue($apiProduct, ['san']));
        $included = $this->toArray($this->readValue($san, ['included']));

        $includedSingleRaw = $this->readValue($included, ['single', 'san', 'single_san']);
        if ($includedSingleRaw === null) {
            $includedSingleRaw = $this->readValue($apiProduct, ['included_sans', 'included_single_sans', 'included_single_san']);
        }
        $includedWildcardRaw = $this->readValue($included, ['wildcard', 'wildcard_san']);
        if ($includedWildcardRaw === null) {
            $includedWildcardRaw = $this->readValue($apiProduct, ['included_wildcard_sans', 'included_wildcard_san']);
        }

        $includedSingle = is_numeric($includedSingleRaw) ? (int) $includedSingleRaw : null;
        $includedWildcard = is_numeric($includedWildcardRaw) ? (int) $includedWildcardRaw : null;

        $singleAllowedValue = $this->readValue($san, ['single_allowed']);
        if ($singleAllowedValue === null) {
            $singleAllowedValue = method_exists($apiProduct, 'isSanEnabled') ? (int) $apiProduct->isSanEnabled() : 0;
        }

        $wildcardAllowedValue = $this->readValue($san, ['wildcard_allowed']);
        if ($wildcardAllowedValue === null) {
            $wildcardAllowedValue = method_exists($apiProduct, 'isSanWildcardEnabled') ? (int) $apiProduct->isSanWildcardEnabled() : 0;
        }

        return [
            'included_single' => $includedSingle,
            'included_wildcard' => $includedWildcard,
            'single_allowed' => $this->toBool($singleAllowedValue),
            'wildcard_allowed' => $this->toBool($wildcardAllowedValue),
        ];
    }

    private function buildProductPricingByCurrency($apiProduct, $currencies)
    {
        $termPrices = $this->extractBasePricesByTerm($apiProduct);
        $annual = isset($termPrices[12]) ? $termPrices[12] : 0.00;
        $globalRate = $this->getGlobalRate();
        $pricingByCurrency = [];

        foreach ($currencies as $currency) {
            $currencyRate = ($currency->default == '1') ? 1 : (float) $currency->rate;
            if ($currencyRate <= 0) {
                $currencyRate = 1;
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

        return $results;
    }

    private function getAcmePricingApiProduct($apiProductId, $fallbackApiProduct)
    {
        try {
            $apiResponse = main\eProviders\ApiProvider::getInstance()->getApi()->getProductPrice((int) $apiProductId);
            $prices = $this->extractPricesFromApiResponse($apiResponse);
            if (empty($prices)) {
                return $fallbackApiProduct;
            }

            $mergedProduct = $this->toArray($fallbackApiProduct);
            $mergedProduct['id'] = (int) $apiProductId;
            $mergedProduct['prices'] = $prices;

            return $mergedProduct;
        } catch (\Exception $e) {
            return $fallbackApiProduct;
        }
    }

    private function extractPricesFromApiResponse($apiResponse)
    {
        $response = $this->toArray($apiResponse);
        if (empty($response)) {
            return [];
        }

        $prices = $this->toArray($this->readValue($response, ['prices']));
        if (!empty($prices)) {
            return $this->normalizeAcmePrices($prices);
        }

        $productNode = $this->toArray($this->readValue($response, ['product']));
        $prices = $this->toArray($this->readValue($productNode, ['prices']));
        if (!empty($prices)) {
            return $this->normalizeAcmePrices($prices);
        }

        return [];
    }

    private function normalizeAcmePrices(array $prices)
    {
        if ($this->isTermEntriesList($prices)) {
            return $prices;
        }

        $vendor = $this->toArray($this->readValue($prices, ['vendor']));

        $baseByTerm = $this->extractTermPriceMap($prices);
        if (empty($baseByTerm)) {
            $baseByTerm = $this->extractTermPriceMap($vendor);
        }

        $singleSanByTerm = $this->extractTermPriceMap($this->toArray($this->readValue($vendor, ['single_san'])));
        if (empty($singleSanByTerm)) {
            $singleSanByTerm = $this->extractTermPriceMap($this->toArray($this->readValue($prices, ['single_san'])));
        }
        if (empty($singleSanByTerm)) {
            $singleSanByTerm = $this->extractTermPriceMap($this->toArray($this->readValue($prices, ['san'])));
        }

        $wildcardSanByTerm = $this->extractTermPriceMap($this->toArray($this->readValue($vendor, ['wildcard_san'])));
        if (empty($wildcardSanByTerm)) {
            $wildcardSanByTerm = $this->extractTermPriceMap($this->toArray($this->readValue($prices, ['wildcard_san'])));
        }

        $terms = array_unique(array_merge(array_keys($baseByTerm), array_keys($singleSanByTerm), array_keys($wildcardSanByTerm)));
        if (empty($terms)) {
            return [];
        }

        sort($terms, SORT_NUMERIC);

        $normalized = [];
        foreach ($terms as $term) {
            $basePrice = isset($baseByTerm[$term]) ? (float) $baseByTerm[$term] : 0.0;
            $singleSanPrice = isset($singleSanByTerm[$term]) ? (float) $singleSanByTerm[$term] : 0.0;
            $wildcardSanPrice = isset($wildcardSanByTerm[$term]) ? (float) $wildcardSanByTerm[$term] : 0.0;

            $normalized[] = [
                'term' => (int) $term,
                'base' => $basePrice,
                'price' => $basePrice,
                'san' => [
                    'single' => ['price' => $singleSanPrice],
                    'wildcard' => ['price' => $wildcardSanPrice],
                ],
            ];
        }

        return $normalized;
    }

    private function isTermEntriesList(array $prices)
    {
        foreach ($prices as $entry) {
            if (!is_array($entry) && !is_object($entry)) {
                continue;
            }
            $term = $this->readValue($entry, ['term', 'period']);
            if (is_numeric($term) && (int) $term > 0) {
                return true;
            }
        }

        return false;
    }

    private function extractTermPriceMap(array $node)
    {
        $result = [];
        foreach ($node as $term => $value) {
            if (!is_numeric($term) || !is_numeric($value)) {
                continue;
            }
            $termInt = (int) $term;
            if ($termInt <= 0) {
                continue;
            }
            $result[$termInt] = (float) $value;
        }

        return $result;
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

    private function toInt($value, $default = 0)
    {
        return is_numeric($value) ? (int) $value : (int) $default;
    }

    private function toBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return ((int) $value) === 1;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function getGlobalRate()
    {
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $rate = isset($apiConf->rate) ? (float) $apiConf->rate : 1;
        return ($rate > 0) ? $rate : 1;
    }

    function saveItemHTML($input, $vars = array()) {

        if ($this->checkToken()) {
            try {

                $login = trim($input['login']);
                $password = trim($input['password']);
                if (empty($login) || empty($password))
                    throw new Exception('empty_fields');

                $login = $input['login'];
                $password = $input['password'];

                $apiConfigRepo = new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository();
                $apiConfigRepo->setConfiguration($login, $password);
            } catch (Exception $ex) {
                $vars['formError'] = main\mgLibs\Lang::T('messages', $ex->getMessage());
            }
        }

        return $this->indexHTML($input, $vars);
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
