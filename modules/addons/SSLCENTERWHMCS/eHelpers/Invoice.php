<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use \DateInterval;
use \DateTime;
use \MGModule\SSLCENTERWHMCS\mgLibs\MySQL\Query;
use \MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Description of Invoice
 *
 * @author Rafal Sereda <rafal.se at modulesgarden.com>
 */
class Invoice
{
    protected static $adminUserName = null;
    
    const INVOICE_INFOS_TABLE_NAME = 'mgfw_SSLCENTER_invoices_info';
    
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
    protected function getNextDueDate($nextduedate, $dateFormat = 'Y-m-d', $timeShift = 'P12M') {
        $datetime = new DateTime($nextduedate);
        
        $datetime->add(new DateInterval($timeShift)) ; //plus 1 year by default;
        $datetime->sub(new DateInterval('P1D')) ; //plus 1 year;
        
        return $datetime->format($dateFormat);
    }
    
    protected function getClientCurrencyID($id)
    {
        $clientRepo = new \MGModule\SSLCENTERWHMCS\models\whmcs\clients\Client($id);
        return $clientRepo->getCurrencyId();
    }
    
    protected function getProductPricing($id)
    {
        $productRepo = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();     
        return $productRepo->getProductPricing($id);
    }
    
    protected function getProperServiceBillingCycleBasedOnProductPricing($service)
    {
        //get client currency
        $clientCurrencyID = $this->getClientCurrencyID($service->userid);
        //get product pricing
        $productPricing = $this->getProductPricing($service->packageid);   
        foreach($productPricing as $pricing)
        {            
            //check if pricing currency is clients currency and if service pricing period is setted
            if($pricing->currency == $clientCurrencyID && $pricing->{strtolower($service->billingcycle)} != '-1.00')
                return [
                    'key' => $service->billingcycle, 
                    'price' => $pricing->{strtolower($service->billingcycle)}
                ];
        }
        $highestAvaialblePeriod = $highestAvaialblePeriodPrice = 0;
        foreach($productPricing as $pricing)
        { 
            if($pricing->currency == $clientCurrencyID)
            {
                foreach($pricing as $key => $priceFieldValue)
                { 
                    if(key_exists($key, \MGModule\SSLCENTERWHMCS\models\whmcs\pricing\BillingCycle::PERIODS) && $priceFieldValue != '-1.00')
                    {                        
                        $period = \MGModule\SSLCENTERWHMCS\models\whmcs\pricing\BillingCycle::convertStringToPeriod($key);
                        
                        if($highestAvaialblePeriod < $period)
                        {
                            $highestAvaialblePeriod = $period;  
                            $highestAvaialblePeriodPrice = $priceFieldValue;
                        }
                    }                    
                }
            }            
        }
                
        // return highest available        
        return [
            'key' => \MGModule\SSLCENTERWHMCS\models\whmcs\pricing\BillingCycle::convertPeriodToName($highestAvaialblePeriod) ,
            'price' => $highestAvaialblePeriodPrice
        ];
    }
    
