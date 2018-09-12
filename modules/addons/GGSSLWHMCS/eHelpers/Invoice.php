<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MGModule\GGSSLWHMCS\eHelpers;

use \DateInterval;
use \DateTime;
use \MGModule\GGSSLWHMCS\mgLibs\MySQL\Query;

/**
 * Description of Invoice
 *
 * @author Rafal Sereda <rafal.se at modulesgarden.com>
 */
class Invoice
{
    protected static $adminUserName = null;
    
    const INVOICE_INFOS_TABLE_NAME = 'mgfw_ggssl_invoices_info';
    
    public static function createInfosTable() {
        
        Query::query('CREATE TABLE IF NOT EXISTS `' . self::INVOICE_INFOS_TABLE_NAME . '` (
                        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                        `user_id` int(10) unsigned NOT NULL,
                        `invoice_id` int(10) unsigned NOT NULL,
                        `service_id` int(10) unsigned NOT NULL,
                        `product_id` int(10) unsigned NOT NULL,
                        `order_id` int(10) unsigned NOT NULL,
                        `new_service_id` int(10) unsigned NOT NULL,
                        `status` varchar(10) NOT NULL,
                        `created_at` datetime NOT NULL,
                        `updated_at` datetime NOT NULL,
                        PRIMARY KEY (`id`),
                        KEY `service_id` (`service_id`),
                        KEY `new_service_id` (`new_service_id`,`order_id`),
                        KEY `invoice_id` (`invoice_id`,`order_id`)
                       ) ENGINE=InnoDB ');
    }
    
    public function checkInvoiceAlreadyCreated($serviceIDs) {
        $services = Query::select(['service_id', 'id'], self::INVOICE_INFOS_TABLE_NAME, ['service_id' => $serviceIDs ])->fetchAll();
        
        $result = [];
        foreach ($services as $srvinfo) {
            $result[$srvinfo['service_id']] = $srvinfo;
        }
        return $result;
    }
    
    public function getInvoiceCreatedInfo($invoiceId, $orderIdNull = true) {
        
        $wherePart = ' WHERE invoice_id = ' . $invoiceId;        
        
        if ($orderIdNull == true) {
            $wherePart .= ' AND order_id = 0 ';
        }
        return Query::query('SELECT * FROM ' . self::INVOICE_INFOS_TABLE_NAME . $wherePart)->fetch();
    }
    
    protected static function getNewInvoiceCreatedInfo($newServiceId, $orderId = null) {
        $wherePart = ' WHERE new_service_id = ' . $newServiceId;        
        
        if ($orderId != null) {
            $wherePart .= ' AND order_id = ' . $orderId;
        }
        return Query::query('SELECT * FROM ' . self::INVOICE_INFOS_TABLE_NAME . $wherePart)->fetch();
    }
    
    public static function getLatestCreatedInvoiceInfo($serviceId) {
        $wherePart = ' WHERE service_id = ' . $serviceId;        
        
        return Query::query('SELECT invoice_id FROM ' . self::INVOICE_INFOS_TABLE_NAME . $wherePart . ' ORDER BY id DESC LIMIT 1')->fetch();
    }
    protected function getNextDueDate($currentDueDate, $dateFormat = 'Y-m-d') {
        
        $datetime = new DateTime($currentDueDate);
        
        $datetime->add(new DateInterval('P1Y')) ; //plus 1 year;
        $datetime->sub(new DateInterval('P1D')) ; //plus 1 year;
        return $datetime->format($dateFormat);
    }
    
