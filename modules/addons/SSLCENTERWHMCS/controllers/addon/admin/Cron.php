<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\admin;

use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions as C;
use Illuminate\Database\Capsule\Manager as Capsule;
use \MGModule\SSLCENTERWHMCS as main;

class Cron extends main\mgLibs\process\AbstractController
{
    private $sslRepo = null;

    public function indexCRON($input, $vars = array())
    {

        $updatedServices = [];

        $this->sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();

        //get all completed ssl orders
        $sslOrders = $this->getSSLOrders();

        foreach ($sslOrders as $sslService)
        {
            $serviceID = $sslService->serviceid;

            if(!isset($sslService->remoteid) || empty($sslService->remoteid))
            {
                continue;
            }

            if($sslService->status != 'Awaiting Configuration')
            {
                $configdata = json_decode($sslService->configdata, true);
                if(isset($configdata['domain']) && !empty($configdata['domain']))
                {
                    Capsule::table('tblhosting')->where('id', $serviceID)->update(['domain' => $configdata['domain']]);
                }
            }

            //if service is synchronized skip it
            if ($this->checkIfSynchronized($serviceID))
                continue;

            //set ssl certificate as synchronized
            $this->setSSLServiceAsSynchronized($serviceID);

            try{
                $order = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);
            } catch (\Exception $e) {
                continue;
            }

            $service = (array)Capsule::table('tblhosting')->where('id', $serviceID)->first();
            $product = (array)Capsule::table('tblproducts')->where('servertype', 'SSLCENTERWHMCS')->where('id', $service['packageid'])->first();

            if(isset($product['configoption7']) && !empty($product['configoption7']) && $service['billingcycle'] == 'One Time')
            {
                Capsule::table('tblhosting')->where('id', $serviceID)->update(array('termination_date' => $order['valid_till']));
            }

            if ($order['status'] == 'expired' || $order['status'] == 'cancelled')
            {
                $this->setSSLServiceAsTerminated($serviceID);
                $updatedServices[] = $serviceID;
            }

            //if certificate is active

            if ($order['status'] == 'active')
            {
                //update whmcs service next due date
                $newNextDueDate = $order['valid_till'];
                if(!empty($order['end_date']))
                {
                    $newNextDueDate = $order['end_date'];
                }

                //set ssl certificate as terminated if expired
                if (strtotime($order['valid_till']) < strtotime(date('Y-m-d')))
                {
                    $this->setSSLServiceAsTerminated($serviceID);
                }

                //if service is montlhy, one time, free skip it
                if ($this->checkServiceBillingPeriod($serviceID))
                    continue;

                $this->updateServiceNextDueDate($serviceID, $newNextDueDate);

                $updatedServices[] = $serviceID;
            }
        }
        echo 'Synchronization completed.';
        echo '<br />Number of synchronized services: ' . count($updatedServices);

        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Synchronization completed. Number of synchronized services: " . count($updatedServices));

