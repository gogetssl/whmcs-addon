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
        $conditions = ['invoice_id' => $invoiceId ];
        
        if ($orderIdNull == true) {
            $conditions['order_id'] = 0;
        }
        
        return Query::select(['*'], self::INVOICE_INFOS_TABLE_NAME, $conditions)->fetch();
    }
    protected static function getNewInvoiceCreatedInfo($newServiceId, $orderId = null) {
        $wherePart = ' WHERE new_service_id = ' . $newServiceId;        
        
        if ($orderId != null) {
            $wherePart .= ' AND order_id = ' . $orderId;
        }
        return Query::query('SELECT * FROM ' . self::INVOICE_INFOS_TABLE_NAME . $wherePart)->fetch();
    }
    
    protected function getNextDueDate($currentDueDate, $dateFormat = 'Y-m-d') {
        
        $datetime = new DateTime($currentDueDate);
        
        $datetime->add(new DateInterval('P1Y')) ; //plus 1 year;
        $datetime->sub(new DateInterval('P1D')) ; //plus 1 year;
        return $datetime->format($dateFormat);
    }
    
    public function createInvoice($service, $product) {
        
        $dateFormat = 'Y-m-d';
        
        $dateInvoice = date($dateFormat);
        
        $startDate = $service->nextduedate;
        $endDate = $this->getNextDueDate($service->nextduedate, $dateFormat);
        
        $invoiceItemDescription = $product->name . ($service->domain ? ' - ' . $service->domain : '' ) . ' (' . $startDate . ' - ' . $endDate . ') - Renewal';
                
        $postData = array(
            'userid' => $service->userid,
            'sendinvoice' => true,
            'date' => $dateInvoice,
            'duedate' => $dateInvoice,
            'itemdescription1' => $invoiceItemDescription,
            'itemamount1' => $service->amount,
        );
        
        $adminUserName = Admin::getAdminUserName();

        $results = localAPI('CreateInvoice', $postData, $adminUserName);
        
        $invoiceId = $results['invoiceid'];
        
        $this->saveInvoiceInfo($service->userid, $invoiceId, $service->id, $product->id);
        
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
}