    public function createInvoice($service, $product, $returnInvoiceID = false) {
        
        $dateFormat = 'Y-m-d';
        
        $dateInvoice = date($dateFormat);
        //get product commission / client commission        
        $commission = \MGModule\SSLCENTERWHMCS\eHelpers\Commission::getCommissionValue(array('pid' => $service->packageid, 'client' => $service->userid));
        
        $configoptions = array();
        $sanCountCODetails = $this->getSanCountConfigOptionServiceDetails($service);
        $boughtSans = 0;
        if(!empty($sanCountCODetails))
        {
            $boughtSans = $sanCountCODetails['boughtSans'];
            $sanCountConfigOptionID = $sanCountCODetails['configOptionID'];
            $sanCountFrendlyName =  $sanCountCODetails['frendlyName'];
            $configbillingcycle  = $sanCountCODetails['configBillingCycle'];
            
        }        
        //get client currency id
        $clientCurrencyID = $this->getClientCurrencyID($service->userid);    
        if($service->billingcycle != 'One Time')
        {   
            /*
             * check if product support it(if is setted related pricing period) 
             * if not select the highest available            
             */        
            $availableBillingCycle = $this->getProperServiceBillingCycleBasedOnProductPricing($service);           
            if(in_array($availableBillingCycle['key'], array('free', 'Free Account', 'free account')))
            {
                $productConfiguration = Capsule::table("tblproducts")->where("tblproducts.servertype", "=", "SSLCENTERWHMCS")->where("id", "=", $service->packageid)->first();
                $timeShift = 'P' . $productConfiguration->{ConfigOptions::API_PRODUCT_MONTHS} . 'M';            
            }
            else
                $timeShift = 'P' . \MGModule\SSLCENTERWHMCS\models\whmcs\pricing\BillingCycle::convertStringToPeriod($availableBillingCycle['key']) . 'M';
            
            //get current product amount
            $itemamount = $availableBillingCycle['price'];          
            $startDate = $service->nextduedate;
            $endDate = $this->getNextDueDate($service->nextduedate, $dateFormat, $timeShift);            
            $invoiceItemDescription = $product->name . ($service->domain ? ' - ' . $service->domain : '' ) . ' (' . $startDate . ' - ' . $endDate . ') - Renewal';
        } 
        else
        {              
            //get product pricing         
            $productPricing = $this->getProductPricing($service->packageid);   
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
        
        //modify item amount based on product commission / client commission       
        $postData = array(
            'userid' => $service->userid,
            'sendinvoice' => true,
            'date' => $dateInvoice,
            'duedate' => $dateInvoice,
            'itemdescription1' => $invoiceItemDescription,
            'itemamount1' => (float)$itemamount + (float)$itemamount * (float)$commission,
            'itemtaxed1' => $product->tax
        );
        
        if($boughtSans > 0)
        {            
            $qtyprice = get_query_val("tblpricing", strtolower($configbillingcycle), array( "type" => "configoptions", "currency" => $clientCurrencyID, "relid" => $sanCountConfigOptionID ));
            $optionname .= formatCurrency($qtyprice);

            $postData['itemdescription2'] = $sanCountFrendlyName . ': ' .  $boughtSans . ' x ' . $optionname;
            $postData['itemamount2'] = $qtyprice;
            $postData['itemtaxed2'] = $product->tax;
            
        }
        
        $adminUserName = Admin::getAdminUserName();

        $results = localAPI('CreateInvoice', $postData, $adminUserName);
        
        $invoiceId = $results['invoiceid'];
        
        $this->saveInvoiceInfo($service->userid, $invoiceId, $service->id, $product->id);
       
        Capsule::table('tblinvoiceitems')
                ->where('invoiceid', '=', $invoiceId)
                ->update(array('type' => 'Hosting'));
        //add relid to invoiceitem entry in the tblinvoiceitems table -> WHMCS do not fill this column via local API CreateInvoice command
        Capsule::table('tblinvoiceitems')
                ->where('invoiceid', '=', $invoiceId)
                ->update(array('relid' => $service->id));
        
        Capsule::table('tblinvoices')->where('id', '=', $invoiceId)->update(array('status' => 'Payment Pending'));
        
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
        
        $configoptions = array();
        $sanCountCODetails = $this->getSanCountConfigOptionServiceDetails($service);
        if(!empty($sanCountCODetails))
        {
            $configoptions = array(
                $sanCountCODetails['configID'] => array(
                    'optionid' => $sanCountCODetails['configOptionID'],
                    'qty'   => $sanCountCODetails['boughtSans']
                )
            );
        }
        

        $orderInfo = $this->createOrder($invoice->userid, $invoice->paymentmethod, $invoiceInfo['product_id'], $service->domain, $this->getNextDueDate($service->nextduedate), $service->billingcycle , $configoptions);
                
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
            $this->CopyConfigurationAndSendRenewToGGSSL($newOrderId);

        }
    }
    
    
    public function CopyConfigurationAndSendRenewToGGSSL($newOrderId)
    {
        $apiConfigRepo = new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository();
        $input         = (array) $apiConfigRepo->get();
        if(!$input['automatic_processing_of_renewal_orders'])
        {
            return false;
        }
        
        $invoiceInfo = Capsule::table(self::INVOICE_INFOS_TABLE_NAME)->where('order_id', $newOrderId)->first();
        if(!isset($invoiceInfo->service_id) || empty($invoiceInfo->service_id))
        {
            return false;
        }
        
        $serviceId = $invoiceInfo->service_id;
        
        $sslOrderInfo = Capsule::table('tblsslorders')->where('serviceid', $serviceId)->first();
        if(!isset($sslOrderInfo->remoteid) || empty($sslOrderInfo->remoteid))
        {
            return false;
        }
        
        $sslOrder = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslOrderInfo->remoteid);