        return array();
    }

    public function notifyCRON($input, $vars = array())
    {
        //get renewal settings
        $apiConf                      = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $auto_renew_invoice_one_time  = (bool) $apiConf->auto_renew_invoice_one_time;
        $auto_renew_invoice_reccuring = (bool) $apiConf->auto_renew_invoice_reccuring;
        $renew_new_order              = (bool) $apiConf->renew_new_order;
        //get saved amount days to generate invoice (one time & reccuring)
        $renew_invoice_days_one_time  = $apiConf->renew_invoice_days_one_time;
        $renew_invoice_days_reccuring = $apiConf->renew_invoice_days_reccuring;
        $auto_renew_invoice_subscription = (bool) $apiConf->auto_renew_invoice_subscription;
        $renew_invoice_days_subscription = $apiConf->renew_invoice_days_subscription;

        $send_expiration_notification_reccuring = (bool) $apiConf->send_expiration_notification_reccuring;
        $send_expiration_notification_one_time  = (bool) $apiConf->send_expiration_notification_one_time;
        $send_expiration_notification_subscription  = (bool) $apiConf->send_expiration_notification_subscription;

        $this->sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();

        //get all completed ssl orders
        $sslOrders       = $this->getSSLOrders();

        $synchServicesId = [];
        foreach($sslOrders as $row)
        {
            $config = json_decode($row->configdata);
            if (isset($config->synchronized))
            {
                $synchServicesId[] = $row->serviceid;
            }
            else
            {
                $serviceonetime = \WHMCS\Service\Service::where('id', $row->serviceid)->where('billingcycle', 'One Time')->first();
                if(isset($serviceonetime->id))
                {
                    $synchServicesId[] = $serviceonetime->id;
                }
            }
        }

        if(!empty($synchServicesId))
        {
            $services = \WHMCS\Service\Service::whereIn('id', $synchServicesId)->get();
        }
        else
        {
            $services = [];
        }

        $acmeSubscriptionServices = Capsule::table('SSLCENTER_acme_subscriptions')
            ->whereIn('status', ['active', 'pending'])
            ->pluck('service_id')
            ->toArray();

        if (!empty($acmeSubscriptionServices))
        {
            $synchServicesId = array_values(array_unique(array_merge($synchServicesId, $acmeSubscriptionServices)));
        }

        $emailSendsCount = 0;
        $emailSendsCountReissue = 0;
        $invoicesCreatedCount = 0;

        $packageLists = [];
        $serviceIDs   = [];

        foreach ($synchServicesId as $serviceid)
        {
            $srv = Capsule::table('tblhosting')->where('id', $serviceid)->first();
            if (!$srv || $serviceid != 18)
            {
                continue;
            }

            $isAcmeSubscription = \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeByServiceId($srv->id);
            $acmeSubscriptionData = null;

            if ($isAcmeSubscription)
            {
                $acmeSubscriptionData = (new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository())->getByServiceId($srv->id);
            }

            //get days left to expire from WHMCS
            $daysLeft         = $this->checkOrderExpireDate($srv->nextduedate);
            $daysReissue         = $this->checkReissueDate($srv->id);

            //if service is One Time and nextduedate is setted as 0000-00-00 get valid_till from SSLCenter API

            if ($srv->billingcycle == 'One Time')
            {
                $sslOrder = Capsule::table('tblsslorders')->where('serviceid', $srv->id)->first();
                if(isset($sslOrder->remoteid) && !empty($sslOrder->remoteid) && !$isAcmeSubscription) {
                    $order    = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslOrder->remoteid);
                    $daysLeft = $this->checkOrderExpireDate($order['valid_till']);
                }
            }

            $product = Capsule::table('tblproducts')->where('id', $srv->packageid)->first();
            if($srv->domainstatus == 'Active' && $daysReissue == '30' && $product->configoption2 > 12 && !$isAcmeSubscription)
            {
                // send email
                $emailSendsCountReissue += $this->sendReissueNotfiyEmail($srv->id);
            }

            //service was synchronized, so we can base on nextduedate, that should be the same as valid_till
            //$daysLeft = 90;
            if ($daysLeft >= 0)
            {
                if (!$isAcmeSubscription && $srv->billingcycle == 'One Time' && $send_expiration_notification_one_time || $srv->billingcycle != 'One Time' && $send_expiration_notification_reccuring)
                {
                    $emailSendsCount += $this->sendExpireNotfiyEmail($srv->id, $daysLeft);
                }
            }

            if ($isAcmeSubscription)
            {
                $expiryDate = !empty($acmeSubscriptionData->renewal_date) ? $acmeSubscriptionData->renewal_date : $acmeSubscriptionData->period_end;
                if (!empty($expiryDate))
                {
                    $daysLeft = $this->checkOrderExpireDateExact($expiryDate);
                }


            }

            $savedRenewDays = $renew_invoice_days_reccuring;
            if ($isAcmeSubscription)
            {
                $savedRenewDays = $renew_invoice_days_subscription;
            }
            elseif ($srv->billingcycle == 'One Time')
            {
                $savedRenewDays = $renew_invoice_days_one_time;
            }

            //if it is proper amount of days before expiry, we create invoice
            if ($daysLeft == (int) $savedRenewDays)
            {
                if ($isAcmeSubscription)
                {
                    if ($auto_renew_invoice_subscription && isset($acmeSubscriptionData->auto_renew) && (int) $acmeSubscriptionData->auto_renew === 1)
                    {
                        $nextInvoiceDate = $this->getAcmeNextInvoiceDate($acmeSubscriptionData->renewal_date, $savedRenewDays);

                        if ($nextInvoiceDate !== null && $nextInvoiceDate <= date('Y-m-d'))
                        {
                            $invoicesCreatedCount += $this->createAcmeSubscriptionRenewalInvoice($srv, $product, $acmeSubscriptionData, $savedRenewDays);
                        }

                        if ($send_expiration_notification_subscription)
                        {
                            $emailSendsCount += $this->sendExpireNotfiyEmail(
                                $srv->id,
                                $daysLeft,
                                main\eServices\EmailTemplateService::SUBSCRIPTION_EXPIRATION_TEMPLATE_ID
                            );
                        }

                    }
                }
                elseif ($srv->billingcycle == 'One Time' && $auto_renew_invoice_one_time || $srv->billingcycle != 'One Time' && $auto_renew_invoice_reccuring)
                {
                    $packageLists[$srv->packageid][] = $srv;
                    $serviceIDs[]                    = $srv->id;
                }
            }
        }

        if(!$renew_new_order)
        {
            $invoicesCreatedCount += $this->createAutoInvoice($packageLists, $serviceIDs);

            $invoices = Capsule::table('tblinvoices')->where('status', 'Payment Pending')->get();
            foreach($invoices as $invoice)
            {
                $itemsInvoice = Capsule::table('tblinvoiceitems')->where('invoiceid', $invoice->id)->where('description', 'LIKE', '%- Renewal')->get();

                if(!empty($itemsInvoice))
                {
                    $sslInvoice = Capsule::table('mgfw_SSLCENTER_invoices_info')->where('invoice_id', $invoice->id)->first();

                    $serviceid = $sslInvoice->service_id;

                    $sslInfo = $this->getSSLOrders($serviceid)[0];
                    $sslOrder    = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslInfo->remoteid);

                    $today = (string)date('Y-m-d');

                    if($sslOrder['valid_till'] == $today)
                    {
                        Capsule::table('tblinvoices')->where('id', $invoice->id)->update(array('status' => 'Cancelled'));
                    }
                }
            }
        }



        echo 'Notifier completed.' . PHP_EOL;
        echo '<br />Number of emails send (expire): ' . $emailSendsCount . PHP_EOL;
        echo '<br />Number of emails send (reissue): ' . $emailSendsCountReissue . PHP_EOL;

        logActivity('Notifier completed. Number of emails send: '.$emailSendsCount, 0);

        if(!$renew_new_order)
        {
            echo '<br />Number of invoiced created: ' . $invoicesCreatedCount . PHP_EOL;
        }

        $this->cancelOverdueAcmeSubscriptions();
        $this->cancelExpiredStoppedAutoRenewAcmeSubscriptions();

        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Notifier completed. Number of emails send: " . $emailSendsCount);

        if(!$renew_new_order)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Notifier completed. Number of invoiced created: " . $invoicesCreatedCount);
        }

        return array();
    }

    public function certificateSendCRON($input, $vars = array())
    {
        echo 'Certificate Sender started.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificate Sender started.");

        $emailSendsCount = 0;
        $this->sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();

        $services = new main\models\whmcs\service\Repository();
        $services->onlyStatus(['Active']);

        $servicesArray = [];
        foreach ($services->get() as $service)
        {
            $apiOrders = null;
            $product   = $service->product();
            //check if product is SSLCENTER
            if ($product->serverType != 'SSLCENTERWHMCS')
            {
                continue;
            }

            $SSLOrder = new main\eModels\whmcs\service\SSL();
            $ssl      = $SSLOrder->getWhere(array('serviceid' => $service->id, 'userid' => $service->clientID))->first();

            if ($ssl == NULL || $ssl->remoteid == '')
            {
                continue;
            }
            $apiOrder = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($ssl->remoteid);
            if ($apiOrder['status'] !== 'active' || empty($apiOrder['ca_code']))
            {
                continue;
            }

            if ($this->checkIfCertificateSent($service->id))
                continue;

            $apiConf                  = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
            $sendCertyficateTermplate = $apiConf->send_certificate_template;
            if ($sendCertyficateTermplate == NULL)
            {
                sendMessage(\MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::SEND_CERTIFICATE_TEMPLATE_ID, $service->id, [
                    'ssl_certyficate' => nl2br($apiOrder['ca_code']),
                    'crt_code' => nl2br($apiOrder['crt_code']),
                ]);
            }
            else
            {
                $templateName = \MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::getTemplateName($sendCertyficateTermplate);
                sendMessage($templateName, $service->id, [
                    'ssl_certyficate' => nl2br($apiOrder['ca_code']),
                    'crt_code' => nl2br($apiOrder['crt_code']),
                ]);
            }
            $this->setSSLCertificateAsSent($service->id);
            $emailSendsCount++;
        }

        echo 'Certificate Sender completed.' . PHP_EOL;
        echo '<br />The number of messages sent: ' . $emailSendsCount . PHP_EOL;

        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificate Sender completed. The number of messages sent: " . $emailSendsCount);
        return array();
    }

    public function monitorAcmeSubscriptionsCRON($input, $vars = array())
    {
        echo 'ACME subscriptions monitor started.' . PHP_EOL;
        $this->cancelOverdueAcmeSubscriptions();
        $this->cancelExpiredStoppedAutoRenewAcmeSubscriptions();
        echo 'ACME subscriptions monitor completed.' . PHP_EOL;
        return array();
    }

    public function certificateDetailsUpdateCRON($input, $vars = array())
    {
        echo 'Certificate Details Updating.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificate Details Updating started.");

        $this->sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();

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

        if (!Capsule::schema()->hasColumn('mgfw_SSLCENTER_product_brand', 'data'))
        {
            Capsule::schema()->table('mgfw_SSLCENTER_product_brand', function($table)
            {
                $table->text('data');
            });
        }

        Capsule::table('mgfw_SSLCENTER_product_brand')->truncate();

        $apiProducts = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getProducts();

        foreach ($apiProducts['products'] as $apiProduct) {
            Capsule::table('mgfw_SSLCENTER_product_brand')->insert(array(
                'pid' => $apiProduct['id'],
                'brand' => $apiProduct['brand'],
                'data' => json_encode($apiProduct)
            ));
        }

        $sslOrders = $this->getSSLOrders();

        foreach ($sslOrders as $sslService)
        {

            $sslService = \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL::hydrate(array($sslService))[0];

            $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService);
            $configDataUpdate->run();
        }

        echo '<br/ >';
        echo 'Certificate Details Updating completed.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificate Details Updating completed.");
        return array();
    }

    public function loadCertificateStatsCRON($input, $vars = array())
    {
        echo 'Certificate Stats Loader started.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificate Stats Loader started.");

        $emailSendsCount = 0;
        $this->sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();

        $services = new main\models\whmcs\service\Repository();
        $services->onlyStatus(['Active', 'Suspended']);

        $servicesArray = [];
        foreach ($services->get() as $service)
        {
            $apiOrders = null;
            $product   = $service->product();
            //check if product is SSLCENTER
            if ($product->serverType != 'SSLCENTERWHMCS')
            {
                continue;
            }

            $SSLOrder = new main\eModels\whmcs\service\SSL();
            $ssl      = $SSLOrder->getWhere(array('serviceid' => $service->id, 'userid' => $service->clientID))->first();

            if ($ssl == NULL || $ssl->remoteid == '')
            {
                continue;
            }
            $apiOrder = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($ssl->remoteid);

            $this->setSSLCertificateValidTillDate($service->id, $apiOrder['valid_till']);
            $this->setSSLCertificateStatus($service->id, $apiOrder['status']);
        }
        echo '<br/ >';
        echo 'Certificate Stats Loader completed.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificate Stats Loader completed.");
        return array();
    }

    public function updateProductPricesCRON($input, $vars = array())
    {
        echo 'Products Price Updater started.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Products Price Updater started.");

        try
        {
            //get all products prices
            $apiProductsPrices = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\ProductsPrices::getInstance();

            foreach ($apiProductsPrices->getAllProductsPrices() as $productPrice)
            {
                $productPrice->saveToDatabase();
            }

            $apiProductsById = $this->getApiProductsForCron();

            $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
            //get sslcenter all products
            $products     = $productModel->getModuleProducts();

            foreach ($products as $product)
            {
                //if auto price not enabled skip product
                if (!$product->{C::PRICE_AUTO_DOWNLOAD})
                    continue;

                $apiProduct = isset($apiProductsById[(int) $product->{C::API_PRODUCT_ID}]) ? $apiProductsById[(int) $product->{C::API_PRODUCT_ID}] : null;
                $isAcmeProduct = \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId((int) $product->{C::API_PRODUCT_ID});

                if ($apiProduct !== null && $isAcmeProduct)
                {
                    $this->generateNewPricesFromApiProduct($product->pricing, $apiProduct);
                    $this->syncProductDataFromApi((int) $product->id, $apiProduct);
                    $this->syncSanConfigurableOptionsFromApi((int) $product->id, C::OPTION_SANS_COUNT, $apiProduct, 'single');
                    $this->syncSanConfigurableOptionsFromApi((int) $product->id, C::OPTION_SANS_WILDCARD_COUNT, $apiProduct, 'wildcard');
                }
                else
                {
                    //legacy fallback
                    $apiPrice = $productPrice->loadSavedPriceData($product->{C::API_PRODUCT_ID});
                    $this->generateNewPricesBasedOnAPI($product->pricing, $apiPrice);
                }
            }
        }
        catch (\Exception $e)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS Products Price Updater Error: " . $e->getMessage());
        }

        echo '<br/ >';
        echo 'Products Price Updater completed.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Products Price Updater completed.");
        return array();
    }

    private function getApiProductsForCron()
    {
        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getProducts();
        $products = isset($response['products']) && is_array($response['products']) ? $response['products'] : [];
        $result = [];

        foreach ($products as $product)
        {
            if (!isset($product['id']))
            {
                continue;
            }
            $result[(int) $product['id']] = $product;
        }

        return $result;
    }

    private function generateNewPricesFromApiProduct($currentPrices, $apiProduct)
    {
        $termPrices = $this->extractBasePricesByTerm($apiProduct);
        if (empty($termPrices))
        {
            return;
        }

        $annual = isset($termPrices[12]) ? (float) $termPrices[12] : 0.00;
        $rate = $this->getGlobalRate();
        $currencies = (new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository())->getAllCurrencies();

        foreach ($currentPrices as $price)
        {
            $currencyRate = $this->getCurrencyRateById($currencies, (int) $price->currency);
            $periodMap = [
                'monthly'      => 12,
                'quarterly'    => 3,
                'semiannually' => 6,
                'annually'     => 12,
                'biennially'   => 24,
                'triennially'  => 36,
            ];

            $update = [];
            foreach ($periodMap as $cycle => $term)
            {
                if ($price->{$cycle} === '-1.00')
                {
                    $update[$cycle] = '-1.00';
                    continue;
                }

                $base = isset($termPrices[$term]) ? (float) $termPrices[$term] : $annual;
                $update[$cycle] = number_format($base * $currencyRate * $rate, 2, '.', '');
            }

            Capsule::table("tblpricing")
                ->where("id", "=", $price->pricing_id)
                ->where("type", "=", 'product')
                ->where("relid", "=", $price->relid)
                ->update($update);
        }
    }

    private function syncProductDataFromApi($productId, array $apiProduct)
    {
        $san = $this->toArray($this->readValue($apiProduct, ['san']));
        $included = $this->toArray($this->readValue($san, ['included']));

        $update = [
            C::PRODUCT_INCLUDED_SANS => $this->toInt($this->readValue($included, ['single']), 0),
            C::PRODUCT_INCLUDED_SANS_WILDCARD => $this->toInt($this->readValue($included, ['wildcard']), 0),
            C::PRODUCT_ENABLE_SAN => $this->toBool($this->readValue($san, ['single_allowed'])) ? 'on' : '',
            C::PRODUCT_ENABLE_SAN_WILDCARD => $this->toBool($this->readValue($san, ['wildcard_allowed'])) ? 'on' : '',
        ];

        $description = $this->readValue($apiProduct, ['description']);
        if (is_string($description))
        {
            $update['description'] = $description;
        }

        if (\MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeProductId((int) $this->readValue($apiProduct, ['id'])))
        {
            $update['paytype']             = 'onetime';
            $update[C::API_PRODUCT_MONTHS] = 12;
            $update['configoptionsupgrade'] = 1;
        }

        Capsule::table('tblproducts')->where('id', (int) $productId)->update($update);
    }

    private function syncSanConfigurableOptionsFromApi($productId, $optionTypeName, array $apiProduct, $sanType)
    {
        $like = $optionTypeName . '%';
        $option = Capsule::table('tblproductconfiggroups')
            ->select(['tblproductconfigoptions.id'])
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiggroups.id')
            ->where('tblproductconfiggroups.description', '=', 'Auto generated by module - SSLCenter #' . (int) $productId)
            ->where('tblproductconfigoptions.optionname', 'LIKE', $like)
            ->first();

        if (!$option || !isset($option->id))
        {
            return;
        }

        $san = $this->toArray($this->readValue($apiProduct, ['san']));
        $min = $this->toInt($this->readValue($san, ['min']), 0);
        $max = $this->toInt($this->readValue($san, ['max']), 10);
        if ($min < 0) {
            $min = 0;
        }
        if ($max < $min) {
            $max = $min;
        }

        Capsule::table('tblproductconfigoptions')
            ->where('id', (int) $option->id)
            ->update([
                'qtyminimum' => $min,
                'qtymaximum' => $max,
            ]);

        $subOptionIds = Capsule::table('tblproductconfigoptionssub')
            ->where('configid', (int) $option->id)
            ->pluck('id')
            ->toArray();

        if (empty($subOptionIds))
        {
            return;
        }

        $pricingByCurrency = $this->buildSanPricingByCurrency($apiProduct, $sanType);
        foreach ($subOptionIds as $subOptionId)
        {
            $pricingRows = Capsule::table('tblpricing')
                ->where('type', 'configoptions')
                ->where('relid', (int) $subOptionId)
                ->get();

            foreach ($pricingRows as $pricingRow)
            {
                if (!isset($pricingByCurrency[(int) $pricingRow->currency]))
                {
                    continue;
                }

                Capsule::table('tblpricing')
                    ->where('id', (int) $pricingRow->id)
                    ->update($pricingByCurrency[(int) $pricingRow->currency]);
            }
        }
    }

    private function buildSanPricingByCurrency(array $apiProduct, $sanType)
    {
        $termPrices = $this->extractSanPricesByTerm($apiProduct, $sanType);
        $annual = isset($termPrices[12]) ? (float) $termPrices[12] : 0.00;
        $rate = $this->getGlobalRate();
        $currencies = (new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository())->getAllCurrencies();
        $pricingByCurrency = [];

        foreach ($currencies as $currency)
        {
            $currencyRate = ($currency->default == '1') ? 1 : (float) $currency->rate;
            if ($currencyRate <= 0)
            {
                $currencyRate = 1;
            }

            $periodMap = [
                'monthly'      => 12,
                'quarterly'    => 3,
                'semiannually' => 6,
                'annually'     => 12,
                'biennially'   => 24,
                'triennially'  => 36,
            ];

            $pricing = [];
            foreach ($periodMap as $cycle => $term)
            {
                $base = isset($termPrices[$term]) ? (float) $termPrices[$term] : $annual;
                $pricing[$cycle] = number_format($base * $currencyRate * $rate, 2, '.', '');
            }

            $pricingByCurrency[(int) $currency->id] = $pricing;
        }

        return $pricingByCurrency;
    }

    private function extractBasePricesByTerm(array $apiProduct)
    {
        $prices = $this->toArray($this->readValue($apiProduct, ['prices']));
        $result = [];

        foreach ($prices as $entry)
        {
            $term = (int) $this->readValue($entry, ['term', 'period']);
            if ($term <= 0)
            {
                continue;
            }

            $baseNode = $this->readValue($entry, ['base']);
            $price = $this->resolveBasePriceFromNode($baseNode);
            if ($price === null)
            {
                $price = $this->readMonetaryValue($entry, ['price', 'selling', 'retail']);
            }

            if ($price !== null)
            {
                $result[$term] = (float) $price;
            }
        }

        return $result;
    }

    private function extractSanPricesByTerm(array $apiProduct, $sanType)
    {
        $prices = $this->toArray($this->readValue($apiProduct, ['prices']));
        $result = [];

        foreach ($prices as $entry)
        {
            $term = (int) $this->readValue($entry, ['term', 'period']);
            if ($term <= 0)
            {
                continue;
            }

            $sanNode = $this->toArray($this->readValue($entry, ['san']));
            $typeNode = $this->toArray($this->readValue($sanNode, [$sanType]));
            $price = $this->readMonetaryValue($typeNode, ['selling', 'retail', 'price']);

            if ($price !== null)
            {
                $result[$term] = (float) $price;
            }
        }

        return $result;
    }

    private function resolveBasePriceFromNode($baseNode)
    {
        if (is_numeric($baseNode))
        {
            return (float) $baseNode;
        }

        $baseArray = $this->toArray($baseNode);
        if (empty($baseArray))
        {
            return null;
        }

        foreach (['single', 'wildcard'] as $type)
        {
            $node = $this->readValue($baseArray, [$type]);
            $price = $this->readMonetaryValue($node, ['selling', 'retail', 'price']);
            if ($price !== null)
            {
                return $price;
            }
        }

        return $this->readMonetaryValue($baseArray, ['selling', 'retail', 'price']);
    }

    private function readMonetaryValue($node, array $keys)
    {
        foreach ($keys as $key)
        {
            $value = $this->readValue($node, [$key]);
            if (is_numeric($value))
            {
                return (float) $value;
            }
        }

        if (is_numeric($node))
        {
            return (float) $node;
        }

        return null;
    }

    private function readValue($source, array $keys)
    {
        foreach ($keys as $key)
        {
            if (is_array($source) && array_key_exists($key, $source))
            {
                return $source[$key];
            }

            if (is_object($source) && isset($source->{$key}))
            {
                return $source->{$key};
            }
        }

        return null;
    }

    private function toArray($value)
    {
        if (is_array($value))
        {
            return $value;
        }

        if (is_object($value))
        {
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
        if (is_bool($value))
        {
            return $value;
        }

        if (is_numeric($value))
        {
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

    private function getCurrencyRateById($currencies, $currencyId)
    {
        foreach ($currencies as $currency)
        {
            if ((int) $currency->id !== (int) $currencyId)
            {
                continue;
            }

            $rate = ($currency->default == '1') ? 1 : (float) $currency->rate;
            return ($rate > 0) ? $rate : 1;
        }

        return 1;
    }
    private function checkOrdersStatus($sslorders, $processingOnly = false)
    {
        $cids = [];
        foreach ($sslorders as $sslService) {
            $cids[] = $sslService->remoteid;
        }
        try
        {

            $cids = implode(',', $cids);

            $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigs($cids, $processingOnly);
            $configDataUpdate->run();

        }
        catch (\Exception $e)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS Products Price Updater Error: " . $e->getMessage());
        }
    }
    public function dailyStatusCheckCRON($input, $vars = array())
    {
        echo 'Certificates (ssl status Completed) Data Updater started.' . PHP_EOL;
        $this->sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslorders = Capsule::table('tblhosting')
        ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
        ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
        ->where('tblhosting.domainstatus', 'Active')
        ->whereIn('tblsslorders.status', ['Completed', 'Configuration Submitted'])
        ->get(['tblsslorders.*']);

        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificates (ssl status Completed) Data Updater started.");

        $this->checkOrdersStatus($sslorders);

        echo '<br/ >';
        echo 'Certificates (ssl status Completed) Data Updater completed.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificates (ssl status Completed) Data Updater completed.");
        return array();
    }
    public function processingOrdersCheckCRON($input, $vars = array())
    {
        echo 'Certificates (ssl status Processing) Data Updater started.' . PHP_EOL;
        $this->sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslorders = Capsule::table('tblhosting')
        ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
        ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
        ->where('tblhosting.domainstatus', 'Active')
        ->where('tblsslorders.configdata', 'like', '%"ssl_status":"processing"%')
        ->get(['tblsslorders.*']);

        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificates (ssl status Processing) Data Updater started.");

        $this->checkOrdersStatus($sslorders, true);
        $updatedPendingAcme = $this->refreshPendingAcmeSubscriptions();

        echo '<br/ >';
        echo 'Pending ACME subscriptions refreshed: ' . $updatedPendingAcme . PHP_EOL;
        echo 'Certificates (ssl status Processing) Data Updater completed.' . PHP_EOL;
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Pending ACME subscriptions refreshed: " . $updatedPendingAcme);
        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Certificates (ssl status Processing) Data Updater completed.");
        return array();
    }

    private function refreshPendingAcmeSubscriptions()
    {
        $updated = 0;
        $subRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $sslRepo = new main\eRepository\whmcs\service\SSL();
        $pendingSubs = Capsule::table('SSLCENTER_acme_subscriptions')
            ->whereIn('status', ['pending', 'Pending'])
            ->whereNotNull('api_order_id')
            ->where('api_order_id', '>', 0)
            ->get();

        foreach ($pendingSubs as $subscription)
        {
            try
            {
                $details = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getCertificateDetails('acme', (int) $subscription->api_order_id);
            }
            catch (\Exception $e)
            {
                continue;
            }

            $orderStatus = isset($details['order']['status']) ? $details['order']['status'] : (isset($details['status']) ? $details['status'] : $subscription->status);
            $items = isset($details['items']) && is_array($details['items']) ? $details['items'] : array();
            $firstItem = isset($items[0]) && is_array($items[0]) ? $items[0] : array();
            $acmeId = isset($firstItem['id']) ? (int) $firstItem['id'] : (isset($subscription->acme_id) ? (int) $subscription->acme_id : 0);
            $account = isset($firstItem['account']) && is_array($firstItem['account']) ? $firstItem['account'] : array();
            $subscriptionData = isset($firstItem['subscription']) && is_array($firstItem['subscription']) ? $firstItem['subscription'] : array();

            $periodStart = isset($subscriptionData['begin']) ? $subscriptionData['begin'] : $subscription->period_start;
            $periodEnd = isset($subscriptionData['end']) ? $subscriptionData['end'] : $subscription->period_end;
            $renewalDate = isset($subscriptionData['next_renewal']) ? $subscriptionData['next_renewal'] : $subscription->renewal_date;
            $autoRenew = isset($subscriptionData['next_renewal']) ? 1 : (int) $subscription->auto_renew;

            $subRepo->upsertByServiceId((int) $subscription->service_id, [
                'status'          => $orderStatus,
                'acme_id'         => $acmeId > 0 ? $acmeId : null,
                'acme_account_id' => isset($account['id']) ? (string) $account['id'] : (string) $subscription->acme_account_id,
                'eab_kid'         => isset($account['eab_mac_id']) ? (string) $account['eab_mac_id'] : (string) $subscription->eab_kid,
                'eab_hmac_key'    => isset($account['eab_mac_key']) ? (string) $account['eab_mac_key'] : (string) $subscription->eab_hmac_key,
                'server_url'      => isset($account['server_url']) ? (string) $account['server_url'] : (string) $subscription->server_url,
                'period_start'    => $periodStart,
                'period_end'      => $periodEnd,
                'renewal_date'    => $renewalDate,
                'auto_renew'      => $autoRenew,
            ]);

            $sslService = $sslRepo->getByServiceId((int) $subscription->service_id);
            if ($sslService)
            {
                $sslService->remoteid = (int) $subscription->api_order_id;
                $sslService->status = 'Completed';
                $sslService->setConfigdataKey('ssl_status', $orderStatus);
                if (!empty($periodStart))
                {
                    $sslService->setConfigdataKey('begin_date', $periodStart);
                }
                if (!empty($periodEnd))
                {
                    $sslService->setConfigdataKey('end_date', $periodEnd);
                }
                if (!empty($renewalDate))
                {
                    $sslService->setConfigdataKey('renewal_date', $renewalDate);
                }
                $sslService->save();
            }

            $updated++;
        }

        return $updated;
    }
    private function generateNewPricesBasedOnAPI($currentPrices, $apiPrices)
    {
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $rate  = (float)$apiConf->rate;

        if(empty($rate))
        {
            $rate = 1;
        }

        foreach ($currentPrices as $price)
        {
            $currency = $price->currency;

            $monthly      = (in_array($price->monthly, array('-1.00'))) ? $price->monthly : $rate*$this->generateNewPrice('12', $apiPrices, $currency);
            $quarterly    = (in_array($price->quarterly, array('-1.00'))) ? $price->quarterly : $rate*$this->generateNewPrice('3', $apiPrices, $currency);
            $semiannually = (in_array($price->semiannually, array('-1.00'))) ? $price->semiannually : $rate*$this->generateNewPrice('6', $apiPrices, $currency);
            $annually     = (in_array($price->annually, array('-1.00'))) ? $price->annually : $rate*$this->generateNewPrice('12', $apiPrices, $currency);
            $biennially   = (in_array($price->biennially, array('-1.00'))) ? $price->biennially : $rate*$this->generateNewPrice('24', $apiPrices, $currency);
            $triennially  = (in_array($price->triennially, array('-1.00'))) ? $price->triennially : $rate*$this->generateNewPrice('36', $apiPrices, $currency);

            //save new pricing
            Capsule::table("tblpricing")
                    ->where("id", "=", $price->pricing_id)
                    ->where("type", "=", 'product')
                    ->where("relid", "=", $price->relid)
                    ->update(array(
                        'monthly'      => $monthly,
                        'quarterly'    => $quarterly,
                        'semiannually' => $semiannually,
                        'annually'     => $annually,
                        'biennially'   => $biennially,
                        'triennially'  => $triennially));
        }
    }

    private function generateNewPrice($period, $apiPrices, $priceCurrency)
    {
        /* if(in_array($price, array('-1.00', '0.00')))
          return $price; */
        $productModel = new \MGModule\SSLCENTERWHMCS\models\productConfiguration\Repository();
        //get all currenccies
        $currencies   = $productModel->getAllCurrencies();

        $newPrice = NULL;
        foreach ($currencies as $curr)
        {
            if ($priceCurrency != $curr->id)
                continue;

            foreach ($apiPrices as $apiPrice)
            {
                if ($apiPrice->period != $period)
                    continue;

                $price = $apiPrice->price;
            }

            if ($curr->default == '1')
            {
                $newPrice = $price;
            }
            else
            {   //exchange based on rate
                $newPrice = (float) $price * $curr->rate;
            }
        }

        return $newPrice;
    }

    private function getSSLOrders($serviceID = null)
    {
        $where = [
            'status' => 'Completed',
            'module' => 'SSLCENTERWHMCS'
        ];

        if ($serviceID != NULL)
            $where['serviceid'] = $serviceID;

        return $this->sslRepo->getBy($where, true);
    }

    private function updateServiceNextDueDate($serviceID, $date)
    {
        $service = \WHMCS\Service\Service::find($serviceID);
        if (!empty($service))
        {
            $createInvoiceDaysBefore = Capsule::table("tblconfiguration")->where('setting', 'CreateInvoiceDaysBefore')->first();
            $service->nextduedate = $date;
            $nextinvoicedate = date('Y-m-d', strtotime("-{$createInvoiceDaysBefore->value} day", strtotime($date)));
            $service->nextinvoicedate = $nextinvoicedate;
            $service->save();

            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Service #$serviceID nextduedate set to ".$date." and nextinvoicedate to". $nextinvoicedate);
        }
    }

    private function setSSLServiceAsSynchronized($serviceID)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('synchronized', date('Y-m-d'));
        $sslService->save();
    }

    private function setSSLServiceAsTerminated($serviceID)
    {
        $service = \WHMCS\Service\Service::find($serviceID);
        if (!empty($service))
        {
            $service->status = 'terminated';
            $service->save();

            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: Service #$serviceID set as Terminated");
        }
    }

    private function checkIfSynchronized($serviceID)
    {
        $result     = false;
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);

        $date = date('Y-m-d');
        $date = strtotime("-5 day", strtotime($date));

        if (strtotime($sslService->getConfigdataKey('synchronized')) > $date)
        {
            $result = true;
        }

        return $result;
    }

    public function checkIfCertificateSent($serviceID)
    {
        $result        = false;
        if ($this->sslRepo == NULL)
            $this->sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();

        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        if ($sslService->getConfigdataKey('certificateSent'))
        {
            $result = true;
        }

        return $result;
    }

    public function setSSLCertificateAsSent($serviceID)
    {
        if ($this->sslRepo == NULL)
            $this->sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService    = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('certificateSent', true);
        $sslService->save();
    }

    private function setSSLCertificateValidTillDate($serviceID, $date)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('valid_till', $date);
        $sslService->save();
    }

    private function setSSLCertificateStatus($serviceID, $status)
    {
        $sslService = $this->sslRepo->getByServiceId((int) $serviceID);
        $sslService->setConfigdataKey('ssl_status', $status);
        $sslService->save();
    }

    private function checkServiceBillingPeriod($serviceID)
    {
        $skipPeriods = ['Monthly', 'One Time', 'Free Account'];
        $skip        = false;
        $service     = \WHMCS\Service\Service::find($serviceID);

        if (in_array($service->billingcycle, $skipPeriods) || $service == null)
        {
            $skip = true;
        }

        return $skip;
    }

    public function checkReissueDate($serviceid)
    {
        $sslOrder = Capsule::table('tblsslorders')->where('serviceid', $serviceid)->first();

        if(isset($sslOrder->configdata) && !empty($sslOrder->configdata)){

            $configdata = json_decode($sslOrder->configdata, true);

            if(isset($configdata['end_date']) && !empty($configdata['end_date']))
            {
                $now = strtotime(date('Y-m-d'));
                $end_date = strtotime($configdata['valid_till']);
                $datediff = $now - $end_date;

                $nextReissue = abs(round($datediff / (60 * 60 * 24)));
                return $nextReissue;
            }
        }
        return false;
    }

    public function checkOrderExpireDate($expireDate)
    {
        $expireDaysNotify = array_flip(array('90', '60', '30', '21', '15', '14', '10', '7', '3', '1', '0'));

        if (stripos($expireDate, ':') === false)
        {
            $expireDate .= ' 23:59:59';
        }
        $expire = new \DateTime($expireDate);
        $today  = new \DateTime();

        $diff = $expire->diff($today, false);
        if ($diff->invert == 0)
        {
            //if date from past
            return -1;
        }

        return isset($expireDaysNotify[$diff->days]) ? $diff->days : -1;
    }

    private function checkOrderExpireDateExact($expireDate)
    {
        if (stripos($expireDate, ':') === false)
        {
            $expireDate .= ' 23:59:59';
        }

        $expire = new \DateTime($expireDate);
        $today  = new \DateTime();

        $diff = $expire->diff($today, false);
        if ($diff->invert == 0)
        {
            return -1;
        }

        return (int) $diff->days;
    }

    private function getAcmeNextInvoiceDate($renewalDate, $daysBefore)
    {
        if (empty($renewalDate) || !is_numeric($daysBefore))
        {
            return null;
        }

        $renewalTimestamp = strtotime((string) $renewalDate);
        if ($renewalTimestamp === false)
        {
            return null;
        }

        return date('Y-m-d', strtotime('-' . (int) $daysBefore . ' days', $renewalTimestamp));
    }

    private function createAcmeSubscriptionRenewalInvoice($service, $product, $subscriptionData, $savedRenewDays)
    {
        if ($this->hasOpenAcmeSubscriptionRenewalInvoice((int) $service->id))
        {
            return 0;
        }

        $amount = isset($service->firstpaymentamount) ? (float) $service->firstpaymentamount : 0.00;
        $dateInvoice = date('Y-m-d');
        $dueDate = new \DateTime($dateInvoice);
        if (is_numeric($savedRenewDays))
        {
            $dueDate->add(new \DateInterval('P' . max(0, (int) $savedRenewDays) . 'D'));
        }
        $periodStart = !empty($subscriptionData->renewal_date)
            ? date('Y-m-d', strtotime((string) $subscriptionData->renewal_date))
            : $dateInvoice;

        $periodEndDate = new \DateTime($periodStart);
        $periodEndDate->add(new \DateInterval('P12M'));
        $periodEndDate->sub(new \DateInterval('P1D'));

        $description = $product->name
            . ($service->domain ? ' - ' . $service->domain : '')
            . ' (1 Year) - ACME Subscription Renewal';

        $postData = [
            'userid' => $service->userid,
            'sendinvoice' => true,
            'date' => $dateInvoice,
            'duedate' => $dueDate->format('Y-m-d'),
            'itemdescription1' => $description,
            'itemamount1' => $amount,
            'itemtaxed1' => isset($product->tax) ? $product->tax : 0,
        ];

        $results = localAPI('CreateInvoice', $postData);

        if (!isset($results['result']) || $results['result'] !== 'success' || empty($results['invoiceid']))
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: Failed to create ACME subscription renewal invoice for service #' . (int) $service->id . '.');
            return 0;
        }

        $invoiceId = (int) $results['invoiceid'];

        Capsule::table('tblinvoiceitems')
            ->where('invoiceid', '=', $invoiceId)
            ->update([
                'type' => 'Hosting',
                'relid' => $service->id,
            ]);

        main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: ACME subscription renewal invoice #' . $invoiceId . ' created for service #' . (int) $service->id . '.');

        return 1;
    }

    private function hasOpenAcmeSubscriptionRenewalInvoice($serviceId)
    {
        return Capsule::table('tblinvoiceitems')
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoiceitems.relid', (int) $serviceId)
            ->where('tblinvoiceitems.description', 'LIKE', '%ACME Subscription Renewal')
            ->whereNotIn('tblinvoices.status', ['Paid', 'Cancelled', 'Refunded'])
            ->exists();
    }

    public function sendExpireNotfiyEmail($serviceId, $daysLeft, $templateName = null)
    {
        $command = 'SendEmail';
        if ($templateName === null)
        {
            $templateName = main\eServices\EmailTemplateService::EXPIRATION_TEMPLATE_ID;
        }

        $postData = array(
            'id'          => $serviceId,
            'messagename' => $templateName,
            'customvars'  => base64_encode(serialize(array("expireDaysLeft" => $daysLeft))),
        );

        $adminUserName = main\eHelpers\Admin::getAdminUserName();

        $results = localAPI($command, $postData, $adminUserName);

        $resultSuccess = $results['result'] == 'success';
        if (!$resultSuccess)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS Notifier: Error while sending customer notifications (service ' . $serviceId . '): ' . $results['message'], 0);
        }
        return $resultSuccess;
    }

    private function cancelOverdueAcmeSubscriptions()
    {
        $today = date('Y-m-d');

        $overdueItems = Capsule::table('tblinvoiceitems')
            ->select(['tblinvoiceitems.relid as service_id', 'tblinvoices.id as invoice_id', 'tblinvoices.duedate'])
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->whereIn('tblproducts.configoption1', \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::getProductIds())
            ->whereIn('tblinvoices.status', ['Unpaid', 'Payment Pending'])
            ->where('tblinvoiceitems.description', 'LIKE', '%ACME Subscription Renewal')
            ->where('tblinvoices.duedate', '<', $today)
            ->get();

        $subRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();

        foreach ($overdueItems as $row)
        {
            $subscription = $subRepo->getByServiceId($row->service_id);
            if (!$subscription || (int) $subscription->api_order_id <= 0)
            {
                continue;
            }
            if (in_array($subscription->status, ['cancelled', 'terminated'], true))
            {
                continue;
            }

            try
            {
                \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->cancelCertificate((int) $subscription->api_order_id, 'Overdue renewal invoice');
            }
            catch (\Exception $e)
            {
            }

            $subRepo->upsertByServiceId($row->service_id, [
                'status'       => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);

            Capsule::table('tblhosting')->where('id', $row->service_id)->update([
                'domainstatus' => 'Terminated'
            ]);
        }
    }

    private function cancelExpiredStoppedAutoRenewAcmeSubscriptions()
    {
        $today = date('Y-m-d');
        $subRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();

        $subscriptions = Capsule::table('SSLCENTER_acme_subscriptions')
            ->join('tblhosting', 'tblhosting.id', '=', 'SSLCENTER_acme_subscriptions.service_id')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->whereIn('tblproducts.configoption1', \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::getProductIds())
            ->where('SSLCENTER_acme_subscriptions.auto_renew', '=', 0)
            ->whereNotIn('SSLCENTER_acme_subscriptions.status', ['cancelled', 'terminated'])
            ->where(function($query) use ($today) {
                $query->whereNotNull('SSLCENTER_acme_subscriptions.renewal_date')
                    ->where('SSLCENTER_acme_subscriptions.renewal_date', '<=', $today)
                    ->orWhere(function($q) use ($today) {
                        $q->whereNull('SSLCENTER_acme_subscriptions.renewal_date')
                            ->whereNotNull('SSLCENTER_acme_subscriptions.period_end')
                            ->where('SSLCENTER_acme_subscriptions.period_end', '<=', $today);
                    });
            })
            ->get(['SSLCENTER_acme_subscriptions.*']);

        foreach ($subscriptions as $subscription)
        {
            if ((int) $subscription->api_order_id > 0)
            {
                try
                {
                    \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->cancelCertificate((int) $subscription->api_order_id, 'Auto-renew disabled and subscription expired');
                }
                catch (\Exception $e)
                {
                }
            }

            $subRepo->upsertByServiceId((int) $subscription->service_id, [
                'status'       => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);

            Capsule::table('tblhosting')->where('id', (int) $subscription->service_id)->update([
                'domainstatus' => 'Cancelled'
            ]);
        }
    }

    public function sendReissueNotfiyEmail($serviceId)
    {
        $command = 'SendEmail';

        $postData = array(
            'serviceid'          => $serviceId,
            'messagename' => main\eServices\EmailTemplateService::REISSUE_TEMPLATE_ID,
        );

        $adminUserName = main\eHelpers\Admin::getAdminUserName();

        $results = localAPI($command, $postData, $adminUserName);

        $resultSuccess = $results['result'] == 'success';
        if (!$resultSuccess)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS Notifier: Error while sending customer notifications (service ' . $serviceId . '): ' . $results['message'], 0);
        }
        return $resultSuccess;
    }

    public function createAutoInvoice($packages, $serviceIds, $jsonAction = false)
    {
        if (empty($packages))
        {
            return 0;
        }

        $products             = \WHMCS\Product\Product::whereIn('id', array_keys($packages))->get();
        $invoiceGenerator     = new main\eHelpers\Invoice();
        $servicesAlreadyAdded = $invoiceGenerator->checkInvoiceAlreadyCreated($serviceIds);
        $getInvoiceID         = false;
        if ($jsonAction)
        {
            $getInvoiceID = true;
        }
        $invoiceCounter = 0;
        foreach ($products as $prod)
        {

            foreach ($packages[$prod->id] as $service)
            {
                //have product, service                
                if (isset($servicesAlreadyAdded[$service->id]))
                {
                    if ($jsonAction)
                    {
                        return array('invoiceID' => $invoiceGenerator->getLatestCreatedInvoiceInfo($service->id)['invoice_id']);
                    }
                    continue;
                }
                $invoiceCounter += $invoiceGenerator->createInvoice($service, $prod, $getInvoiceID);
            }
        }

        return $invoiceCounter;
    }
}