    public function createInvoice($service, $product, $returnInvoiceID = false) {
        
        $dateFormat = 'Y-m-d';
        
        $dateInvoice = date($dateFormat);
        
        if($service->billingcycle != 'One Time'){
            $itemamount = $service->amount;
            $startDate = $service->nextduedate;
            $endDate = $this->getNextDueDate($service->nextduedate, $dateFormat);

            $invoiceItemDescription = $product->name . ($service->domain ? ' - ' . $service->domain : '' ) . ' (' . $startDate . ' - ' . $endDate . ') - Renewal';
        } 
        else
        {      
            //get client currency id
            $clientRepo = new \MGModule\GGSSLWHMCS\models\whmcs\clients\Client($service->userid);
            $clientCurrencyID = $clientRepo->getCurrencyId();
            
            //get product pricing
            $productID = $service->packageid;           
            $productRepo = new \MGModule\GGSSLWHMCS\models\productConfiguration\Repository();     
            $productPricing = $productRepo->getProductPricing($productID);
            //get proper pricing related to client pricing
            foreach($productPricing as $pricing)
            {
                if($pricing->currency == $clientCurrencyID)
                {
                    $itemamount = ($pricing->monthly == '-1.00') ? 0 : $pricing->monthly;
                }
            }
            
            $invoiceItemDescription = $product->name . ($service->domain ? ' - ' . $service->domain : '' ) .' - Renewal';
        }

        $postData = array(
            'userid' => $service->userid,
            'sendinvoice' => true,
            'date' => $dateInvoice,
            'duedate' => $dateInvoice,
            'itemdescription1' => $invoiceItemDescription,
            'itemamount1' => $itemamount,
        );
        
        $adminUserName = Admin::getAdminUserName();

        $results = localAPI('CreateInvoice', $postData, $adminUserName);
        
        $invoiceId = $results['invoiceid'];
        
        $this->saveInvoiceInfo($service->userid, $invoiceId, $service->id, $product->id);
       
        if($returnInvoiceID)
            return $invoiceId;
            
        return $results['result'] == 'success';
    }
    
    public function invoicePaid($invoicId) {
     
        Query::useCurrentConnection();
        
        $invoiceInfo = $this->getInvoiceCreatedInfo($invoicId);
        
        if (empty($invoiceInfo)) {
            return false;
        }
        
        //collect invoice info payment method
        $invoice = \WHMCS\Billing\Invoice::find($invoicId);
        $service = \WHMCS\Service\Service::find($invoiceInfo['service_id']);
        
        $orderInfo = $this->createOrder($invoice->userid, $invoice->paymentmethod, $invoiceInfo['product_id'], $service->domain, $this->getNextDueDate($service->nextduedate), $service->billingcycle );
        
        if ($orderInfo['result'] != 'success') {
            return false;
        }
        
        $productId = $orderInfo['productids'];
        $newOrderId = $orderInfo['orderid'];
        
        if ($productId <= 0) {
            return false;
        }
        Query::update(self::INVOICE_INFOS_TABLE_NAME, ['order_id' => $newOrderId, 'new_service_id' => $productId, 'status' => 'added'], ['id' => $invoiceInfo['id']]);
        //link invoice id with newly created order
        Query::update('tblorders', ['invoiceid' => $invoicId], ['id' => $newOrderId]);
        
        $moduleCreated = $this->moduleCreate($productId);
        if ($moduleCreated) {
            Query::update(self::INVOICE_INFOS_TABLE_NAME, ['status' => 'crated'], ['id' => $invoiceInfo['id']]);
        }
    }
    
    public function createOrder($userId, $paymentMethod, $productID, $domain, $dueDateNewOrder, $serviceBillingCycle) {
        $command = 'AddOrder';
        $postData = array(
            'clientid' => $userId,
            'paymentmethod' => $paymentMethod,
            'pid' => array($productID),
            'domain' => array($domain),
            'billingcycle' => array(strtolower($serviceBillingCycle)),
            'noinvoice' => true,
            'noinvoiceemail' => true,
            'noemail' => true,
        );
        
        $adminUsername = Admin::getAdminUserName();

        $results = localAPI($command, $postData, $adminUsername);
        
        if ($results['result'] == 'success') {
            $postData = array(
                'serviceid' => $results['productids'],
                'nextduedate' => $dueDateNewOrder,
                'status' => 'Active',
            );
            localAPI('UpdateClientProduct', $postData, $adminUsername);
        }
        
        return $results;
    }
    