        $order               = [];
        $order['dcv_method'] = $sslOrder['dcv_method'];
        $order['product_id'] = $sslOrder['product_id'];
        $order['period']     = $sslOrder['validity_period'];
        $order['csr']        = $sslOrder['csr_code'];
        $order['server_count']       = $sslOrder['server_count'];
        $order['approver_email']     = $sslOrder['approver_method'];
        $order['webserver_type']     = $sslOrder['webserver_type'];
        $order['admin_firstname']    = $sslOrder['admin_firstname'];
        $order['admin_lastname']     = $sslOrder['admin_lastname'];
        $order['admin_organization'] = $sslOrder['admin_organization'];
        $order['admin_title']        = $sslOrder['admin_title'];
        $order['admin_addressline1'] = $sslOrder['admin_addressline1'];
        $order['admin_phone']        = $sslOrder['admin_phone'];
        $order['admin_email']        = $sslOrder['admin_email'];
        $order['admin_city']         = $sslOrder['admin_city'];
        $order['admin_country']      = $sslOrder['admin_country'];
        $order['admin_postalcode']   = $sslOrder['admin_postalcode'];
        $order['admin_region']       = $sslOrder['admin_region'];

     
        $order['tech_firstname']    = $sslOrder['tech_firstname'];
        $order['tech_lastname']     = $sslOrder['tech_lastname'];
        $order['tech_organization'] = $sslOrder['tech_organization'];
        $order['tech_addressline1'] = $sslOrder['tech_addressline1'];
        $order['tech_phone']        = $sslOrder['tech_phone'];
        $order['tech_title']        = $sslOrder['tech_title'];
        $order['tech_email']        = $sslOrder['tech_email'];
        $order['tech_city']         = $sslOrder['tech_city'];
        $order['tech_country']      = $sslOrder['tech_country'];
        $order['tech_fax']          = $sslOrder['tech_fax'];
        $order['tech_postalcode']   = $sslOrder['tech_postalcode'];
        $order['tech_region']       = $sslOrder['tech_region'];
        
        if(isset($sslOrder['org_name']) && !empty($sslOrder['org_name']))
        {
            $order['org_name'] = $sslOrder['org_name'];
        }
        if(isset($sslOrder['org_division']) && !empty($sslOrder['org_division']))
        {
            $order['org_division'] = $sslOrder['org_division'];
        }
        if(isset($sslOrder['org_duns']) && !empty($sslOrder['org_duns']))
        {
            $order['org_duns'] = $sslOrder['org_duns'];
        }
        if(isset($sslOrder['org_addressline1']) && !empty($sslOrder['org_addressline1']))
        {
            $order['org_addressline1'] = $sslOrder['org_addressline1'];
        }
        
        $order['org_city']         = $sslOrder['org_city'];
        $order['org_country']      = $sslOrder['org_country'];
        $order['org_fax']          = $sslOrder['org_fax'];
        $order['org_phone']        = $sslOrder['org_phone'];
        $order['org_postalcode']   = $sslOrder['org_postalcode'];
        $order['org_region']       = $sslOrder['org_region'];
        
        if(isset($sslOrder['domains']) && !empty($sslOrder['domains']))
        {
            $order['dns_names']       = $sslOrder['domains'];
        }
        $order['approver_emails'] = $sslOrder['approver_emails'];
        
