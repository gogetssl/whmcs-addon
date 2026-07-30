<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits;

use MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule;
use MGModule\SSLCENTERWHMCS\eModels\cpanelservices\Service;
use MGModule\SSLCENTERWHMCS\eHelpers\Cpanel;
use MGModule\SSLCENTERWHMCS\models\orders\Repository as OrderRepo;
use MGModule\SSLCENTERWHMCS\models\logs\Repository as LogsRepo;

trait SslConfigTrait
{
    private function sslConfigurationHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];
        $userId    = (int) $input['params']['userid'];

        $apiProductId = $input['params']['configoption1'];
        $apiRepo      = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $apiProduct   = $apiRepo->getProduct($apiProductId);

        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();

        // Get WHMCS product name
        $whmcsProduct = Capsule::table('tblproducts')
            ->select(['name'])
            ->where('id', (int) $input['params']['pid'])
            ->first();
        $productName = isset($whmcsProduct->name) ? $whmcsProduct->name : 'SSL Certificate';

        // Load ALL cPanel services for this client — domain matching is irrelevant
        $cpanelDomains = null;
        try {
            $cpanelService     = new Service();
            $allCpanelServices = $cpanelService->getCpanelServices($userId);

            if ($allCpanelServices->isNotEmpty()) {
                $merged = ['main' => null, 'addon' => [], 'parked' => [], 'subdomain' => []];
                $seen   = [];

                foreach ($allCpanelServices as $svcInfo) {
                    try {
                        $cpanel  = new Cpanel();
                        $cpanel->setService($svcInfo);
                        $grouped = $cpanel->listDomainsGrouped($svcInfo->user);
                    } catch (\Exception $e) {
                        // This hosting server is unreachable or misconfigured — skip it,
                        // continue collecting domains from the remaining services.
                        continue;
                    }

                    if (!empty($grouped['main']) && !isset($seen[$grouped['main']])) {
                        $seen[$grouped['main']] = true;
                        if (!$merged['main']) {
                            $merged['main'] = $grouped['main'];
                        } else {
                            $merged['addon'][] = $grouped['main'];
                        }
                    }
                    foreach ($grouped['addon'] as $d) {
                        if (!isset($seen[$d])) {
                            $seen[$d]        = true;
                            $merged['addon'][] = $d;
                        }
                    }
                    foreach ($grouped['parked'] as $d) {
                        if (!isset($seen[$d])) {
                            $seen[$d]         = true;
                            $merged['parked'][] = $d;
                        }
                    }
                    foreach ($grouped['subdomain'] as $s) {
                        $subKey = is_array($s) ? $s['domain'] : $s;
                        if (!isset($seen[$subKey])) {
                            $seen[$subKey]        = true;
                            $merged['subdomain'][] = $s;
                        }
                    }
                }

                if ($merged['main'] || !empty($merged['addon']) || !empty($merged['subdomain'])) {
                    $cpanelDomains = $merged;
                }
            }
        } catch (\Exception $e) {
            // getCpanelServices itself failed — manual enrollment only
        }

        // Build optgroup structure: each parent domain is a group with itself + subdomains
        $domainGroups = [];
        if ($cpanelDomains !== null) {
            $parents = [];
            if (!empty($cpanelDomains['main'])) {
                $parents[] = $cpanelDomains['main'];
            }
            foreach ($cpanelDomains['addon'] as $d) {
                $parents[] = $d;
            }
            foreach ($parents as $parent) {
                $group = ['label' => $parent, 'domains' => [$parent]];
                foreach ($cpanelDomains['subdomain'] as $sub) {
                    if ($sub['parent'] === $parent) {
                        $group['domains'][] = $sub['domain'];
                    }
                }
                $domainGroups[] = $group;
            }
        }

        // Countries list with default first
        $countries     = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountriesForMgAddonDropdown();
        $defaultCountry = $apiConf->default_csr_generator_country;
        if ($defaultCountry && isset($countries[$defaultCountry])) {
            $label = $countries[$defaultCountry];
            unset($countries[$defaultCountry]);
            $countries = array_merge([$defaultCountry => $label], $countries);
        }

        $client = Capsule::table('tblclients')->where('id', $userId)->first();

        // CSR pre-fill — when profile_data_csr is enabled, use client's WHMCS profile data
        $csrDefaultCountry = $defaultCountry;
        $csrDefaultState   = '';
        $csrDefaultCity    = '';
        $csrDefaultOrg     = '';
        $csrDefaultEmail   = '';
        if ($apiConf->profile_data_csr && $client) {
            $clientCountry = strtoupper(trim((string) $client->country));
            if ($clientCountry && isset($countries[$clientCountry])) {
                $csrDefaultCountry = $clientCountry;
            }
            $csrDefaultState = (string) $client->state;
            $csrDefaultCity  = (string) $client->city;
            $csrDefaultOrg   = (string) $client->companyname;
            $csrDefaultEmail = (string) $client->email;
        }

        $isWildcard = !empty($apiProduct->wildcard_enabled);

        $dcvMethods = [];
        if ($apiProduct->dcv_email) $dcvMethods[] = 'email';
        if (!$isWildcard && $apiProduct->dcv_http)  $dcvMethods[] = 'http';
        if (!$isWildcard && $apiProduct->dcv_https) $dcvMethods[] = 'https';
        if ($apiProduct->dcv_dns)   $dcvMethods[] = 'dns';
        if (empty($dcvMethods))     $dcvMethods   = $isWildcard ? ['email', 'dns'] : ['email', 'http', 'https', 'dns'];

        $sansEnabled         = $input['params']['configoption3'] === 'on';
        $sansWildcardEnabled = $input['params']['configoption13'] === 'on';
        $includedSans        = (int) $input['params']['configoption4'];
        $includedWildcard    = (int) $input['params']['configoption8'];
        $boughtSans          = isset($input['params']['configoptions']['sans_count']) ? (int) $input['params']['configoptions']['sans_count'] : 0;
        $boughtWildcard      = isset($input['params']['configoptions']['sans_wildcard_count']) ? (int) $input['params']['configoptions']['sans_wildcard_count'] : 0;
        $sansLimit           = $sansEnabled ? ($includedSans + $boughtSans) : 0;
        $wildcardSansLimit   = $sansWildcardEnabled ? ($includedWildcard + $boughtWildcard) : 0;

        $vars['allOk']               = true;
        $vars['serviceid']           = $serviceId;
        $vars['userid']              = $userId;
        $vars['assetsURL']           = main\Server::I()->getAssetsURL();
        $vars['productName']         = $productName;
        $vars['domainGroups']        = $domainGroups;
        $vars['hasCpanel']           = $cpanelDomains !== null;
        $vars['dcvMethods']          = $dcvMethods;
        $vars['emailDcvEnabled']     = in_array('email', $dcvMethods);
        $vars['displayCsrGenerator'] = (bool) $apiConf->display_csr_generator;
        $vars['countries']           = $countries;
        $vars['defaultCountry']      = $defaultCountry;
        $vars['csrDefaultCountry']   = $csrDefaultCountry;
        $vars['csrDefaultState']     = $csrDefaultState;
        $vars['csrDefaultCity']      = $csrDefaultCity;
        $vars['csrDefaultOrg']       = $csrDefaultOrg;
        $vars['csrDefaultEmail']     = $csrDefaultEmail;
        $vars['techFirstname']       = $apiConf->tech_firstname;
        $vars['techLastname']        = $apiConf->tech_lastname;
        $vars['techEmail']           = $apiConf->tech_email;
        $vars['techPhone']           = $apiConf->tech_phone;
        $vars['techOrganization']    = $apiConf->tech_organization;
        $vars['techTitle']           = $apiConf->tech_title;
        $vars['techCity']            = $apiConf->tech_city;
        $vars['techCountry']         = $apiConf->tech_country;
        $vars['techRegion']          = $apiConf->tech_region;
        $vars['techPostalcode']      = $apiConf->tech_postalcode;
        $vars['techAddressline1']    = $apiConf->tech_addressline1;
        $vars['useAdminContact']     = (bool) $apiConf->use_admin_contact;
        $vars['client']              = $client;
        $vars['isMultiDomain']       = $sansLimit > 0 || $wildcardSansLimit > 0;
        $vars['sansLimit']           = $sansLimit;
        $vars['wildcardSansLimit']   = $wildcardSansLimit;
        $vars['isWildcard']          = $isWildcard;
        $vars['certTypeConfig']      = $apiProduct->isOrganizationRequired() ? 'OV' : 'DV';
        $vars['productDomain']       = $input['params']['domain'];
        $vars['isOrganizationRequired'] = $apiProduct->isOrganizationRequired();

        return array(
            'tpl'  => 'home_ssl_configuration',
            'vars' => $vars,
        );
    }

    public function getApproverEmailsJSON($input, $vars = array())
    {
        $domain = isset($input['domain']) ? trim($input['domain']) : '';
        if (empty($domain)) {
            return ['emails' => []];
        }
        try {
            $result = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()
                ->getApi()
                ->getDomainEmails($domain);
            $emails = [];
            if (isset($result['ComodoApprovalEmails']) && is_array($result['ComodoApprovalEmails'])) {
                foreach ($result['ComodoApprovalEmails'] as $email) {
                    if (is_string($email) && !empty($email)) {
                        $emails[] = $email;
                    }
                }
            }
            return ['success' => 1, 'emails' => $emails];
        } catch (\Exception $e) {
            return ['success' => 0, 'emails' => []];
        }
    }

    public function generateCsrJSON($input, $vars = array())
    {
        $post   = (array) $input;
        $params = (array) $input['params'];
        $gen    = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\GenerateCSR($params, $post);
        $result = $gen->run();
        $data   = json_decode($result, true);
        if (isset($data['success']) && $data['success']) {
            return [
                'success'     => $data['msg'],
                'public_key'  => $data['public_key'],
                'private_key' => $data['private_key'],
            ];
        }
        throw new \Exception(isset($data['msg']) ? $data['msg'] : 'CSR generation failed');
    }

    public function submitSslConfigurationJSON($input, $vars = array())
    {
        $serviceId   = (int) $input['params']['serviceid'];
        $userId      = (int) $input['params']['userid'];
        $installType = isset($input['install_type']) ? $input['install_type'] : 'manual';
        $dcvMethod   = isset($input['dcv_method']) ? strtolower(trim($input['dcv_method'])) : 'http';
        $includeWww  = !empty($input['include_www']);
        $orderType   = isset($input['order_type']) ? $input['order_type'] : 'new';

        $ownCsr = ($installType === 'manual') ? trim(isset($input['own_csr']) ? $input['own_csr'] : '') : '';

        $mainDomain = '';
        if ($installType === 'autoinstall') {
            $mainDomain = isset($input['main_domain']) ? strtolower(trim($input['main_domain'])) : '';
        } elseif (!empty($ownCsr)) {
            $csrSubject = @openssl_csr_get_subject($ownCsr);
            if (!$csrSubject || empty($csrSubject['CN'])) {
                throw new \Exception('Invalid CSR: unable to extract domain name. Please check your CSR code.');
            }
            $mainDomain = strtolower(trim($csrSubject['CN']));
        } else {
            $mainDomain = isset($input['manual_domain']) ? strtolower(trim($input['manual_domain'])) : '';
        }
        if (empty($mainDomain)) {
            $mainDomain = strtolower(trim($input['params']['domain']));
        }
        if (empty($mainDomain)) {
            throw new \Exception(main\mgLibs\Lang::absoluteT('anErrorOccurred') . ': domain is required');
        }

        $sanDomainsRaw = isset($input['san_domains']) ? $input['san_domains'] : '';
        $wildcardRaw   = isset($input['wildcard_san']) ? $input['wildcard_san'] : '';

        if (is_array($sanDomainsRaw)) { $sanDomainsRaw = implode("\n", $sanDomainsRaw); }
        if (is_array($wildcardRaw))   { $wildcardRaw   = implode("\n", $wildcardRaw); }

        $isWildcard = strpos($mainDomain, '*.') === 0 || !empty(trim($wildcardRaw));

        $csrCode    = '';
        $privateKey = '';

        if (!empty($ownCsr)) {
            $csrCode = $ownCsr;
        } else {
            $domain = $mainDomain;
            if (!$isWildcard && $installType === 'autoinstall' && $includeWww && strpos($domain, 'www.') !== 0) {
                $domain = 'www.' . $domain;
            }

            $privKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            if (!$privKey || !openssl_pkey_export($privKey, $privKeyOut)) {
                throw new \Exception(main\mgLibs\Lang::absoluteT('csrCodeGeneraterFailed'));
            }
            $dn = array_filter([
                'countryName'            => isset($input['csr_country']) ? strtoupper($input['csr_country']) : 'US',
                'stateOrProvinceName'    => isset($input['csr_state']) ? $input['csr_state'] : '',
                'localityName'           => isset($input['csr_city']) ? $input['csr_city'] : '',
                'organizationName'       => isset($input['csr_org']) ? $input['csr_org'] : $mainDomain,
                'organizationalUnitName' => isset($input['csr_org_unit']) ? $input['csr_org_unit'] : '',
                'commonName'             => $domain,
                'emailAddress'           => isset($input['admin_email']) ? $input['admin_email'] : '',
            ], function ($v) { return $v !== ''; });
            $csr = openssl_csr_new($dn, $privKey, ['digest_alg' => 'sha256']);
            if (!$csr || !openssl_csr_export($csr, $csrCode)) {
                throw new \Exception(main\mgLibs\Lang::absoluteT('csrCodeGeneraterFailed'));
            }
            $privateKey = encrypt($privKeyOut);
        }

        $billingCycle     = isset($input['params']['templatevars']['billingcycle']) ? $input['params']['templatevars']['billingcycle'] : 'Annually';
        $apiProductMonths = (int) $input['params']['configoption2'];
        $monthOneTime     = $input['params']['configoption7'];

        $billingPeriods = [
            'Free Account'  => $apiProductMonths,
            'One Time'      => $apiProductMonths,
            'Monthly'       => 12,
            'Quarterly'     => 3,
            'Semi-Annually' => 6,
            'Annually'      => 12,
            'Biennially'    => 24,
            'Triennially'   => 36,
        ];
        if (!empty($monthOneTime)) {
            $billingPeriods['One Time'] = (int) $monthOneTime;
        }

        $apiProductId = $input['params']['configoption1'];
        $apiRepo      = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $apiProduct   = $apiRepo->getProduct($apiProductId);

        $productPeriods = $apiProduct->getPeriods();
        if (in_array('1', $productPeriods))  { $billingPeriods['Monthly']  = 1; }
        if (in_array('12', $productPeriods)) { $billingPeriods['One Time'] = 12; }

        $period = isset($billingPeriods[$billingCycle]) ? $billingPeriods[$billingCycle] : 12;

        $webserverType = '-1';
        if (in_array($apiProduct->brand, ['geotrust', 'rapidssl', 'digicert', 'thawte'])) {
            $webserverType = '18';
        }

        $order = [];
        $order['product_id']         = $apiProductId;
        $order['period']             = $period;
        $order['csr']                = $csrCode;
        $order['server_count']       = -1;
        $order['dcv_method']         = $dcvMethod;
        $order['webserver_type']     = $webserverType;
        $approverEmailVal            = isset($input['approver_email']) ? trim($input['approver_email']) : '';
        $order['approver_email']     = !empty($approverEmailVal) ? $approverEmailVal : '';
        $order['admin_firstname']    = isset($input['admin_firstname']) ? $input['admin_firstname'] : '';
        $order['admin_lastname']     = isset($input['admin_lastname']) ? $input['admin_lastname'] : '';
        $order['admin_organization'] = isset($input['admin_organization']) ? $input['admin_organization'] : '';
        $order['admin_title']        = isset($input['admin_title']) ? $input['admin_title'] : '';
        $order['admin_addressline1'] = isset($input['admin_addressline1']) ? $input['admin_addressline1'] : '';
        $order['admin_phone']        = isset($input['admin_phone']) ? $input['admin_phone'] : '';
        $order['admin_email']        = isset($input['admin_email']) ? $input['admin_email'] : '';
        $order['admin_city']         = isset($input['admin_city']) ? $input['admin_city'] : '';
        $order['admin_country']      = isset($input['admin_country']) ? $input['admin_country'] : '';
        $order['admin_postalcode']   = isset($input['admin_postalcode']) ? $input['admin_postalcode'] : '';
        $order['admin_region']       = isset($input['admin_region']) ? $input['admin_region'] : '';

        $apiConf     = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $sameAsAdmin = !isset($input['tech_firstname']);

        if ($sameAsAdmin) {
            $order['tech_firstname']    = $order['admin_firstname'];
            $order['tech_lastname']     = $order['admin_lastname'];
            $order['tech_organization'] = $order['admin_organization'];
            $order['tech_addressline1'] = $order['admin_addressline1'];
            $order['tech_phone']        = $order['admin_phone'];
            $order['tech_title']        = $order['admin_title'];
            $order['tech_email']        = $order['admin_email'];
            $order['tech_city']         = $order['admin_city'];
            $order['tech_country']      = $order['admin_country'];
            $order['tech_fax']          = '';
            $order['tech_postalcode']   = $order['admin_postalcode'];
            $order['tech_region']       = $order['admin_region'];
        } else {
            $order['tech_firstname']    = isset($input['tech_firstname']) ? $input['tech_firstname'] : $apiConf->tech_firstname;
            $order['tech_lastname']     = isset($input['tech_lastname']) ? $input['tech_lastname'] : $apiConf->tech_lastname;
            $order['tech_organization'] = isset($input['tech_organization']) ? $input['tech_organization'] : $apiConf->tech_organization;
            $order['tech_addressline1'] = isset($input['tech_addressline1']) ? $input['tech_addressline1'] : $apiConf->tech_addressline1;
            $order['tech_phone']        = isset($input['tech_phone']) ? $input['tech_phone'] : $apiConf->tech_phone;
            $order['tech_title']        = isset($input['tech_title']) ? $input['tech_title'] : $apiConf->tech_title;
            $order['tech_email']        = isset($input['tech_email']) ? $input['tech_email'] : $apiConf->tech_email;
            $order['tech_city']         = isset($input['tech_city']) ? $input['tech_city'] : $apiConf->tech_city;
            $order['tech_country']      = isset($input['tech_country']) ? $input['tech_country'] : $apiConf->tech_country;
            $order['tech_fax']          = $apiConf->tech_fax;
            $order['tech_postalcode']   = isset($input['tech_postalcode']) ? $input['tech_postalcode'] : $apiConf->tech_postalcode;
            $order['tech_region']       = isset($input['tech_region']) ? $input['tech_region'] : $apiConf->tech_region;
        }

        $template = Capsule::table('tblconfiguration')->where('setting', 'Template')->first();
        if (isset($template->value) && $template->value == 'twenty-one') {
            $countriesHelper        = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance();
            $order['admin_country'] = $countriesHelper->getCountryCodeByName($order['admin_country']);
            $order['tech_country']  = $countriesHelper->getCountryCodeByName($order['tech_country']);
        }

        $sanEnabledForProduct = $input['params']['configoption3'] === 'on';
        $boughtSans           = isset($input['params']['configoptions']['sans_count']) ? (int) $input['params']['configoptions']['sans_count'] : 0;
        $includedSans         = (int) $input['params']['configoption4'];
        $includedWildcard     = (int) $input['params']['configoption8'];
        $boughtWildcard       = isset($input['params']['configoptions']['sans_wildcard_count']) ? (int) $input['params']['configoptions']['sans_wildcard_count'] : 0;
        $sansLimit            = $sanEnabledForProduct ? ($includedSans + $boughtSans) : 0;
        $wildcardLimit        = ($input['params']['configoption13'] === 'on') ? ($includedWildcard + $boughtWildcard) : 0;

        $sanDomains      = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($sanDomainsRaw);
        $wildcardDomains = \MGModule\SSLCENTERWHMCS\eHelpers\SansDomains::parseDomains($wildcardRaw);
        $allSans         = array_merge($sanDomains, $wildcardDomains);

        if ($sanEnabledForProduct && !empty($allSans)) {
            $order['dns_names'] = implode(',', $allSans);
            $approverEmails = [];
            foreach ($allSans as $sanDomain) {
                $approverEmails[] = ($dcvMethod === 'email') ? $order['approver_email'] : $dcvMethod;
            }
            $order['approver_emails'] = implode(',', $approverEmails);
        }

        $addedSSLOrder = $orderType === 'renew'
            ? \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLRenewOrder($order)
            : \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSSLOrder($order);

        $tlsItemId = \MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterApi::extractItemId($addedSSLOrder);

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domain' => $mainDomain]);

        $sslRepo   = new main\eRepository\whmcs\service\SSL();
        $sslConfig = $sslRepo->getByServiceId($serviceId);

        if (is_null($sslConfig)) {
            throw new \Exception(main\mgLibs\Lang::absoluteT('createNotInitialized'));
        }

        $orderDetails = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($addedSSLOrder['order_id']);

        $sslConfig->setRemoteId($addedSSLOrder['order_id']);
        $sslConfig->setApproverEmails(isset($order['approver_emails']) ? $order['approver_emails'] : $order['approver_email']);
        $sslConfig->setCa($orderDetails['ca_code']);
        $sslConfig->setCrt($orderDetails['crt_code']);
        $sslConfig->setPartnerOrderId($orderDetails['partner_order_id']);
        $sslConfig->setValidFrom($orderDetails['valid_from']);
        $sslConfig->setValidTill($orderDetails['valid_till']);
        $sslConfig->setDomain($orderDetails['domain']);
        $sslConfig->setOrderStatusDescription($orderDetails['status_description']);
        $sslConfig->setApproverMethod($orderDetails['approver_method']);
        $sslConfig->setDcvMethod($orderDetails['dcv_method']);
        $sslConfig->setProductId($orderDetails['product_id']);
        $sslConfig->setSanDetails($orderDetails['san']);
        $sslConfig->setSSLStatus($orderDetails['status']);
        $sslConfig->setStatus('Completed');
        if (!empty($privateKey)) {
            $sslConfig->setConfigdataKey('private_key', $privateKey);
        }
        if (!empty($csrCode)) {
            $sslConfig->setCsr($csrCode);
        }
        $sslConfig->setConfigdataKey('install_type', $installType);
        $sslConfig->setConfigdataKey('is_wildcard', $isWildcard ? 1 : 0);
        $sslConfig->setConfigdataKey('configssl', 1);
        if ($tlsItemId > 0) {
            $sslConfig->setConfigdataKey('tls_item_id', $tlsItemId);
        }
        $sslConfig->save();

        $sslOrderRow = Capsule::table('tblsslorders')->where('serviceid', $serviceId)->first();
        $sslOrderId  = $sslOrderRow ? $sslOrderRow->id : 0;

        $orderRepo = new OrderRepo();
        $orderRepo->addOrder(
            $userId,
            $serviceId,
            $sslOrderId,
            $orderDetails['dcv_method'],
            'Pending Verification',
            $addedSSLOrder
        );

        if ($installType === 'autoinstall') {
            $cronQueueRepo = new \MGModule\SSLCENTERWHMCS\models\cronQueue\Repository();
            $validateTaskData = [
                'domain'       => $mainDomain,
                'install_type' => 'autoinstall',
                'api_order_id' => $addedSSLOrder['order_id'],
                'is_wildcard'  => $isWildcard,
            ];
            if ($tlsItemId > 0) {
                $validateTaskData['tls_item_id'] = $tlsItemId;
            }
            if ($isWildcard && $dcvMethod === 'dns') {
                $validateTaskData['dns_check_start'] = time();
            }
            if ($dcvMethod === 'dns') {
                $dnsFallbackEmail = isset($input['dns_fallback_email']) ? trim($input['dns_fallback_email']) : '';
                if (!empty($dnsFallbackEmail)) {
                    $validateTaskData['dns_fallback_email'] = $dnsFallbackEmail;
                }
            }
            $cronQueueRepo->addTask($userId, $serviceId, 'validate', $validateTaskData);
        }

        $logsRepo = new LogsRepo();
        $logsRepo->addLog($userId, $serviceId, 'success', 'SSL order placed for domain: ' . $mainDomain);

        run_hook('SSLCenter_StepThree', [
            'serviceid'     => $serviceId,
            'userid'        => $userId,
            'api_ssl_order' => $addedSSLOrder,
            'order_details' => $orderDetails,
        ]);

        $dcvApproverEmail = '';
        if (is_string($orderDetails['approver_method'])) {
            $dcvApproverEmail = $orderDetails['approver_method'];
        } elseif (isset($orderDetails['approver_method']['email'])) {
            $dcvApproverEmail = $orderDetails['approver_method']['email'];
        }

        return [
            'success'        => 1,
            'approver_email' => $dcvApproverEmail,
        ];
    }
}
