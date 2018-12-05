<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\admin;

//ini_set("xdebug.overload_var_dump", "off");

use MGModule\SSLCENTERWHMCS as main;
use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions as C;
use Illuminate\Database\Capsule\Manager as Capsule;

/*
 * Base example
 */

class UserCommissions extends main\mgLibs\process\AbstractController
{
    //public $tableName = 'mgfw_SSLCENTER_user_commission';

    /**
     * This is default page. 
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function indexHTML($input = [], $vars = [])
    {

        //get all clients
        $vars['clients'] = array();

        $clietnsRepo = new \MGModule\SSLCENTERWHMCS\models\whmcs\clients\Clients();
        foreach ($clietnsRepo->get() as $key => $client)
        {
            $vars['clients'][] = array(
                'id'   => $client->id,
                'name' => trim($client->firstname . ' ' . $client->lastname . ' ' . $client->companyname),
            );
        }
        $vars['templatePath1']  = ROOTDIR . DS . 'modules' . DS . 'templates' . DS . 'orderforms' . DS . 'YOUR_TEMPLATE' . DS . 'configureproduct.tpl';
        $vars['templatePath2']  = ROOTDIR . DS . 'modules' . DS . 'templates' . DS . 'orderforms' . DS . 'YOUR_TEMPLATE' . DS . 'products.tpl';
        
        return array
            (
            'tpl'  => 'userCommissions',
            'vars' => $vars
        );
    }

    public function addNewCommissionRuleJSON($input = [], $vars = [])
    {
        try
        {
            if (!isset($input['client_id']) OR ! trim($input['client_id']))
                throw new \Exception(main\mgLibs\Lang::T('messages', 'clientIDNotProvided'));
            if (!isset($input['product_id']) OR ! trim($input['product_id']))
                throw new \Exception(main\mgLibs\Lang::T('messages', 'productIDNotProvided'));
            if (!isset($input['commission']) OR ! trim($input['commission']))
                throw new \Exception(main\mgLibs\Lang::T('messages', 'commissionIDNotProvided'));

            $clientID   = $input['client_id'];
            $productID  = $input['product_id'];
            $commission = $input['commission'];

            $commissionRule = new main\models\userCommission\UserCommission();
            $commissionRule->setClientID($clientID);
            $commissionRule->setProductID($productID);
            $commissionRule->setCommission((float) $commission / 100);
            $commissionRule->save();
        }
        catch (\Exception $e)
        {
            return [
                'error' => true,
                'msg'   => $e->getMessage()
            ];
        }

        return [
            'success' => main\mgLibs\Lang::T('messages', 'addSuccess')
        ];
    }

    public function removeCommissionRuleJSON($input = [], $vars = [])
    {
        try
        {
            if (!isset($input['rule_id']) OR ! trim($input['rule_id']))
                throw new \Exception(main\mgLibs\Lang::T('messages', 'ruleIDNotProvided'));

            $ruleID = $input['rule_id'];

            $commissionRule = new main\models\userCommission\UserCommission($ruleID);
            $commissionRule->delete();
        }
        catch (\Exception $e)
        {
            return [
                'error' => true,
                'msg'   => $e->getMessage()
            ];
        }

        return [
            'success' => main\mgLibs\Lang::T('messages', 'removeSuccess')
        ];
    }
    
    public function updateCommissionRuleJSON($input = [], $vars = [])
    {
        try
        {
            if (!isset($input['rule_id']) OR ! trim($input['rule_id']))
                throw new \Exception(main\mgLibs\Lang::T('messages', 'ruleIDNotProvided'));
            if (!isset($input['commission']) OR ! trim($input['commission']))
                throw new \Exception(main\mgLibs\Lang::T('messages', 'commissionIDNotProvided'));

            $ruleID = $input['rule_id'];            
            $commission = $input['commission'];

            $commissionRule = new main\models\userCommission\UserCommission($ruleID);
            $commissionRule->setCommission((float) $commission / 100);
            $commissionRule->save();
        }
        catch (\Exception $e)
        {
            return [
                'error' => true,
                'msg'   => $e->getMessage()
            ];
        }

        return [
            'success' => main\mgLibs\Lang::T('messages', 'updateSuccess')
        ];
    }

    public function getCommissionRulesJSON($input = [], $vars = [])
    {
        try
        {
            $data['data']       = array();
            $userCommissionRepo = new \MGModule\SSLCENTERWHMCS\models\userCommission\Repository();
            foreach ($userCommissionRepo->get() as $rule)
            {
                $data['data'][] = $this->formatRow('row', $rule);
            }
        }
        catch (\Exception $ex)
        {
            return [
                'error' => $ex->getMessage()
            ];
        }

        return $data;
    }

    public function getSingleCommissionRuleJSON($input = [], $vars = [])
    {
        try
        {
            $data = array();
            if (!isset($input['rule_id']) OR ! trim($input['rule_id']))
                throw new \Exception(main\mgLibs\Lang::T('messages', 'ruleIDNotProvided'));

            $ruleID = $input['rule_id'];

            $commissionRule = new main\models\userCommission\UserCommission($ruleID);
            $data  = array(
                'client' => $this->getClient($commissionRule->getClientID()),
                'product' => $this->getProduct($commissionRule->getProductID()),
                'commission' => (float)$commissionRule->getCommission() * 100,
                'pricings' => $this->loadPricing($commissionRule->getProductID(), $commissionRule->getCommission(), true)
            );
            
        }
        catch (\Exception $ex)
        {
            return [
                'error' => $ex->getMessage()
            ];
        }

        return $data;
    }

    public function loadProductPricingJSON($input = [], $vars = [])
    {
        try
        {
            if (!isset($input['product_id']) OR ! trim($input['product_id']))
                throw new \Exception(main\mgLibs\Lang::T('messages', 'productIDNotProvided'));

            $productID = $input['product_id'];

            $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
            $pricings     = $productModel->getProductPricing($productID);

            $ppricings = array();
            foreach ($pricings as $price)
            {
                $ppricings[] = array(
                    'code'         => $price->code,
                    'monthly'      => (!in_array($price->monthly, ['-1.00'/* , '0.00' */])) ? $price->monthly : '-',
                    'quarterly'    => (!in_array($price->quarterly, ['-1.00'])) ? $price->quarterly : '-',
                    'semiannually' => (!in_array($price->semiannually, ['-1.00'])) ? $price->semiannually : '-',
                    'annually'     => (!in_array($price->annually, ['-1.00'])) ? $price->annually : '-',
                    'biennially'   => (!in_array($price->biennially, ['-1.00'])) ? $price->biennially : '-',
                    'triennially'  => (!in_array($price->triennially, ['-1.00'])) ? $price->triennially : '-',
                );
            }
        }
        catch (\Exception $ex)
        {
            return [
                'error' => $ex->getMessage()
            ];
        }

        return [
            'pricings' => $ppricings
        ];
    }

    public function loadAvailableProductsJSON($input = [], $vars = [])
    {
        try
        {
            $products     = array();
            $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
            //get sslcenter all products
            foreach ($productModel->getModuleProducts() as $product)
            {
                //skip free products
                if($product->paytype == 'free')
                    continue;

                //exclude products for which commision is already added for gicen client
                $userCommissionRepo = new \MGModule\SSLCENTERWHMCS\models\userCommission\Repository();
                $userCommissionRepo->onlyProductID($product->id);

                if (isset($input['client_id']))
                    $userCommissionRepo->onlyClientID($input['client_id']);

                if ($userCommissionRepo->count() > 0)
                    continue;

                $products[] = array(
                    'id'       => $product->id,
                    'name'     => $product->name,
                    'pricings' => $product->pricing
                );
            }
        }
        catch (\Exception $ex)
        {
            return [
                'error' => $ex->getMessage()
            ];
        }

        return [
            'products' => $products
        ];
    }

    private function formatRow($template, $item)
    {
        //get client details
        $client = $this->getClient($item->getClientID());

        //get product details
        $product      = $this->getProduct($item->getProductID());
        //load product pricing
        $pricings     = $this->loadPricing($item->getProductID(), $item->getCommission());
        
        $data['client']     = $client;
        $data['rule_id']    = $item->getID();
        $data['product']    = $product;
        $data['commission'] = (float) $item->getCommission() * 100;
        $data['pricings']   = $pricings;

        $rows = $this->dataTablesParseRow($template, $data);

        return $rows;
    }

    private function getClient($id)
    {
        $clientDetails = new \MGModule\SSLCENTERWHMCS\models\whmcs\clients\Client($id);

        return array(
            'id'   => $clientDetails->id,
            'name' => trim($clientDetails->firstname . ' ' . $clientDetails->lastname . ' ' . $clientDetails->companyname),
        );
    }

    private function getProduct($id)
    {
        $productDetails = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($id);

        return array(
            'id'   => $productDetails->id,
            'name' => $productDetails->name,
        );
    }

    private function loadPricing($productID, $comminssion, $returnNoneIfNotSetOrNull = false)
    {
        $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
        $pricings     = $productModel->getProductPricing($productID);
        foreach ($pricings as &$price)
        {
            $price->commission_monthly      = (!in_array($price->monthly, ['-1.00', '0.00'])) ? (string) ((float) $price->monthly + (float) $price->monthly * (float) $comminssion) : '0.00';
            $price->commission_quarterly    = (!in_array($price->quarterly, ['-1.00', '0.00'])) ? (string) ((float) $price->quarterly + (float) $price->quarterly * (float) $comminssion) : '0.00';
            $price->commission_semiannually = (!in_array($price->semiannually, ['-1.00', '0.00'])) ? (string) ((float) $price->semiannually + (float) $price->semiannually * (float) $comminssion) : '0.00';
            $price->commission_annually     = (!in_array($price->annually, ['-1.00', '0.00'])) ? (string) ((float) $price->annually + (float) $price->annually * (float) $comminssion) : '0.00';
            $price->commission_biennially   = (!in_array($price->biennially, ['-1.00', '0.00'])) ? (string) ((float) $price->biennially + (float) $price->biennially * (float) $comminssion) : '0.00';
            $price->commission_triennially  = (!in_array($price->triennially, ['-1.00', '0.00'])) ? (string) ((float) $price->triennially + (float) $price->triennially * (float) $comminssion) : '0.00';
            
            if($returnNoneIfNotSetOrNull)
            {
                $price->monthly = (!in_array($price->monthly, ['-1.00', '0.00'])) ? $price->monthly  : '-';
                $price->quarterly = (!in_array($price->quarterly, ['-1.00', '0.00'])) ? $price->quarterly  : '-';
                $price->semiannually = (!in_array($price->semiannually, ['-1.00', '0.00'])) ? $price->semiannually  : '-';
                $price->annually = (!in_array($price->annually, ['-1.00', '0.00'])) ? $price->annually  : '-';
                $price->biennially = (!in_array($price->biennially, ['-1.00', '0.00'])) ? $price->biennially  : '-';
                $price->triennially = (!in_array($price->triennially, ['-1.00', '0.00'])) ? $price->triennially  : '-';
            }            
        }
        
        return $pricings;
    }
}