        $configdata = json_encode(array(
            'servertype' => $sslOrder['webserver_type'],
            'csr' => $sslOrder['csr_code'],
            'firstname' => $sslOrder['tech_firstname'],
            'lastname' => $sslOrder['tech_lastname'],
            'orgname' => $sslOrder['tech_organization'],
            'jobtitle' => $sslOrder['tech_title'],
            'email' => $sslOrder['tech_email'],
            'address1' => $sslOrder['tech_addressline1'],
            'address2' => $sslOrder['tech_addressline2'],
            'city' => $sslOrder['tech_city'],
            'state' => $sslOrder['tech_region'],
            'postcode' => $sslOrder['tech_postalcode'],
            'country' => $sslOrder['tech_country'],
            'phonenumber' => $sslOrder['tech_phone'],
            'fields' => array(
                'order_type' => 'renew',
                'org_name' => isset($sslOrder['org_name']) && !empty($sslOrder['org_name']) ? $sslOrder['org_name'] : '',
                'org_division' => isset($sslOrder['org_division']) && !empty($sslOrder['org_division']) ? $sslOrder['org_division'] : '',
                'org_duns' => isset($sslOrder['org_duns']) && !empty($sslOrder['org_duns']) ? $sslOrder['org_duns'] : '',
                'org_addressline1' => isset($sslOrder['org_addressline1']) && !empty($sslOrder['org_addressline1']) ? $sslOrder['org_addressline1'] : '',
                'org_city' => $sslOrder['org_city'],
                'org_country' => $sslOrder['org_country'],
                'org_fax' => $sslOrder['org_fax'],
                'org_phone' => $sslOrder['org_phone'],
                'org_postalcode' => $sslOrder['org_postalcode'],
                'org_regions' => $sslOrder['org_region']
            )
        ));
        
        $addedSSLOrder = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLRenewOrder($order);
        
        Capsule::table('tblsslorders')->where('serviceid', $invoiceInfo->new_service_id)->delete();
        Capsule::table('tblsslorders')->insert(array(
            'userid' => $invoiceInfo->user_id,
            'serviceid' => $invoiceInfo->new_service_id,
            'addon_id' => '0',
            'remoteid' => $addedSSLOrder['order_id'],
            'module' => 'SSLCENTERWHMCS',
            'certtype' => '',
            'configdata' => $configdata,
            'completiondate' => date('Y-m-d H:i:s'),
            'status' => 'Completed'
        ));
    }
    
    private function getSanCountConfigOptionServiceDetails($service)
    {
        $product = \WHMCS\Product\Product::where('id', $service->packageid)->first();
        
        $isSanEnabled = $product->{ConfigOptions::PRODUCT_ENABLE_SAN}=== 'on';
        $boughtSans = 0;
        if($isSanEnabled)
        {            
            $server = new \WHMCS\Module\Server();
            if( !$server->loadByServiceID($service->id) ) 
            {
                logActivity("SSLCENTER WHMCS: Required Product Module '" . $server->getServiceModule() . "' Missing"); 
            }
            else
            {
                $serviceParams = $server->buildParams(); 
                if (!empty($serviceParams)) {         
                    $CORepo = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\configOptions\Repository($service->id);
                    
                    return array(
                        'configOptionID' => $CORepo->getOptionID(ConfigOptions::OPTION_SANS_COUNT),
                        'configID'       => $CORepo->getConfigID(ConfigOptions::OPTION_SANS_COUNT),
                        'boughtSans'     => $serviceParams['configoptions'][ConfigOptions::OPTION_SANS_COUNT],
                        'frendlyName'    => $CORepo->getFrendlyName(ConfigOptions::OPTION_SANS_COUNT),
                        'configBillingCycle' => (in_array($service->billingcycle, ['One Time', 'Free Account'])) ? 'monthly' : $service->billingcycle
                    ); 
                }
            }            
        }
        
        return array();
    }
    
    
    public function createOrder($userId, $paymentMethod, $productID, $domain, $dueDateNewOrder, $serviceBillingCycle, $configOptions = array()) {
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
            if(!empty($configOptions))
            {
                $postData['configoptions'] = base64_encode(serialize($configOptions));
            }
            $result = localAPI('UpdateClientProduct', $postData, $adminUsername);
        }
        logActivity(print_r($postData, true)); 
        logActivity(print_r($result, true)); 
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
            {
            $service = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\Service($serviceID);       
            $whmcsProduct = $service->product();

            if($whmcsProduct->getShowDomainOptions() || $whmcsProduct->getPayType() == 'free')
                return;
            //get invoice related with order
            $whmcsOrder = $service->order();
            $invoice = $whmcsOrder->invoice();
            $invoiceItemsRepo = new \MGModule\SSLCENTERWHMCS\models\whmcs\invoices\RepositoryItem();        
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

                    logActivity("SSLCENTER WHMCS: Description of the invoice item for Invoice ID: " . $invoice->getId() . ' has been changed from "' . $oldDescription . '" to "' . $newDescription . '"');
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
