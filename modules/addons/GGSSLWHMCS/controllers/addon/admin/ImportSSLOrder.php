<?php

namespace MGModule\GGSSLWHMCS\controllers\addon\admin;

use MGModule\GGSSLWHMCS as main;
use MGModule\GGSSLWHMCS\eServices\provisioning\ConfigOptions as C;
use Illuminate\Database\Capsule\Manager as Capsule;

/*
 * Base example
 */

class ImportSSLOrder extends main\mgLibs\process\AbstractController
{
    const PERIODS = array(
        '1'  => 'monthly',
        '3'  => 'quarterly',
        '6'  => 'semiannually',
        '12' => 'annually',
        '24' => 'biennially',
        '36' => 'triennially',
    );

    /**
     * This is default page. 
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function indexHTML($input = [], $vars = [])
    {

        if ($_SERVER['REQUEST_METHOD'] === 'GET')
        {
            $apiConfigRepo = new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository();
            $input         = (array) $apiConfigRepo->get();
        }

        $clients          = array();
        $clientRepisitory = (new\MGModule\GGSSLWHMCS\models\whmcs\clients\Clients());
        $clientRepisitory->sortBy('id', 'asc');
        foreach ($clientRepisitory->get() as $client)
        {
            $clients[$client->getID()] = '#' . $client->getId() . ' ' . $client->getFirstname() . ' ' . $client->getLastname() . ' ' . $client->getCompanyName();
        }
        
        $form = new main\mgLibs\forms\Creator('importSSLOrder');

        $field        = new main\mgLibs\forms\TextField();
        $field->name  = 'order_id';
        $field->value = $input['order_id'];
        $field->error = $this->getFieldError('order_id');
        $form->addField($field);

        $field                   = new main\mgLibs\forms\SelectPicker();
        $field->readonly         = $input['client_id'] ? true : false;
        $field->name             = 'client_id';
        $field->required         = true;
        $field->value            = $input['client_id'];
        $field->translateOptions = false;
        $field->options          = $clients;
        $field->error            = $this->getFieldError('client_id');
        $form->addField($field);

        $form->addField('button', 'importSSL', array(
            'color' => 'success btn-inverse',
            'value' => 'importSSL'
        ));

        $vars['form'] = $form->getHTML();

        return array
            (
            'tpl'  => 'import_ssl_order',
            'vars' => $vars
        );
    }

    public function importSSLJSON($input = [], $vars = [])
    {
        try
        {
            if (!isset($input['order_id']) || trim($input['order_id']) == "")
            {
                throw new \Exception(main\mgLibs\Lang::T('messages', 'order_id_not_provided'));
            }
            if (!isset($input['client_id']) || trim($input['client_id']) == "")
            {
                throw new \Exception(main\mgLibs\Lang::T('messages', 'client_id_not_provided'));
            }

            $sslOrderID = trim($input['order_id']);
            $clientID   = trim($input['client_id']);


            $api = new \MGModule\GGSSLWHMCS\mgLibs\GoGetSSLApi();

            //get order details from API
            $orderStatus = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslOrderID);
            if($orderStatus['status'] == 'cancelled')
                throw new \Exception(main\mgLibs\Lang::T('messages', 'order_cancelled_import_unable'));
         
            $SSLOrder = new main\eModels\whmcs\service\SSL();
            //check if SSL already exist
            if (!empty($SSLOrder->getWhere(array('remoteid' => $sslOrderID))->get()))
            {
                throw new \Exception(main\mgLibs\Lang::T('messages', 'ssl_order_already_exist'));
            }

            //check if ssl order product exist in WHMCS
            $productModel   = new \MGModule\GGSSLWHMCS\models\productConfiguration\Repository();
            $products       = $productModel->getModuleProducts();
            $whmcsProductID = false;
            foreach ($products as $product)
            {
                if ($product->{C::API_PRODUCT_ID} == $orderStatus['product_id'])
                {            
                    $whmcsProductID = $product->id;
                    break;
                }
            }
            if (!$whmcsProductID)
                throw new \Exception(main\mgLibs\Lang::T('messages', 'ssl_order_product_not_exist'));

            //prepare data for create order
            $data        = array(
                'userID'        => $clientID,
                'paymentMethod' => 'banktransfer',
                'productID'     => $whmcsProductID,
                'billingcycle'  => self::PERIODS[$orderStatus['validity_period']],
                'domain'        => $orderStatus['domain'],
                'nextdueDate'   => $orderStatus['valid_till'],
            );
            $invoiceRepo = new main\eHelpers\Invoice();
            $orderInfo   = $invoiceRepo->createOrder($data['userID'], $data['paymentMethod'], $data['productID'], $data['domain'], $data['nextdueDate'], $data['billingcycle']);

            if ($orderInfo['result'] != 'success')
            {
                throw new \Exception(main\mgLibs\Lang::T('messages', 'order_create_error'));
            }


            $newOrderID         = $orderInfo['orderid'];
            $newServiceID       = $orderInfo['productids'];
            //prepare data for ssl order
            $sslOrderConfigData = array(
                //config data column
                'servertype'    => $orderStatus['webserver_type'],
                'csr'           => $orderStatus['csr_code'],
                'firstname'     => $orderStatus['admin_firstname'],
                'lastname'      => $orderStatus['admin_lastname'],
                'orgname'       => $orderStatus['admin_organization'],
                'jobtitle'      => $orderStatus['admin_title'],
                'email'         => $orderStatus['admin_email'],
                'address1'      => $orderStatus['admin_addressline1'],
                'address2'      => $orderStatus['admin_addressline2'],
                'city'          => $orderStatus['admin_city'],
                'state'         => $orderStatus['admin_region'],
                'postcode'      => $orderStatus['admin_postalcode'],
                'country'       => $orderStatus['admin_country'],
                'phonenumber'   => $orderStatus['admin_phone'],
                'fields'        => array(
                    'order_type' => (!$orderStatus['renew']) ? 'new' : 'renew'
                ),
                'approveremail' => $orderStatus['approver_method']
            );
            
            $newSSLOrder = new main\eModels\whmcs\service\SSL();
            $newSSLOrder->setUserId($clientID);
            $newSSLOrder->setServiceId((string)$newServiceID);
            $newSSLOrder->setAddonId(0);
            $newSSLOrder->setRemoteId($sslOrderID);
            $newSSLOrder->setModule('GGSSLWHMCS');
            $newSSLOrder->setCertType('');
            $newSSLOrder->setCompletionDate('0000-00-00 00:00:00');
            $newSSLOrder->setStatus('Completed');
            foreach($sslOrderConfigData as $key => $value)
            {
                $newSSLOrder->setConfigdataKey($key, $value); 
            }
            //get sans domains
            if(isset($orderStatus['domains']))
            {
                $newSSLOrder->setSansDomains($orderStatus['domains']);
                //update sans conig options count
                $this->updateHostingIncludedSans($newServiceID, $orderStatus['domains']);
            }
                
           
            $newSSLOrder->save();            
            
        }
        catch (\Exception $e)
        {
            return[
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return [
            'success' => main\mgLibs\Lang::T('messages', 'import_success')
        ];
    }
    
    private function updateHostingIncludedSans($serviceID ,$sanDomains)
    {
        $domainsCount = count(explode(',', $sanDomains));
        $update = array(
            C::OPTION_SANS_COUNT => $domainsCount
        );
        $CORepo = new main\models\whmcs\service\configOptions\Repository($serviceID, $update);        
        $CORepo->update();
    }

    /**
     * This is custom page. 
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function pageHTML()
    {
        $vars = array();

        return array
            (
            //You have to create tpl file  /modules/addons/GGSSLWHMCS/templates/admin/pages/example1/page.1tpl
            'tpl'  => 'page',
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
    public function ajaxErrorHTML()
    {
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
    public function getErrorArrayJSON()
    {
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
    public function getSuccessArrayJSON()
    {
        return array
            (
            'success' => 'Custom success'
        );
    }
    /*     * ************************************************************************
     * AJAX USING DATA-ACT
     * *********************************************************************** */

    public function ajaxErrorDataActHTML()
    {
        return array
            (
            'tpl' => 'ajaxErrorDataAct'
        );
    }
    /*     * ************************************************************************
     * AJAX CONTENT
     * *********************************************************************** */

    public function ajaxContentHTML()
    {
        return array
            (
            'tpl' => 'ajaxContent'
        );
    }

    public function ajaxContentJSON()
    {
        return array
            (
            'html' => main\mgLibs\Smarty::I()->view('ajaxContentJSON')
        );
    }
}
