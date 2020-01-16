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
                main\eServices\ConfigurableOptionService::createForProduct($input['productId'], $input['productName']);
                $vars['success'] = main\mgLibs\Lang::T('messages', 'configurable_generated');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($input['saveProduct'])) {                
                $this->saveProducts($input, $vars);
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
                
                $apiConfig                  = (object) null;
                $apiConfig->name            = $apiProduct->product;
                $apiConfig->peroids         = max($apiProduct->getPeriods());
                $apiConfig->availablePeriods= $apiProduct->getPeriods();                
                $apiConfig->isSanEnabled    = $apiProduct->isSanEnabled();
                $products[$key]->apiConfig  = $apiConfig;
                $products[$key]->confOption = main\eServices\ConfigurableOptionService::getForProduct($product->id);    
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
        foreach ($input['product'] as $key => $value) {
            $productModel->updateProducDetails($key, $value);
        }

        foreach ($input['currency'] as $key => $value) {
            $productModel->updateProductPricing($key, $value);
        }
        
        return true;
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