    public function moduleCreate($serviceId) {
        $command = 'ModuleCreate';
        $postData = array(
            'accountid' => $serviceId,
        );
        $adminUsername = Admin::getAdminUserName();

        $results = localAPI($command, $postData, $adminUsername);
        
        return $results['result'] == 'success';
    }
    
    public static function markPreviousOrderAsCompleted($serviceID) {
        
        $invoiceInfo = self::getNewInvoiceCreatedInfo($serviceID);
        
        if ($invoiceInfo == null) {
            return false;
        }
        
        $service = \WHMCS\Service\Service::find($invoiceInfo['service_id']);
        $service->status = 'Completed';
        $service->save();
        
        Query::update(self::INVOICE_INFOS_TABLE_NAME, ['status' => 'configured'], ['id' => $invoiceInfo['id']]);
        
        return true;
    }
    
    protected function saveInvoiceInfo($userId, $invoiceId, $serviceId, $productId) {
        Query::insert(self::INVOICE_INFOS_TABLE_NAME, [
            'user_id' => $userId, 'invoice_id' => $invoiceId, 'service_id' => $serviceId, 'product_id' => $productId, 'created_at' => date('Y-m-d H:i:s')
        ]);
        
    }
    
    public static function insertDomainInfoIntoInvoiceItemDescription($serviceID, $domain, $checkIfAlreadyIncluded = false)
    {        
        try
            {$service = new \MGModule\GGSSLWHMCS\models\whmcs\service\Service($serviceID);       
            $whmcsProduct = $service->product();

            if($whmcsProduct->getShowDomainOptions() || $whmcsProduct->getPayType() == 'free')
                return;
            //get invoice related with order
            $whmcsOrder = $service->order();
            $invoice = $whmcsOrder->invoice();
            $invoiceItemsRepo = new \MGModule\GGSSLWHMCS\models\whmcs\invoices\RepositoryItem();        
            $invoiceItemsRepo->onlyInvoiceId($invoice->getId())->onlyServiceId($serviceID);
            $serviceInvoiceItems = $invoiceItemsRepo->get();
            foreach ($serviceInvoiceItems as  $item)
            {     
                $newDescription = '';    
                $domainInfo = $whmcsProduct->getName() . ' - ' . $domain;
                if($checkIfAlreadyIncluded && $domainIncluded = self::checkIfAddedDomainInfoInInvoiceItemDescription($item->getDescription(), $whmcsProduct->getName()))
                {    

                    $newDescription = str_replace($whmcsProduct->getName() . ' ' . $domainIncluded, $domainInfo, $item->getDescription());
                }
                else
                    $newDescription = str_replace($whmcsProduct->getName(), $domainInfo, $item->getDescription());
                if($newDescription)
                {
                    $oldDescription = $item->getDescription();
                    $item->setDescription($newDescription);
                    $item->save();

                    logActivity("GGSSL WHMCS: Description of the invoice item for Invoice ID: " . $invoice->getId() . ' has been changed from "' . $oldDescription . '" to "' . $newDescription . '"');
                }
            } 
        }
        catch(\Exception $e)
        {            
            return;
        }        
    }
    private static function checkIfAddedDomainInfoInInvoiceItemDescription($itemDescription, $productName)
    {
        $itemDescription - str_replace($productName, '', $itemDescription);

        $start  = strpos($itemDescription, ' - ');    
        if($start === false)
            return false;
       
        
        $end    = strpos($itemDescription, '(', $start + 1);
        if($end === false)
            $end = strpos($itemDescription, ' Setup Fee', $start + 1);
                if($end === false)
                    $end = strpos($itemDescription, PHP_EOL, $start + 1);
                        if($end === false)
                            return ltrim($itemDescription, ' - ');
        
        $length = $end - $start;
        if($length == NULL)
            return false;
        
        $domain = substr($itemDescription, $start + 1, $length - 1);
        if(trim($domain) == '')
            return false;
        
        return $domain;
    }
}
