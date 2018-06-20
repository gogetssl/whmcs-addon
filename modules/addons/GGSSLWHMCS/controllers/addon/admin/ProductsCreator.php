<?php

namespace MGModule\GGSSLWHMCS\controllers\addon\admin;

use MGModule\GGSSLWHMCS as main;
use MGModule\GGSSLWHMCS\eServices\provisioning\ConfigOptions as C;
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
            $this->apiProductsRepo = main\eRepository\gogetssl\Products::getInstance();
            $productModel = new \MGModule\GGSSLWHMCS\models\productConfiguration\Repository();
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
        
        $productData = [
            'type'       => 'hostingaccount',
            'gid'        => $input['gid'],
            'name'       => $input['name'],
            'paytype'    => $input['paytype'] ? $input['paytype'] : 'recurring',
            'servertype' => 'GGSSLWHMCS',
            'hidden'     => '1',
            'autosetup'  => $input['autosetup'],
            C::API_PRODUCT_ID => $input[C::API_PRODUCT_ID],
            C::API_PRODUCT_MONTHS => $input[C::API_PRODUCT_MONTHS],
            C::PRODUCT_ENABLE_SAN => $input[C::PRODUCT_ENABLE_SAN] ? $input[C::PRODUCT_ENABLE_SAN] : '',
            C::PRODUCT_INCLUDED_SANS => $input[C::PRODUCT_INCLUDED_SANS] ? $input[C::PRODUCT_INCLUDED_SANS] : 0,
        ];
        $productModel = new \MGModule\GGSSLWHMCS\models\productConfiguration\Repository();
        $newProductId = $productModel->createNewProduct($productData);
        foreach ($input['currency'] as $key => $value) {
            $value['relid'] = $newProductId;
            $productModel->createPricing($value);
        }
        
        $apiProduct = $this->apiProductsRepo->getProduct($input[C::API_PRODUCT_ID]);
        
        if($apiProduct->isSanEnabled() AND $input[C::PRODUCT_ENABLE_SAN] === 'on') {
            main\eServices\ConfigurableOptionService::createForProduct($newProductId, $productData['name']);
        }
    }
    
    public function saveProducts($currencies, $post) {
        
        $apiProducts = $this->apiProductsRepo->getAllProducts();
        $productModel = new \MGModule\GGSSLWHMCS\models\productConfiguration\Repository();
        $moduleProducts = $productModel->getModuleProducts('GGSSLWHMCS', $post['gid']);
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
            $input[C::PRODUCT_INCLUDED_SANS] = '0';
            $input['paytype'] = $apiProduct->getPayType();
            $input['currency'] = $dummyCurrencies;
            $input['autosetup'] = ($apiProduct->getPayType() == 'free') ? 'order' : 'payment' ;
            $this->saveProduct($input);
        }

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

                $apiConfigRepo = new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository();
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
            //You have to create tpl file  /modules/addons/GGSSLWHMCS/templates/admin/pages/example1/page.1tpl
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
