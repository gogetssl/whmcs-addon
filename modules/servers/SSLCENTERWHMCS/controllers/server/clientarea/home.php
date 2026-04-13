<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea;

use MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule;
use MGModule\SSLCENTERWHMCS\eModels\cpanelservices\Service;
use MGModule\SSLCENTERWHMCS\eHelpers\Cpanel;
use MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription as AcmeHelper;
use MGModule\SSLCENTERWHMCS\models\orders\Repository as OrderRepo;
use MGModule\SSLCENTERWHMCS\models\logs\Repository as LogsRepo;

/**
 * Description of home
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class home extends main\mgLibs\process\AbstractController {

    function indexHTML($input, $vars = array()) {
        try {

            global $CONFIG;

            if($input['params']['status'] != 'Active')
            {
                return true;
            }

            if (AcmeHelper::isAcmeByServiceParams($input['params']))
            {
                if (isset($_GET['acmeconfig']) && (string) $_GET['acmeconfig'] === '1')
                {
                    return $this->acmeConfigurationHTML($input, $vars);
                }
                return $this->indexAcmeHTML($input, $vars);
            }

            $disabledValidationMethods = [];
 
            $serviceId  = $input['params']['serviceid'];
            $serviceBillingCycle = $input['params']['templatevars']['billingcycle'];
            $userid = $input['params']['userid'];
            $ssl        = new main\eRepository\whmcs\service\SSL();
            $sslService = $ssl->getByServiceId($serviceId);
        
            if(($sslService->configdata->ssl_status == 'pending' || $sslService->configdata->ssl_status == 'reissue' || $sslService->configdata->ssl_status == 'new_order' || $sslService->configdata->ssl_status == 'processing' || $sslService->configdata->ssl_status == '') && $sslService->remoteid != '' && $sslService->remoteid != 0)
            {
                $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
                $sslService = $sslRepo->getByServiceId($serviceId);

                if (is_null($sslService)) {
                    throw new \Exception(main\mgLibs\Lang::absoluteT('createNotInitialized'));
                }

                if ($input['params']['userid'] != $sslService->userid) {
                    throw new \Exception(main\mgLibs\Lang::absoluteT('anErrorOccurred'));
                }

                $apicertdata = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid);

                $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService, $apicertdata);
                $configDataUpdate->run();

               // if($apicertdata['status'] != 'new_order')
                //{
                //    $sslService->setSSLStatus($apicertdata['status']);
                //    $sslService->setPartnerOrderId($apicertdata['partner_order_id']);
                //    $sslService->setApproverEmails($apicertdata['approver_email']);
                //    $sslService->setDomain($apicertdata['domain']);
                //    $sslService->save();
                //}
                $vars['activationStatus'] = $apicertdata['status'];
                //var_dump($apicertdata);
            }
            //var_dump(\MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($sslService->remoteid)); die;

            $vars['brandsWithOnlyEmailValidation'] = ['geotrust','thawte','rapidssl','symantec',];

            if(is_null($sslService)) {
                throw new \Exception(main\mgLibs\Lang::absoluteT('anErrorOccurredPleaseContactSupport'));
            }

            $url = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Config::getInstance()->getConfigureSSLUrl($sslService->id, $serviceId);

            $vars['privateKey'] = '';
            $privateKey = $sslService->getPrivateKey();
            if($privateKey) {
                $vars['privateKey'] = $privateKey;
            }
            $vars['san_revalidate'] = false;

            if ($sslService->status !== 'Awaiting Configuration') {
                try {
                    $certificateDetails = (array)$sslService->configdata;

                    if(isset($certificateDetails['crt']) && !empty($certificateDetails['crt']))
                    {
                        $sslOrderRepo = new OrderRepo();
                        $checkOrderSSL = $sslOrderRepo->checkOrdersInstallation($serviceId);

                        $service = new Service();
                        $serviceCpanel = $service->getServiceByDomain($input['params']['userid'], $input['params']['domain']);
                        if($serviceCpanel !== false && $checkOrderSSL === true)
                        {
                            $vars['btnInstallCrt'] = true;
                        }
                    }

                    if(!empty($certificateDetails['partner_order_id'])) {
                        $vars['partner_order_id'] = $certificateDetails['partner_order_id'];
                    }
                    if(!empty($certificateDetails['product_brand'])) {
                        $vars['brand'] = $certificateDetails['product_brand'];
                    }

                    if(!empty($certificateDetails['dcv_method']))
                    {
                        $vars['dcv_method'] = $certificateDetails['dcv_method'];

                        if(in_array($vars['dcv_method'], ["http", "https", "dns"]))
                        {

                            if(is_array($certificateDetails['approver_method']))
                            {
                                $vars['approver_method'][$vars['dcv_method']] = $certificateDetails['approver_method'][$vars['dcv_method']];
                            }
                            else
                            {
                                $vars['approver_method'][$vars['dcv_method']] = (array) $certificateDetails['approver_method']->{$vars['dcv_method']};
                            }
                            
                            if($vars['dcv_method'] == 'http' || $vars['dcv_method'] == 'https'){
                               $vars['approver_method'][$vars['dcv_method']]['content'] = explode(PHP_EOL, $vars['approver_method'][$vars['dcv_method']]['content']);
                            }
                        } else {
                            $vars['dcv_method'] = 'email';
                            $vars['approver_method'] = $certificateDetails['approveremail'];
                        }
                    }

                    if (!empty($certificateDetails['csr'])) {
                        $vars['csr'] = ($certificateDetails['csr']);
                    }

                    if (!empty($certificateDetails['crt'])) {
                        $vars['crt'] = ($certificateDetails['crt']);
                    }
                    if (!empty($certificateDetails['ca'])) {
                        $vars['ca'] = ($certificateDetails['ca']);
                    }

                    if (!empty($certificateDetails['domain'])) {
                        $vars['domain'] = $certificateDetails['domain'];
                    }

                    if (!empty($certificateDetails['san_details'])) {
                        foreach ($certificateDetails['san_details'] as $san) {
                            $vars['sans'][$san->san_name]['san_name'] = $san->san_name;
                            $vars['sans'][$san->san_name]['method'] = $san->validation_method;
                            switch ($san->validation_method) {
                                case 'dns':
                                    $vars['san_revalidate'] = true;
                                    $vars['sans'][$san->san_name]['san_validation'] = $san->validation->dns->record;
                                    break;
                                case 'http':
                                    $vars['san_revalidate'] = true;
                                    $vars['sans'][$san->san_name]['san_validation'] = (array)$san->validation->http;
                                    $vars['sans'][$san->san_name]['san_validation']['content'] = explode(PHP_EOL, $san->validation->http->content);
                                    break;
                                case 'https':
                                    $vars['san_revalidate'] = true;
                                    $vars['sans'][$san->san_name]['san_validation'] = (array)$san->validation->https;
                                    $vars['sans'][$san->san_name]['san_validation']['content'] = explode(PHP_EOL, $san->validation->https->content);
                                    break;
                                default:
                                    $vars['sans'][$san->san_name]['san_validation'] = $san->validation->email;
                                    break;
                            }
                        }
                    }
                    if (!$vars['activationStatus']) {
                        $vars['activationStatus'] = $certificateDetails['ssl_status'];
                    }
                    //valid from
                    $vars['validFrom'] = fromMySQLDate($certificateDetails['valid_from'], false, true);
                    //expires
                    $vars['validTill'] = fromMySQLDate($certificateDetails['valid_till'],false,true);
                                        
                    $now = strtotime(date('Y-m-d'));
                    $end_date = strtotime($certificateDetails['valid_till']);
                    $datediff = $now - $end_date;
                        
                    $vars['nextReissue'] = abs(round($datediff / (60 * 60 * 24)));
                    
                    if(isset($certificateDetails['begin_date']) && !empty($certificateDetails['begin_date']))
                    {
                        $vars['subscriptionStarts'] = fromMySQLDate($certificateDetails['begin_date'],false,true);
                    }
                    
                    if(isset($certificateDetails['end_date']) && !empty($certificateDetails['end_date']))
                    {
                        $vars['subscriptionEnds'] = fromMySQLDate($certificateDetails['end_date'],false,true);
                    }
                    
                    //service billing cycle
                    $vars['serviceBillingCycle'] = $serviceBillingCycle;
                    $vars['displayRenewButton'] = false;
                    $today = date('Y-m-d');
                    $diffDays =  abs(strtotime($certificateDetails['end_date']) - strtotime($today))/86400;

                    if($diffDays < 30)
                        $vars['displayRenewButton'] = true;

                    $disabledValidationMethods = [];
                    $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
                    
                    $product = new \MGModule\SSLCENTERWHMCS\models\whmcs\product\Product($input['params']['pid']);
                    $productssl = false;
                    $checkTable = Capsule::schema()->hasTable('mgfw_SSLCENTER_product_brand');
                    if($checkTable)
                    {
                        if (Capsule::schema()->hasColumn('mgfw_SSLCENTER_product_brand', 'data'))
                        {
                            $productsslDB = Capsule::table('mgfw_SSLCENTER_product_brand')->where('pid', $product->configuration()->text_name)->first();
                            if(isset($productsslDB->data))
                            {
                                $productssl['product'] = json_decode($productsslDB->data, true); 
                            }
                        }
                    }

                    if(!$productssl)
                    {
                        $productssl = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->getProduct($product->configuration()->text_name);
                    }

                    if(!$productssl['product']['dcv_email'])
                    {
                        array_push($disabledValidationMethods, 'email');
                    }
                    if(!$productssl['product']['dcv_dns'])
                    {
                        array_push($disabledValidationMethods, 'dns');
                    }
                    if(!$productssl['product']['dcv_http'])
                    {
                        array_push($disabledValidationMethods, 'http');
                    }
                    if(!$productssl['product']['dcv_https'])
                    {
                        array_push($disabledValidationMethods, 'https');
                    }
                    
                } catch (\Exception $ex) {
                    $vars['error'] = main\mgLibs\Lang::absoluteT('canNotLoadOrderDetails');
                }
            }

            $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
            
            $vars['custom_guide'] = $apiConf->custom_guide;
            $vars['visible_renew_button'] = $apiConf->visible_renew_button;
            $vars['disabledValidationMethods'] = $disabledValidationMethods;
            $vars['configurationStatus'] = $sslService->status;
            $vars['configurationURL']    = $url;
            $vars['allOk']               = true;
            $vars['assetsURL'] = main\Server::I()->getAssetsURL();
            $vars['serviceid'] = $serviceId;
            $vars['userid'] = $userid;
            
            $filenameCsr = isset($vars['domain']) && !empty($vars['domain']) ? $vars['domain'] : 'csr_code';
            $filenameCrt = isset($vars['domain']) && !empty($vars['domain']) ? $vars['domain'] : 'crt_code';
            $filenameCa = isset($vars['domain']) && !empty($vars['domain']) ? $vars['domain'] : 'ca_code';
            
            if($_GET['download'] == '1')
            {
                if(isset($vars['sans'][$_GET['domain']]) && !empty($vars['sans'][$_GET['domain']]) && ($vars['sans'][$_GET['domain']]['method'] == 'http' || $vars['sans'][$_GET['domain']]['method'] == 'https'))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['sans'][$_GET['domain']]['san_validation']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    print implode(PHP_EOL, $vars['sans'][$_GET['domain']]['san_validation']['content']);
                    exit;
                }

                if(isset($vars['approver_method']['https']) && !empty($vars['approver_method']['https']))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['approver_method']['https']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    print implode(PHP_EOL, $vars['approver_method']['https']['content']);
                    exit;
                }

                if(isset($vars['approver_method']['http']) && !empty($vars['approver_method']['http']))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['approver_method']['http']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    print implode(PHP_EOL, $vars['approver_method']['http']['content']);
                    exit;
                }
            }

            if($_GET['downloadcsr'] == '1' && !empty($certificateDetails['csr']))
            {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$filenameCsr.'.csr');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                print $certificateDetails['csr'];
                exit;
            }
            if($_GET['downloadcrt'] == '1' && !empty($certificateDetails['crt']))
            {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$filenameCrt.'.crt');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                print $certificateDetails['crt'];
                exit;
            }
            if($_GET['downloadca'] == '1' && !empty($certificateDetails['ca']))
            {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$filenameCa.'.ca');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                print $certificateDetails['ca'];
                exit;
            }
            if($_GET['downloadpem'] == '1' && !empty($certificateDetails['crt']) && !empty($certificateDetails['ca']))
            {
                $pemfile = '';
                
                $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
                $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);
                $privateKey = $sslService->getPrivateKey();

                if(!empty($privateKey))
                {
                    if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') !== false) {
                        $pemfile .= $privateKey;
                    }
                    else {
                        $pemfile .= decrypt($privateKey);
                    }
                }

                $pemfile .= $certificateDetails['crt']. "\n";
                $pemfile .= $certificateDetails['ca'];
                
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$filenameCa.'.pem');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                print $pemfile;
                exit;
            }

            $vars['actual_link'] = $CONFIG['SystemURL'].'/clientarea.php?action=productdetails&id='.$vars['serviceid'];

            $vars['btndownload'] = false;

            if (!empty($certificateDetails['csr'])) {
                $vars['downloadcsr'] = $vars['actual_link'].'&downloadcsr=1';
            }

            if (!empty($certificateDetails['crt'])) {
                $vars['downloadcrt'] = $vars['actual_link'].'&downloadcrt=1';
            }

            if (!empty($certificateDetails['ca'])) {
                $vars['downloadca'] = $vars['actual_link'].'&downloadca=1';
            }
            
            if (!empty($certificateDetails['crt']) && !empty($certificateDetails['ca'])) {
                $vars['downloadpem'] = $vars['actual_link'].'&downloadpem=1';
            }

            if((isset($vars['approver_method']['http']) && !empty($vars['approver_method']['http'])) || (isset($vars['approver_method']['https']) && !empty($vars['approver_method']['https'])))
            {
                $vars['btndownload'] = $vars['actual_link'].'&download=1';
            }

            foreach($vars['sans'] as $detailssan)
            {
                if($detailssan['method'] == 'http' || $detailssan['method'] == 'https')
                {
                    $vars['btndownload'] = $vars['actual_link'].'&download=1&domain='.$detailssan['san_name'];
                }
            }

        } catch (\Exception $ex) {
            $vars['error'] = $ex->getMessage();
        }

        $vars['configoption23'] = $input['params']['configoption23'];
        $vars['configoption24'] = $input['params']['configoption24'];
           
        $vars['approver_email'] = isset($sslService->configdata->approver_method->email) && !empty($sslService->configdata->approver_method->email) ? $sslService->configdata->approver_method->email : false;
        
        return array(
            'tpl'  => 'home'
            , 'vars' => $vars
        );

    }

    function testHTML($input, $vars = array()) {
        return array(
            'tpl'  => 'test'
            , 'vars' => $vars
        );

    }

    private function indexAcmeHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];
        $userId    = (int) $input['params']['userid'];
        $sslRepo   = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);

        $product = Capsule::table('tblproducts')
            ->select(['name', 'configoption13'])
            ->where('id', (int) $input['params']['pid'])
            ->first();

        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $domainRepo       = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();

        $subscription = $subscriptionRepo->getByServiceId($serviceId);
        if ($subscription && (int) $subscription->api_order_id > 0 && strtolower((string) $subscription->status) !== 'active')
        {
            try
            {
                $this->refreshAcmeCertificateDetails($serviceId);
                $subscription = $subscriptionRepo->getByServiceId($serviceId);
            }
            catch (\Exception $e)
            {
                main\eHelpers\Whmcs::savelogActivitySSLCenter(
                    'SSLCENTER WHMCS: Unable to auto-refresh ACME certificate details for service #' . (int) $serviceId . '. Error: ' . $e->getMessage()
                );
            }
        }
        $domains      = $domainRepo->getByServiceId($serviceId);
        $limits = $this->getAcmeDomainLimits($input['params']);
        $singleIncludedSans = $this->getAcmeIncludedSans($input['params'], 'single');
        $wildcardIncludedSans = $this->getAcmeIncludedSans($input['params'], 'wildcard');
        $singleBoughtSans = isset($input['params']['configoptions']['sans_count']) ? (int) $input['params']['configoptions']['sans_count'] : 0;
        $wildcardBoughtSans = isset($input['params']['configoptions']['sans_wildcard_count']) ? (int) $input['params']['configoptions']['sans_wildcard_count'] : 0;
        $activeCounts = $this->getActiveAcmeDomainsCount($serviceId, $domainRepo->tableName);
        $singleSansCurrent = $activeCounts['single'];
        $wildcardSansCurrent = $activeCounts['wildcard'];
        $totalSansCurrent = $singleSansCurrent + $wildcardSansCurrent;
        $singleSansPurchased = $singleIncludedSans + $singleBoughtSans;
        $wildcardSansPurchased = $wildcardIncludedSans + $wildcardBoughtSans;
        $totalSansPurchased = $singleSansPurchased + $wildcardSansPurchased;
        $availableSingleSlots = max(0, $limits['single'] - $singleSansCurrent);
        $availableWildcardSlots = max(0, $limits['wildcard'] - $wildcardSansCurrent);
        $canAddDomains = $availableSingleSlots > 0 || ($availableWildcardSlots > 0 && isset($product->configoption13) && $product->configoption13 === 'on');

        $vars['allOk']                = true;
        $vars['serviceid']            = $serviceId;
        $vars['userid']               = $userId;
        $vars['assetsURL']            = main\Server::I()->getAssetsURL();
        $vars['isAcmeSubscription']   = true;
        $vars['productName']          = isset($product->name) ? $product->name : main\mgLibs\Lang::absoluteT('acmeDefaultProductName');
        $vars['allowWildcard']        = isset($product->configoption13) && $product->configoption13 === 'on';
        $vars['subscription']         = $subscription;
        $vars['domains']              = $domains;
        $vars['singleSansCurrent']    = $singleSansCurrent;
        $vars['singleSansPurchased']  = $singleSansPurchased;
        $vars['wildcardSansCurrent']  = $wildcardSansCurrent;
        $vars['wildcardSansPurchased']= $wildcardSansPurchased;
        $vars['totalSansCurrent']     = $totalSansCurrent;
        $vars['totalSansPurchased']   = $totalSansPurchased;
        $vars['availableSingleSlots'] = $availableSingleSlots;
        $vars['availableWildcardSlots'] = $availableWildcardSlots;
        $vars['canAddDomains']        = $canAddDomains;
        $vars['configurationStatus']  = $sslService ? $sslService->status : null;
        $vars['configurationURL']     = 'clientarea.php?action=productdetails&id=' . $serviceId . '&acmeconfig=1';
        $vars['nextInvoiceDate']      = '';

        if ($subscription && (int) $subscription->auto_renew === 1 && !empty($subscription->renewal_date))
        {
            $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
            $renewInvoiceDays = isset($apiConf->renew_invoice_days_subscription) && is_numeric($apiConf->renew_invoice_days_subscription)
                ? (int) $apiConf->renew_invoice_days_subscription
                : 0;
            if ($renewInvoiceDays < 0) {
                $renewInvoiceDays = 0;
            }

            $renewalTimestamp = strtotime((string) $subscription->renewal_date);
            if ($renewalTimestamp !== false) {
                $vars['nextInvoiceDate'] = date('Y-m-d', strtotime('-' . $renewInvoiceDays . ' days', $renewalTimestamp));
            }
        }

        if ($vars['configurationStatus'] === 'Awaiting Configuration')
        {
            return array(
                'tpl' => 'home_acme_awaiting_configuration',
                'vars' => $vars
            );
        }

        return array(
            'tpl' => 'home_acme',
            'vars' => $vars
        );
    }

    private function acmeConfigurationHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];
        $userId    = (int) $input['params']['userid'];

        $product = Capsule::table('tblproducts')
            ->select(['name', 'configoption13'])
            ->where('id', (int) $input['params']['pid'])
            ->first();

        $singleLimit = $this->getAcmeIncludedSans($input['params'], 'single');
        $singleOptionCount = isset($input['params']['configoptions']['sans_count']) ? (int) $input['params']['configoptions']['sans_count'] : 0;
        $singleLimit += $singleOptionCount;
        if ($singleLimit <= 0)
        {
            $singleLimit = 1;
        }

        $wildcardLimit = $this->getAcmeIncludedSans($input['params'], 'wildcard');
        $wildcardOptionCount = isset($input['params']['configoptions']['sans_wildcard_count']) ? (int) $input['params']['configoptions']['sans_wildcard_count'] : 0;
        $wildcardLimit += $wildcardOptionCount;
        if ($wildcardLimit <= 0)
        {
            $wildcardLimit = 1;
        }

        $vars['allOk']              = true;
        $vars['serviceid']          = $serviceId;
        $vars['userid']             = $userId;
        $vars['assetsURL']          = main\Server::I()->getAssetsURL();
        $vars['isAcmeSubscription'] = true;
        $vars['productName']        = isset($product->name) ? $product->name : main\mgLibs\Lang::absoluteT('acmeDefaultProductName');
        $vars['allowWildcard']      = isset($product->configoption13) && $product->configoption13 === 'on';
        $vars['singleDomainsLimit'] = $singleLimit;
        $vars['wildcardDomainsLimit'] = $wildcardLimit;

        return array(
            'tpl' => 'home_acme_configuration',
            'vars' => $vars
        );
    }

    public function createSubscriptionJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $singleDomains = $this->extractDomainsFromInput(isset($input['single_domains']) ? $input['single_domains'] : '');
        $wildcardDomains = $this->extractDomainsFromInput(isset($input['wildcard_domains']) ? $input['wildcard_domains'] : '');
        $domainsWithType = [];

        if (empty($singleDomains) && empty($wildcardDomains))
        {
            $domains = $this->extractDomainsFromInput(isset($input['domains']) ? $input['domains'] : '');
            if (empty($domains))
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acmePleaseProvideAtLeastOneDomain'));
            }

            foreach ($domains as $domain)
            {
                if (strpos($domain, '*.') === 0)
                {
                    $domainsWithType[$domain] = 'wildcard';
                }
                else
                {
                    $domainsWithType[$domain] = 'single';
                }
            }
        }
        else
        {
            foreach ($singleDomains as $domain)
            {
                $domainsWithType[$domain] = 'single';
            }
            foreach ($wildcardDomains as $domain)
            {
                $domainsWithType[$domain] = 'wildcard';
            }
        }

        if (empty($domainsWithType))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmePleaseProvideAtLeastOneDomain'));
        }

        $limits = $this->getAcmeDomainLimits($input['params']);
        $singleDomainsCount = 0;
        $wildcardDomainsCount = 0;
        foreach ($domainsWithType as $type)
        {
            if ($type === 'wildcard')
            {
                $wildcardDomainsCount++;
            }
            else
            {
                $singleDomainsCount++;
            }
        }

        if ($singleDomainsCount > $limits['single'])
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeExceededSingleDomainsLimit'), $limits['single']));
        }
        if ($wildcardDomainsCount > 0 && $wildcardDomainsCount > $limits['wildcard'])
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeExceededWildcardDomainsLimit'), $limits['wildcard']));
        }

        if (in_array('wildcard', $domainsWithType, true) && (!isset($input['params']['configoption13']) || $input['params']['configoption13'] !== 'on'))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeWildcardDomainsNotEnabled'));
        }

        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $domainRepo       = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();

        $subscription = $subscriptionRepo->getByServiceId($serviceId);
        if ($subscription && (int) $subscription->api_order_id > 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionAlreadyCreated'));
        }

        foreach ($domainsWithType as $domain => $type)
        {
            $this->validateAcmeDomain($domain, $type);
        }

        $domains = array_keys($domainsWithType);

        $api = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi();
        $acmeProductId = isset($input['params']['configoption1']) ? (int) $input['params']['configoption1'] : (int) AcmeHelper::getProductIds()[0];
        $response = $api->createAcmeSubscription([
            'product_id' => $acmeProductId,
            'domains'    => implode(',', $domains),
        ]);

        $apiOrderId = isset($response['order_id']) ? (int) $response['order_id'] : (isset($response['id']) ? (int) $response['id'] : 0);
        $apiItems = isset($response['items']) && is_array($response['items']) ? $response['items'] : array();
        $apiFirstItem = isset($apiItems[0]) && is_array($apiItems[0]) ? $apiItems[0] : array();
        $acmeId = isset($apiFirstItem['id']) ? (int) $apiFirstItem['id'] : (isset($response['item_id']) ? (int) $response['item_id'] : 0);
        if ($apiOrderId <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeMissingSubscriptionOrderId'));
        }

        $primaryDomain = reset($domains);
        if (!empty($primaryDomain))
        {
            Capsule::table('tblhosting')->where('id', $serviceId)->update([
                'domain' => $primaryDomain
            ]);
        }

        $subscription = $subscriptionRepo->upsertByServiceId($serviceId, [
            'client_id'        => (int) $input['params']['userid'],
            'api_order_id'     => $apiOrderId,
            'acme_id'          => $acmeId > 0 ? $acmeId : null,
            'status'           => isset($response['status']) ? $response['status'] : 'active',
            'acme_account_id'  => isset($response['acme_account_id']) ? (string) $response['acme_account_id'] : '',
            'eab_kid'          => isset($response['eab_kid']) ? (string) $response['eab_kid'] : '',
            'eab_hmac_key'     => isset($response['eab_hmac_key']) ? (string) $response['eab_hmac_key'] : '',
            'server_url'       => isset($response['server_url']) ? (string) $response['server_url'] : '',
            'period_start'     => isset($response['begin_date']) ? $response['begin_date'] : null,
            'period_end'       => isset($response['end_date']) ? $response['end_date'] : null,
            'renewal_date'     => isset($response['renewal_date']) ? $response['renewal_date'] : (isset($response['end_date']) ? $response['end_date'] : null),
            'auto_renew'       => 1,
        ]);

        foreach ($domainsWithType as $domain => $type)
        {
            $domainRepo->addDomain($serviceId, $domain, $type);
        }

        $sslRepo = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);
        if ($sslService)
        {
            $sslService->setRemoteId($apiOrderId);
            $sslService->status = 'Configuration Submitted';
            $sslService->setConfigdataKey('subscription', true);
            $sslService->setSSLStatus($subscription->status);
            if (!empty($subscription->period_start))
            {
                $sslService->setConfigdataKey('begin_date', $subscription->period_start);
            }
            if (!empty($subscription->period_end))
            {
                $sslService->setConfigdataKey('end_date', $subscription->period_end);
            }
            if (!empty($subscription->renewal_date))
            {
                $sslService->setConfigdataKey('renewal_date', $subscription->renewal_date);
            }
            $sslService->save();

            $orderRepo = new \MGModule\SSLCENTERWHMCS\models\orders\Repository();
            $usedTypes = array_values(array_unique(array_values($domainsWithType)));
            $verificationMethod = count($usedTypes) > 1 ? 'mixed' : ($usedTypes[0] === 'wildcard' ? 'dns' : 'http');
            $orderRepo->addOrder(
                (int) $input['params']['userid'],
                $serviceId,
                (int) $sslService->id,
                $verificationMethod,
                'Configuration Submitted',
                $response
            );
        }

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeSubscriptionConfigurationSubmittedSuccessfully'),
        ];
    }

    public function buyMoreDomainsJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $singleDomains = $this->extractDomainsFromInput(isset($input['single_domains']) ? $input['single_domains'] : '');
        $wildcardDomains = $this->extractDomainsFromInput(isset($input['wildcard_domains']) ? $input['wildcard_domains'] : '');
        $domainsWithType = [];

        if (empty($singleDomains) && empty($wildcardDomains))
        {
            $domains = $this->extractDomainsFromInput(isset($input['domains']) ? $input['domains'] : '');
            $domainType = isset($input['domain_type']) && $input['domain_type'] === 'wildcard' ? 'wildcard' : 'single';

            foreach ($domains as $domain)
            {
                $domainsWithType[$domain] = $domainType;
            }
        }
        else
        {
            foreach ($singleDomains as $domain)
            {
                $domainsWithType[$domain] = 'single';
            }
            foreach ($wildcardDomains as $domain)
            {
                $domainsWithType[$domain] = 'wildcard';
            }
        }

        if (empty($domainsWithType))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmePleaseProvideAtLeastOneDomain'));
        }

        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $domainRepo       = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        $existingDomains = [];
        foreach ($domainRepo->getByServiceId($serviceId) as $existingDomainRow)
        {
            $existingDomain = strtolower(trim((string) $existingDomainRow->domain));
            if ($existingDomain !== '')
            {
                $existingDomains[$existingDomain] = true;
            }
        }

        $duplicatedDomains = [];
        foreach (array_keys($domainsWithType) as $domain)
        {
            $normalizedDomain = strtolower(trim((string) $domain));
            if (isset($existingDomains[$normalizedDomain]))
            {
                $duplicatedDomains[] = $normalizedDomain;
            }
        }

        if (!empty($duplicatedDomains))
        {
            $duplicatedDomains = array_values(array_unique($duplicatedDomains));
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeDomainsAlreadyExist'), implode(', ', $duplicatedDomains)));
        }

        if (in_array('wildcard', $domainsWithType, true) && (!isset($input['params']['configoption13']) || $input['params']['configoption13'] !== 'on'))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeWildcardDomainsNotEnabled'));
        }

        $limits = $this->getAcmeDomainLimits($input['params']);
        $activeCounts = $this->getActiveAcmeDomainsCount($serviceId, $domainRepo->tableName);
        $remainingSingle = max(0, $limits['single'] - $activeCounts['single']);
        $remainingWildcard = max(0, $limits['wildcard'] - $activeCounts['wildcard']);

        $singleDomainsCount = 0;
        $wildcardDomainsCount = 0;
        foreach ($domainsWithType as $type)
        {
            if ($type === 'wildcard')
            {
                $wildcardDomainsCount++;
            }
            else
            {
                $singleDomainsCount++;
            }
        }

        if ($singleDomainsCount > $remainingSingle)
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeExceededAvailableSingleDomainSlots'), $remainingSingle));
        }

        if ($wildcardDomainsCount > $remainingWildcard)
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeExceededAvailableWildcardDomainSlots'), $remainingWildcard));
        }

        foreach ($domainsWithType as $domain => $type)
        {
            $this->validateAcmeDomain($domain, $type);
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addAcmeDomains((int) $subscription->acme_id, [
            'domains' => implode(',', array_keys($domainsWithType)),
        ]);

        foreach ($domainsWithType as $domain => $type)
        {
            $domainRepo->addDomain($serviceId, $domain, $type);
        }

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeDomainsAddedSuccessfully'),
        ];
    }

    public function upgradeConfigOptionsJSON($input, $vars = array())
    {
        $serviceId    = (int) $input['id'];
        $newSingle    = isset($input['new_sans_count']) ? (int) $input['new_sans_count'] : -1;
        $newWildcard  = isset($input['new_sans_wildcard_count']) ? (int) $input['new_sans_wildcard_count'] : -1;

        if ($newSingle < 0 && $newWildcard < 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeNoValueProvided'));
        }

        // Block upgrade if any unpaid invoice exists for this service
        $unpaidInvoice = Capsule::table('tblinvoices')
            ->join('tblinvoiceitems', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoiceitems.relid', $serviceId)
            ->whereIn('tblinvoices.status', ['Unpaid', 'Overdue'])
            ->first();

        if ($unpaidInvoice)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeUnpaidInvoiceExists'));
        }

        $domainRepo   = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();
        $activeCounts = $this->getActiveAcmeDomainsCount($serviceId, $domainRepo->tableName);

        $CORepo = new \MGModule\SSLCENTERWHMCS\models\whmcs\service\configOptions\Repository($serviceId);

        $configOptionsPayload = [];

        if ($newSingle >= 0)
        {
            if ($newSingle < $activeCounts['single'])
            {
                throw new \Exception(sprintf(
                    main\mgLibs\Lang::absoluteT('acmeDowngradeNotEnoughSingleSlots'),
                    $activeCounts['single']
                ));
            }
            $singleConfigId = $CORepo->getConfigID('sans_count');
            if (!$singleConfigId)
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeConfigOptionNotFound'));
            }
            $configOptionsPayload[$singleConfigId] = $newSingle;
        }

        if ($newWildcard >= 0)
        {
            if ($newWildcard < $activeCounts['wildcard'])
            {
                throw new \Exception(sprintf(
                    main\mgLibs\Lang::absoluteT('acmeDowngradeNotEnoughWildcardSlots'),
                    $activeCounts['wildcard']
                ));
            }
            $wildcardConfigId = $CORepo->getConfigID('sans_wildcard_count');
            if (!$wildcardConfigId)
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeConfigOptionNotFound'));
            }
            $configOptionsPayload[$wildcardConfigId] = $newWildcard;
        }

        $service = Capsule::table('tblhosting')->where('id', $serviceId)->first();
        if (!$service)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeUpgradeServiceNotFound'));
        }

        $adminUserName = main\eHelpers\Admin::getAdminUserName();
        $apiParams = [
            'serviceid'     => $serviceId,
            'paymentmethod' => $service->paymentmethod,
            'type'          => 'configoptions',
            'configoptions' => $configOptionsPayload,
        ];

        $result = localAPI('UpgradeProduct', $apiParams, $adminUserName);

        if (!isset($result['result']) || $result['result'] !== 'success')
        {
            $errorMsg = isset($result['message']) ? $result['message'] : 'UpgradeProduct API failed';
            throw new \Exception($errorMsg);
        }

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeUpgradeSuccess'),
        ];
    }

    public function removeDomainJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $domain    = isset($input['domain']) ? strtolower(trim($input['domain'])) : '';
        if (empty($domain))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeDomainIsRequired'));
        }

        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $domainRepo       = new \MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        $domainRow = Capsule::table($domainRepo->tableName)
            ->where('service_id', $serviceId)
            ->where('domain', $domain)
            ->where('status', 'added')
            ->first();

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->removeAcmeDomain((int) $subscription->api_order_id, (int)$subscription->acme_id, [
            'domain' => $domain,
        ]);

        $addedAt    = $domainRow ? strtotime((string) $domainRow->added_at) : false;
        $withinWindow = $addedAt !== false && (time() - $addedAt) < (30 * 24 * 60 * 60);

        if ($withinWindow)
        {
            // Added within 30 days — delete the row entirely so the slot is freed
            Capsule::table($domainRepo->tableName)
                ->where('service_id', $serviceId)
                ->where('domain', $domain)
                ->delete();

            return [
                'success' => true,
                'message' => main\mgLibs\Lang::absoluteT('acmeDomainRemovedAndRefunded'),
            ];
        }

        $domainRepo->removeDomain($serviceId, $domain);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeDomainRemovedSuccessfully'),
        ];
    }

    public function cancelSubscriptionJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->cancelCertificate((int) $subscription->api_order_id, 'Cancelled by client');
        $subscriptionRepo->upsertByServiceId($serviceId, [
            'status'       => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'auto_renew'   => 0,
        ]);

        Capsule::table('tblhosting')->where('id', $serviceId)->update([
            'domainstatus' => 'Cancelled'
        ]);

        $refundedCreditsAmount = $this->refundLatestPaidServiceInvoice($serviceId, 'ACME subscription cancelled within refund window');
        $message = main\mgLibs\Lang::absoluteT('acmeSubscriptionCancelled');
        if ($refundedCreditsAmount > 0)
        {
            $message = sprintf(
                main\mgLibs\Lang::absoluteT('acmeSubscriptionCancelledAndCreditsRefunded'),
                number_format((float) $refundedCreditsAmount, 2, '.', '')
            );
        }

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    public function stopAutoRenewalJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->disableSubscriptionAutoRenewal((int) $subscription->acme_id);
        $subscriptionRepo->upsertByServiceId($serviceId, [
            'auto_renew' => 0,
        ]);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeAutoRenewalDisabled'),
        ];
    }

    private function isWithinAcmeRefundWindow($serviceId, $days = 30)
    {
        $latestPaidInvoice = $this->getLatestPaidServiceInvoiceForRefund($serviceId);
        if ($latestPaidInvoice && !empty($latestPaidInvoice->datepaid))
        {
            $paidAt = strtotime($latestPaidInvoice->datepaid);
            if ($paidAt !== false)
            {
                $daysFromPayment = floor((time() - $paidAt) / 86400);
                return $daysFromPayment >= 0 && $daysFromPayment <= (int) $days;
            }
        }

        $service = Capsule::table('tblhosting')
            ->select(['regdate'])
            ->where('id', (int) $serviceId)
            ->first();

        if (!$service || empty($service->regdate))
        {
            return false;
        }

        $regDate = strtotime($service->regdate);
        if ($regDate === false)
        {
            return false;
        }

        $daysFromPurchase = floor((time() - $regDate) / 86400);
        return $daysFromPurchase <= (int) $days;
    }

    private function refundLatestPaidServiceInvoice($serviceId, $reason = '')
    {
        $invoice = $this->getLatestPaidServiceInvoiceForRefund($serviceId);

        if (!$invoice || empty($invoice->invoiceid))
        {
            return 0.00;
        }

        $creditPrefix = 'ACME refund for invoice #' . (int) $invoice->invoiceid;
        $alreadyCreditedAmount = Capsule::table('tblcredit')
            ->where('clientid', (int) $invoice->userid)
            ->where('description', 'LIKE', $creditPrefix . '%')
            ->sum('amount');

        if ((float) $alreadyCreditedAmount > 0)
        {
            return round((float) $alreadyCreditedAmount, 2);
        }

        $creditAmount = $this->getRefundableAcmeInvoiceAmount((int) $invoice->invoiceid, (int) $serviceId);
        if ($creditAmount <= 0)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter(
                'SSLCENTER WHMCS: Skipped ACME refund credit for invoice #' . (int) $invoice->invoiceid
                . ' (service #' . (int) $serviceId . ') because refundable ACME items amount is 0.'
            );
            return 0.00;
        }

        $adminUserName = main\eHelpers\Admin::getAdminUserName();
        $creditDescription = $creditPrefix;
        if (!empty($reason))
        {
            $creditDescription .= '. Reason: ' . $reason;
        }

        $result = localAPI('AddCredit', [
            'clientid'    => (int) $invoice->userid,
            'description' => $creditDescription,
            'amount'      => number_format($creditAmount, 2, '.', ''),
        ], $adminUserName);

        if (!isset($result['result']) || $result['result'] !== 'success')
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: Unable to add refund credit for invoice #' . (int) $invoice->invoiceid . ' (service #' . (int) $serviceId . ').');
            return 0.00;
        }

        main\eHelpers\Whmcs::savelogActivitySSLCenter(
            'SSLCENTER WHMCS: Credit refund added for invoice #' . (int) $invoice->invoiceid
            . ' (service #' . (int) $serviceId . ', amount: ' . number_format($creditAmount, 2, '.', '') . ').'
            . (!empty($reason) ? ' Reason: ' . $reason : '')
        );

        return round((float) $creditAmount, 2);
    }

    private function getLatestPaidServiceInvoiceForRefund($serviceId)
    {
        $baseQuery = Capsule::table('tblinvoiceitems')
            ->select([
                'tblinvoiceitems.invoiceid',
                'tblinvoiceitems.description',
                'tblinvoices.userid',
                'tblinvoices.total',
                'tblinvoices.datepaid',
            ])
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->where('tblinvoices.datepaid', '>', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->where('tblinvoiceitems.relid', (int)$serviceId)
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoices.status', 'Paid');

        $renewalInvoice = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('tblinvoiceitems.description', 'LIKE', '%ACME Subscription Renewal%');
            })
            ->orderBy('tblinvoices.datepaid', 'DESC')
            ->first();

        if ($renewalInvoice)
        {
            return $renewalInvoice;
        }

        return (clone $baseQuery)
            ->orderBy('tblinvoices.datepaid', 'DESC')
            ->first();
    }

    private function getRefundableAcmeInvoiceAmount($invoiceId, $serviceId)
    {
        $acmeProductIds = AcmeHelper::getProductIds();

        if(empty($acmeProductIds))
        {
            return 0.00;
        }

        $invoice     = \WHMCS\Billing\Invoice::find($invoiceId);
        $invoiceItem = $invoice->items()
            ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblinvoiceitems.invoiceid', (int)$invoiceId)
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoiceitems.relid', (int)$serviceId)
            ->where('tblproducts.servertype', 'SSLCENTERWHMCS')
            ->whereIn('tblproducts.configoption1', $acmeProductIds)
            ->select(['tblinvoiceitems.*'])
            ->first();

        $itemAmount     = $invoiceItem ? $invoiceItem->amount : 0.00;
        $clientsDetails = getClientsDetails($invoice->userid);
        $taxEnabled     = \WHMCS\Config\Setting::getValue("TaxEnabled");

        if($invoiceItem->taxed && $taxEnabled && !$clientsDetails["taxexempt"])
        {
            $taxRate  = $invoice->taxRate1;
            $taxRate2 = $invoice->taxRate2;

            if(round($taxRate, 2) == $taxRate)
            {
                $taxRate = \format_as_currency($taxRate);
            }
            if(round($taxRate2, 2) == $taxRate2)
            {
                $taxRate2 = \format_as_currency($taxRate2);
            }

            $taxCalculator = new Tax();
            $taxCalculator->setIsInclusive(\WHMCS\Config\Setting::getValue("TaxType") == "Inclusive")->setIsCompound(\WHMCS\Config\Setting::getValue("TaxL2Compound"));

            if(is_numeric($taxRate))
            {
                $taxCalculator->setLevel1Percentage($taxRate);
            }
            if(is_numeric($taxRate2))
            {
                $taxCalculator->setLevel2Percentage($taxRate2);
            }

            $tax = $tax2 = 0;

            $taxCalculator->setTaxBase($invoiceItem->amount);
            $tax          += $taxCalculator->getLevel1TaxTotal();
            $tax2         += $taxCalculator->getLevel2TaxTotal();
            $itemSubtotal = $taxCalculator->getTotalBeforeTaxes();
            $itemAmount   = $itemSubtotal + $tax + $tax2;
        }

        return round((float)$itemAmount, 2);
    }

    public function checkCertificateDetailsJSON($input, $vars = array())
    {
        $serviceId = (int) $input['id'];
        $this->refreshAcmeCertificateDetails($serviceId);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('acmeCertificateDetailsRefreshed'),
        ];
    }

    private function refreshAcmeCertificateDetails($serviceId)
    {
        $subscriptionRepo = new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository();
        $subscription     = $subscriptionRepo->getByServiceId($serviceId);

        if (!$subscription || (int) $subscription->api_order_id <= 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSubscriptionNotCreatedYet'));
        }

        $apiOrderId = (int) $subscription->api_order_id;
        $details = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getCertificateDetails('acme', $apiOrderId);

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

        $subscriptionRepo->upsertByServiceId($serviceId, [
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

        $sslRepo = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);
        if ($sslService)
        {
            $sslService->remoteid = $apiOrderId;
            $sslService->status   = 'Completed';
            $sslService->configdata = json_encode([
                'subscription' => true,
                'ssl_status'   => $orderStatus,
                'begin_date'   => $periodStart,
                'end_date'     => $periodEnd,
                'renewal_date' => $renewalDate,
            ]);
            $sslService->save();
        }
    }

    private function extractDomainsFromInput($domains)
    {
        $items = preg_split('/[\s,;]+/', trim((string) $domains));
        $items = array_filter(array_map('trim', $items));
        $items = array_values(array_unique(array_map('strtolower', $items)));

        return $items;
    }

    private function getAcmeDomainLimits(array $params)
    {
        $singleIncluded = $this->getAcmeIncludedSans($params, 'single');
        $singleBought = isset($params['configoptions']['sans_count']) ? (int) $params['configoptions']['sans_count'] : 0;
        $singleLimit = $singleIncluded + $singleBought;
        if ($singleLimit <= 0)
        {
            $singleLimit = 1;
        }

        $wildcardIncluded = $this->getAcmeIncludedSans($params, 'wildcard');
        $wildcardBought = isset($params['configoptions']['sans_wildcard_count']) ? (int) $params['configoptions']['sans_wildcard_count'] : 0;
        $wildcardLimit = $wildcardIncluded + $wildcardBought;
        if ($wildcardLimit <= 0)
        {
            $wildcardLimit = 1;
        }

        return [
            'single' => $singleLimit,
            'wildcard' => $wildcardLimit,
        ];
    }

    private function getAcmeIncludedSans(array $params, $sanType = 'single')
    {
        $key = ($sanType === 'wildcard') ? 'configoption8' : 'configoption4';
        if (isset($params[$key]) && is_numeric($params[$key]))
        {
            return (int) $params[$key];
        }

        $pid = isset($params['pid']) ? (int) $params['pid'] : 0;
        if ($pid <= 0)
        {
            return 0;
        }

        $column = ($sanType === 'wildcard') ? 'configoption8' : 'configoption4';
        $product = Capsule::table('tblproducts')->select([$column])->where('id', $pid)->first();
        if ($product && isset($product->{$column}) && is_numeric($product->{$column}))
        {
            return (int) $product->{$column};
        }

        return 0;
    }

    private function validateAcmeDomain($domain, $domainType = 'single')
    {
        $domain = strtolower(trim((string) $domain));

        if ($domainType === 'single' && strpos($domain, '*.') === 0)
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('acmeSingleDomainsOnlyRegularDomains'));
        }

        if ($domainType === 'wildcard')
        {
            if (strpos($domain, '*.') !== 0)
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acmeWildcardDomainsOnlyWildcardDomains'));
            }
            $domain = substr($domain, 2);
        }

        if (\MGModule\SSLCENTERWHMCS\eHelpers\Domains::validateDomain($domain) !== true)
        {
            throw new \Exception(sprintf(main\mgLibs\Lang::absoluteT('acmeInvalidDomain'), $domain));
        }
    }

    private function getActiveAcmeDomainsCount($serviceId, $tableName)
    {
        $activeDomains = Capsule::table($tableName)
            ->select(['domain_type'])
            ->where('service_id', (int) $serviceId)
            ->get();

        $single = 0;
        $wildcard = 0;
        foreach ($activeDomains as $domainRow)
        {
            if (isset($domainRow->domain_type) && strtolower((string) $domainRow->domain_type) === 'wildcard')
            {
                $wildcard++;
            }
            else
            {
                $single++;
            }
        }

        return [
            'single' => $single,
            'wildcard' => $wildcard,
        ];
    }

    public function renewJSON($input, $vars = array()) {

        try
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMCS: The renewal action was initiated for the Service ID: " . $input['id']);

            $errorInvoiceExist = false;
            $cron = new \MGModule\SSLCENTERWHMCS\controllers\addon\admin\Cron();
            $service = \WHMCS\Service\Service::where('id', $input['id'])->get();
            $result = $cron->createAutoInvoice(array($input['params']['pid'] => $service), $input['id'], true);
            if(is_array($result) && isset($result['invoiceID']))
            {
                $existInvoiceID = $result['invoiceID'];
                $errorInvoiceExist =  \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('Related invoice already exist.');
            }
        }
        catch(Exception $e)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMC Renew Action Error: " . $e->getMessage());
            return array(
                'error' => $e->getMessage(),
            );
        }
        if($errorInvoiceExist)
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMC Renew Action Error: " . $errorInvoiceExist);

            return array(
                'error' => $errorInvoiceExist,
                'invoiceID' => $existInvoiceID
            );
        }

        main\eHelpers\Whmcs::savelogActivitySSLCenter("SSLCENTER WHMC Renew Action: A new invoice has been successfully created for the Service ID: " . $input['id']);
        return array(
            'success' => true,
            'msg' =>  main\mgLibs\Lang::absoluteT('A new invoice has been successfully created. '),
            'invoiceID' => $result
        );
    }

    public function resendValidationEmailJSON($input, $vars = array()) {
        $ssl = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $ssl->getByServiceId($input['id']);
        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->resendValidationEmail($serviceSSL->remoteid);

        return array(
            'success' => $response['message']
        );
    }

    public function sendCertificateEmailJSON($input, $vars = array()) {
        $ssl = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $ssl->getByServiceId($input['id']);
        $orderStatus = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($serviceSSL->remoteid);

        if($orderStatus['status'] !== 'active') {
            throw new \Exception( \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('orderNotActiveError')); //Can not send certificate. Order status is different than active.
        }

        if(empty($orderStatus['ca_code'])) {
            throw new \Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('CACodeEmptyError')); //An error occurred. Certificate body is empty.
        }
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $sendCertyficateTermplate = $apiConf->send_certificate_template;

        $pathAttachemts = false;
        $checkSettings = Capsule::schema()->hasTable('tblfileassetsettings');
        if($checkSettings !== false) {
            $filesetting = Capsule::table('tblfileassetsettings')->where('asset_type', 'email_attachments')->first();
            if(isset($filesetting->storageconfiguration_id) && !empty($filesetting->storageconfiguration_id))
            {
                $checkStorage = Capsule::schema()->hasTable('tblstorageconfigurations');
                if($checkStorage !== false) {

                    $storage = Capsule::table('tblstorageconfigurations')->where('id', $filesetting->storageconfiguration_id)->first();
                    if(isset($storage->settings) && !empty($storage->settings))
                    {
                        $storageData = json_decode($storage->settings, true);
                        if(isset($storageData['local_path']) && !empty($storageData['local_path']))
                        {
                            $pathAttachemts = $storageData['local_path'];
                        }
                    }
                }
            }
        }

        $attachments = array();
        if(!empty($orderStatus['ca_code'])) {
            if($pathAttachemts === false) {
                $tmp_ca_code = tempnam("/tmp", "FOO");
                $handle = fopen($tmp_ca_code, "w");
                fwrite($handle, $orderStatus['ca_code']);
                fclose($handle);

                $attachments[] = array(
                    'displayname' => 'ca_code.ca',
                    'path' => $tmp_ca_code
                );
            }
            else
            {
                $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$input['params']['serviceid'].$input['params']['accountid'].'_ca_code.ca';
                file_exists($filetemp) or touch($filetemp);
                file_put_contents($filetemp, $orderStatus['csr_code']);

                $attachments[] = array(
                    'displayname' => $input['params']['serviceid'].$input['params']['accountid'].'_ca_code.ca',
                    'filename' => $input['params']['serviceid'].$input['params']['accountid'].'_ca_code.ca'
                );
            }
        }

        if(!empty($orderStatus['crt_code'])) {
            if($pathAttachemts === false) {
                $tmp_crt_code = tempnam("/tmp", "FOO");
                $handle = fopen($tmp_crt_code, "w");
                fwrite($handle, $orderStatus['crt_code']);
                fclose($handle);

                $attachments[] = array(
                    'displayname' => 'crt_code.crt',
                    'path' => $tmp_crt_code
                );
            }
            else
            {
                $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$input['params']['serviceid'].$input['params']['accountid'].'_crt_code.crt';
                file_exists($filetemp) or touch($filetemp);
                file_put_contents($filetemp, $orderStatus['csr_code']);

                $attachments[] = array(
                    'displayname' => $input['params']['serviceid'].$input['params']['accountid'].'_crt_code.crt',
                    'filename' => $input['params']['serviceid'].$input['params']['accountid'].'_crt_code.crt'
                );
            }
        }

        if(!empty($orderStatus['csr_code'])) {
            if($pathAttachemts === false) {
                $tmp_csr_code = tempnam("/tmp", "FOO");
                $handle = fopen($tmp_csr_code, "w");
                fwrite($handle, $orderStatus['csr_code']);
                fclose($handle);

                $attachments[] = array(
                    'displayname' => 'csr_code.csr',
                    'path' => $tmp_csr_code
                );
            }
            else
            {
                $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$input['params']['serviceid'].$input['params']['accountid'].'_csr_code.csr';
                file_exists($filetemp) or touch($filetemp);
                file_put_contents($filetemp, $orderStatus['csr_code']);

                $attachments[] = array(
                    'displayname' => $input['params']['serviceid'].$input['params']['accountid'].'_csr_code.csr',
                    'filename' => $input['params']['serviceid'].$input['params']['accountid'].'_csr_code.csr'
                );
            }
        }

        if($sendCertyficateTermplate == NULL)
        {
            $result = sendMessage(\MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::SEND_CERTIFICATE_TEMPLATE_ID, $input['id'], [
                'domain' => $orderStatus['domain'],
                'ssl_certyficate' => nl2br($orderStatus['ca_code']),
                'crt_code' => nl2br($orderStatus['crt_code']),
            ], false, $attachments);
        }
        else
        {
            $templateName = \MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::getTemplateName($sendCertyficateTermplate);
            $result = sendMessage($templateName, $input['id'], [
                'domain' => $orderStatus['domain'],
                'ssl_certyficate' => nl2br($orderStatus['ca_code']),
                'crt_code' => nl2br($orderStatus['crt_code']),
            ], false, $attachments);
        }

        if(!empty($orderStatus['ca_code'])) {

            unlink($tmp_ca_code);

        }

        if(!empty($orderStatus['crt_code'])) {

            unlink($tmp_crt_code);

        }

        if(!empty($orderStatus['csr_code'])) {

            unlink($tmp_csr_code);

        }

        if($result === true)
        {
             return array(
                'success' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('sendCertificateSuccess')
            );
        }

        throw new \Exception(\MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T($result));
    }

    function revalidateJSON($input, $vars = array()) {
        $serviceId  = $input['params']['serviceid'];
        $ssl        = new main\eRepository\whmcs\service\SSL();
        $sslService = $ssl->getByServiceId($serviceId);

        $brand = $input['brand'];
        
        if($brand == 'digicert' || $brand == 'geotrust' || $brand == 'thawte' || $brand == 'rapidssl')
        {
            if(isset($input['newDcvMethods']))
            {
                $newDcvMethodArray = array();
                foreach($input['newDcvMethods'] as $domain => $method)
                {
                    if(strpos($domain, '___') !== FALSE)
                    {
                        $domain = str_replace('___', '*', $domain);
                    }
                    $newDcvMethodArray[$domain] = $method;
                }

                $input['newDcvMethods']= $newDcvMethodArray;
            }
            
            foreach ($input['newDcvMethods'] as $domain => $newMethod) {
            
                $newdomains = [];
                $new_methods = [];

                foreach ($input['newdomains'] as $newd)
                {
                    $newdomains[] = str_replace('___', '*', $newd);
                    $new_methods[] = $newMethod;
                }

                $data = [
                    'new_methods'      => implode(',', $new_methods),
                    'domains'          => implode(',', $newdomains)
                ];


                try
                {
                    $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeDomainValidationMethod($sslService->remoteid, $data);
                } catch (\Exception $ex) {
                    if(strpos($ex->getMessage(), 'Function is locked for') !== false ) {
                        if(strpos($domain, '___') !== FALSE)
                        {
                            $domain = str_replace('___', '*', $domain);
                        }
                       $message = substr($ex->getMessage(), 0, -1) . ' for the domain: ' . $domain . '.';
                    } else {
                        $message = $domain.': '.$ex->getMessage();
                    }

                    return array(
                        'success' => 0,
                        'msg'     => $message
                    );
                }
            }

            $sslorder = (array)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->first();

            $sslorderconfigdata = json_decode($sslorder['configdata'], true);

            $sslorderconfigdata['dcv_method'] = $data['new_method'];

            if($data['new_method'] != 'email')
            {
                $sslorderconfigdata['approveremail'] = '';
            }

            Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(array(
                'configdata' => json_encode($sslorderconfigdata)
            ));

            return array(
                'success' => $response['success'],
                'msg'     => $response['message']
            );
        }
        else
        {
            $new_methods = [];
            $newdomains = [];
            
            foreach($input['newDcvMethods'] as $method)
            {
                $new_methods[] = $method;
            }
            
            foreach($input['newdomains'] as $newdomain)
            {
                $newdomains[] = str_replace('___', '*', $newdomain);
            }
            $data = [
                'new_methods'      => implode(',', $new_methods),
                'domains'          => implode(',', $newdomains)
            ];
            
            try
            {
                $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeDomainValidationMethod($sslService->remoteid, $data);
            } catch (\Exception $ex) {
                if(strpos($ex->getMessage(), 'Function is locked for') !== false ) {
                    if(strpos($domain, '___') !== FALSE)
                    {
                        $domain = str_replace('___', '*', $domain);
                    }
                   $message = substr($ex->getMessage(), 0, -1) . ' for the domain: ' . $domain . '.';
                } else {
                    $message = $domain.': '.$ex->getMessage();
                }

                return array(
                    'success' => 0,
                    'msg'     => $message
                );
            }

            $sslorder = (array)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->first();

            $sslorderconfigdata = json_decode($sslorder['configdata'], true);

            $sslorderconfigdata['dcv_method'] = $data['new_method'];

            if($data['new_method'] != 'email')
            {
                $sslorderconfigdata['approveremail'] = '';
            }

            Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(array(
                'configdata' => json_encode($sslorderconfigdata)
            ));

            return array(
                'success' => $response['success'],
                'msg'     => $response['message']
            );
        }
        
        
  
    }

    public function getApprovalEmailsForDomainJSON($input, $vars = array()) {

        $domainEmails = [];

        if($input['brand'] == 'geotrust') {
            $apiDomainEmails             = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmailsForGeotrust($input['domain']);
            $domainEmails = $apiDomainEmails['GeotrustApprovalEmails'];
        } else {
            $apiDomainEmails             = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($input['domain']);
            $domainEmails = $apiDomainEmails['ComodoApprovalEmails'];
        }
        $result = [
            'success' => 1,
            'domainEmails' => $domainEmails
        ];

        return $result;
    }

    function changeApproverEmailJSON($input, $vars = array()) {

        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $ssService = $sslRepo->getByServiceId($input['serviceId']);

        $data = [
            'approver_email' => $input['newEmail']
        ];

        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeValidationEmail($ssService->remoteid, $data);

        $ssService->setConfigdataKey("approveremail", $data['approver_email']);
        $ssService->save();

        return array(
            'success' => $response['success'],
            'msg'     => $response['success_message']
        );
    }

    function getPrivateKeyJSON($input, $vars = array()) {
        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);
        $privateKey = $sslService->getPrivateKey();

        if($privateKey = $sslService->getPrivateKey()) {

            if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
                $privateKey =  decrypt($privateKey);
            }

            $result = array(
                'success'     => 1,
                'privateKey'  => $privateKey
            );
        } else {
            $result = array(
                'success'   => 0,
                'message'   => main\mgLibs\Lang::absoluteT('Can not get Private Key, please refresh page or contact support')
            );
        }

        return $result;
    }

    function installCertificateJSON($input, $vars = array()) {

        $logsRepo = new LogsRepo();
        $orderRepo = new OrderRepo();
        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);

        $details = (array)$sslService->configdata;
        $cert = $details['crt'];
        $cabundle = $details['ca'];
        $key = decrypt($details['private_key']);

        try {

            $service = new Service();
            $serviceCpanel = $service->getServiceByDomain($sslService->userid, $details['domain']);
            if($serviceCpanel !== false) {
                $cpanel = new Cpanel();
                $cpanel->setService($serviceCpanel);
                $cpanel->installSSL($serviceCpanel->user, $details['domain'], $cert, $key, $cabundle);
                $logsRepo->addLog($sslService->userid, $sslService->serviceid, 'success', 'The certificate for the ' . $details['domain'] . ' domain has been installed correctly.');
                $orderRepo->updateStatus($sslService->serviceid, 'Success');
            }

        } catch (\Exception $e) {
            $logsRepo->addLog($sslService->userid, $sslService->serviceid, 'error', '['.$details['domain'].'] Error: '.$e->getMessage());
            return ['success' => 0, 'message' => $e->getMessage()];
        }
        return ['success' => 1, 'message' => main\mgLibs\Lang::absoluteT('The certificate has been installed correctly')];
    }

    function revalidateNewJSON($input, $vars = array()) {

        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($input['id']);

        $data = [
            'domain' => $input['params']['domain']
        ];

        $response = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->revalidate($sslService->remoteid, $data);

        return $response;
    }

    function getCertificateDetailsJSON($input, $vars = array()) {

        $clientCheckCertificateDetails = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\ClientRecheckCertificateDetails($input);
        $details = $clientCheckCertificateDetails->run();
    }

    function getPasswordJSON($input, $vars = array()) {
        //do something with input
        unset($input);
        unset($vars);

        return array(
            'password' => 'fuNPassword'
        );

    }

}
