<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea;

use MGModule\SSLCENTERWHMCS as main;
use WHMCS\Database\Capsule;
use MGModule\SSLCENTERWHMCS\eModels\cpanelservices\Service;
use MGModule\SSLCENTERWHMCS\eHelpers\Cpanel;
use MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription as AcmeHelper;
use MGModule\SSLCENTERWHMCS\eHelpers\AisSubscription as AisHelper;
use MGModule\SSLCENTERWHMCS\models\orders\Repository as OrderRepo;
use MGModule\SSLCENTERWHMCS\models\logs\Repository as LogsRepo;
use MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits\AcmeTrait;
use MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits\AisTrait;
use MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits\StandardSslTrait;
use MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits\SslConfigTrait;

/**
 * @author Michal Czech <michael@modulesgarden.com>
 */
class home extends main\mgLibs\process\AbstractController {

    use AcmeTrait, AisTrait, StandardSslTrait, SslConfigTrait;

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

            if (AisHelper::isAisByServiceParams($input['params']))
            {
                if (isset($_GET['aisconfig']) && (string) $_GET['aisconfig'] === '1')
                {
                    return $this->aisConfigurationHTML($input, $vars);
                }
                return $this->indexAisHTML($input, $vars);
            }

            if (isset($_GET['configssl']) && (string) $_GET['configssl'] === '1')
            {
                return $this->sslConfigurationHTML($input, $vars);
            }

            $disabledValidationMethods = [];

            $serviceId  = $input['params']['serviceid'];
            $serviceBillingCycle = $input['params']['templatevars']['billingcycle'];
            $userid = $input['params']['userid'];
            $ssl        = new main\eRepository\whmcs\service\SSL();
            $sslService = $ssl->getByServiceId($serviceId);

            if (isset($_GET['refreshapi']) && (string) $_GET['refreshapi'] === '1' && !empty($sslService->remoteid)) {
                try {
                    $apicertdata = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()
                        ->getApi()->getOrderStatus($sslService->remoteid);
                    $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService, $apicertdata);
                    $configDataUpdate->run();

                    $contactFields = [
                        'admin_firstname', 'admin_lastname', 'admin_email', 'admin_phone', 'admin_organization', 'admin_title',
                        'tech_firstname',  'tech_lastname',  'tech_email',  'tech_phone',  'tech_organization',  'tech_title',
                    ];
                    $hasContact = false;
                    foreach ($contactFields as $field) {
                        if (!empty($apicertdata[$field])) {
                            $sslService->setConfigdataKey($field, $apicertdata[$field]);
                            $hasContact = true;
                        }
                    }
                    if ($hasContact) {
                        $sslService->save();
                    }
                } catch (\Exception $e) {}
                header('Location: ' . $CONFIG['SystemURL'] . '/clientarea.php?action=productdetails&id=' . $serviceId . '&synced=1');
                exit;
            }

            $vars['syncSuccess'] = isset($_GET['synced']) && (string) $_GET['synced'] === '1';

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

                $vars['activationStatus'] = $apicertdata['status'];
            }

            $vars['brandsWithOnlyEmailValidation'] = ['geotrust','thawte','rapidssl','symantec',];

            if(is_null($sslService)) {
                throw new \Exception(main\mgLibs\Lang::absoluteT('anErrorOccurredPleaseContactSupport'));
            }

            $url = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Config::getInstance()->getConfigureSSLUrl($sslService->id, $serviceId);

            $vars['privateKey'] = '';
            $privateKey = $sslService->getPrivateKey();
            if ($privateKey) {
                $vars['privateKey'] = (strpos($privateKey, '-----BEGIN') !== false)
                    ? $privateKey
                    : decrypt($privateKey);
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
                                $rawContent = isset($vars['approver_method'][$vars['dcv_method']]['content'])
                                    ? $vars['approver_method'][$vars['dcv_method']]['content']
                                    : '';
                                $vars['approver_method'][$vars['dcv_method']]['content'] = is_array($rawContent)
                                    ? $rawContent
                                    : explode(PHP_EOL, (string) $rawContent);
                            }

                            if ($vars['dcv_method'] === 'dns' && isset($vars['approver_method']['dns']['record'])) {
                                $record = trim($vars['approver_method']['dns']['record']);
                                $parts  = preg_split('/\s+/', $record, 3);
                                $vars['approver_method']['dns']['host']    = isset($parts[0]) ? $parts[0] : $record;
                                $vars['approver_method']['dns']['type']    = isset($parts[1]) ? strtoupper($parts[1]) : 'CNAME';
                                $vars['approver_method']['dns']['content'] = isset($parts[2]) ? $parts[2] : '';
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
                                    $rawSanContent = isset($san->validation->http->content) ? $san->validation->http->content : '';
                                    $vars['sans'][$san->san_name]['san_validation']['content'] = is_array($rawSanContent)
                                        ? $rawSanContent
                                        : explode(PHP_EOL, (string) $rawSanContent);
                                    break;
                                case 'https':
                                    $vars['san_revalidate'] = true;
                                    $vars['sans'][$san->san_name]['san_validation'] = (array)$san->validation->https;
                                    $rawSanContent = isset($san->validation->https->content) ? $san->validation->https->content : '';
                                    $vars['sans'][$san->san_name]['san_validation']['content'] = is_array($rawSanContent)
                                        ? $rawSanContent
                                        : explode(PHP_EOL, (string) $rawSanContent);
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
                    $vars['validFrom'] = fromMySQLDate($certificateDetails['valid_from'], false, true);
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

                    $adminName = trim(($certificateDetails['admin_firstname'] ?? '') . ' ' . ($certificateDetails['admin_lastname'] ?? ''));
                    $techName  = trim(($certificateDetails['tech_firstname'] ?? '') . ' ' . ($certificateDetails['tech_lastname'] ?? ''));
                    if ($adminName || !empty($certificateDetails['admin_email'])) {
                        $vars['adminContact'] = [
                            'name'         => $adminName ?: null,
                            'email'        => $certificateDetails['admin_email'] ?? '',
                            'phone'        => $certificateDetails['admin_phone'] ?? '',
                            'organization' => $certificateDetails['admin_organization'] ?? '',
                        ];
                    }
                    if ($techName || !empty($certificateDetails['tech_email'])) {
                        $vars['techContact'] = [
                            'name'         => $techName ?: null,
                            'email'        => $certificateDetails['tech_email'] ?? '',
                            'phone'        => $certificateDetails['tech_phone'] ?? '',
                            'organization' => $certificateDetails['tech_organization'] ?? '',
                        ];
                    }

                    $vars['serviceBillingCycle'] = $serviceBillingCycle;
                    $vars['displayRenewButton'] = false;
                    $today = date('Y-m-d');
                    $diffDays = abs(strtotime($certificateDetails['end_date']) - strtotime($today))/86400;

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
            $filenameCa  = isset($vars['domain']) && !empty($vars['domain']) ? $vars['domain'] : 'ca_code';

            if($_GET['download'] == '1')
            {
                if(isset($vars['sans'][$_GET['domain']]) && !empty($vars['sans'][$_GET['domain']]) && ($vars['sans'][$_GET['domain']]['method'] == 'http' || $vars['sans'][$_GET['domain']]['method'] == 'https'))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['sans'][$_GET['domain']]['san_validation']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    $dlContent = $vars['sans'][$_GET['domain']]['san_validation']['content'];
                    print is_array($dlContent) ? implode(PHP_EOL, $dlContent) : (string) $dlContent;
                    exit;
                }

                if(isset($vars['approver_method']['https']) && !empty($vars['approver_method']['https']))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['approver_method']['https']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    $dlContent = $vars['approver_method']['https']['content'];
                    print is_array($dlContent) ? implode(PHP_EOL, $dlContent) : (string) $dlContent;
                    exit;
                }

                if(isset($vars['approver_method']['http']) && !empty($vars['approver_method']['http']))
                {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($vars['approver_method']['http']['filename']));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    $dlContent = $vars['approver_method']['http']['content'];
                    print is_array($dlContent) ? implode(PHP_EOL, $dlContent) : (string) $dlContent;
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

                $sslRepo    = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
                $sslService = $sslRepo->getByServiceId($input['params']['serviceid']);
                $privateKey = $sslService->getPrivateKey();

                if (!empty($privateKey))
                {
                    $pemfile .= (strpos($privateKey, '-----BEGIN') !== false)
                        ? $privateKey
                        : decrypt($privateKey);
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
        $vars['installType']  = isset($sslService->configdata->install_type) ? (string) $sslService->configdata->install_type : 'manual';

        $revalidateState            = $this->getRevalidateState($input['params'], isset($sslService) ? $sslService : null, isset($vars['activationStatus']) ? $vars['activationStatus'] : '');
        $vars['canRevalidate']      = $revalidateState['allowed'];
        $vars['revalidateCooldown'] = $revalidateState['cooldown'];

        // Modern view: certificates configured via configssl (have a record in SSLCENTER_orders)
        $moduleOrderRow = null;
        try {
            $orderRepo      = new OrderRepo();
            $moduleOrderRow = Capsule::table($orderRepo->tableName)
                ->where('service_id', $serviceId)
                ->orderBy('date', 'desc')
                ->first();
        } catch (\Exception $e) {}

        if ($moduleOrderRow !== null) {
            $activationStatus = strtolower((string) ($vars['activationStatus'] ?? ''));
            $configStatus     = isset($sslService) ? (string) $sslService->status : '';

            if ($configStatus === 'Awaiting Configuration') {
                $certState = 'awaiting_config';
            } elseif (in_array($activationStatus, ['cancelled', 'revoked', 'refunded'])) {
                $certState = 'cancelled';
            } elseif ($activationStatus === 'reissue') {
                $certState = 'reissue';
            } elseif ($activationStatus === 'active') {
                $validTillTs = isset($certificateDetails['valid_till']) ? strtotime((string) $certificateDetails['valid_till']) : 0;
                if ($validTillTs && $validTillTs < time()) {
                    $certState = 'expired';
                } elseif (isset($moduleOrderRow->status) && $moduleOrderRow->status === 'Success') {
                    $certState = 'active_installed';
                } else {
                    $certState = 'active';
                }
            } else {
                $certState = 'processing';
            }

            // Parse certificate for serial and organization
            $certSerial = null;
            $certOrg    = null;
            $certOrgUnit = null;
            $certAddress = null;
            if (!empty($vars['crt'])) {
                $parsed = @openssl_x509_parse($vars['crt']);
                if ($parsed) {
                    if (isset($parsed['serialNumberHex'])) {
                        $certSerial = strtoupper((string) $parsed['serialNumberHex']);
                    }
                    if (isset($parsed['subject'])) {
                        $certOrg     = isset($parsed['subject']['O'])  ? $parsed['subject']['O']  : null;
                        $certOrgUnit = isset($parsed['subject']['OU']) ? $parsed['subject']['OU'] : null;
                        if (isset($parsed['subject']['L']) || isset($parsed['subject']['ST']) || isset($parsed['subject']['C'])) {
                            $certAddress = implode(', ', array_filter([
                                isset($parsed['subject']['L'])  ? $parsed['subject']['L']  : null,
                                isset($parsed['subject']['ST']) ? $parsed['subject']['ST'] : null,
                                isset($parsed['subject']['C'])  ? $parsed['subject']['C']  : null,
                            ]));
                        }
                    }
                }
            }

            // Type: DV / OV / EV (derive from product brand or domain structure)
            $certType = 'DV';
            if ($certOrg) {
                $certType = 'OV';
            }

            // Service logs + cron tasks
            $serviceLogs  = [];
            $cronTasks    = [];
            try {
                $logsRepo = new LogsRepo();
                $rawLogs  = Capsule::table($logsRepo->tableName)
                    ->where('service_id', $serviceId)
                    ->orderBy('date', 'desc')
                    ->limit(30)
                    ->get();

                $cronRepo     = new \MGModule\SSLCENTERWHMCS\models\cronQueue\Repository();
                $rawCronTasks = Capsule::table($cronRepo->tableName)
                    ->where('service_id', $serviceId)
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                // Build enriched cron task list and track which types are active
                $activeCronTypes = [];
                foreach ($rawCronTasks as $task) {
                    $td     = json_decode($task->data, true) ?: [];
                    $method = isset($td['dcv_method']) ? strtoupper($td['dcv_method']) : null;
                    $d      = $task->domain ? '<strong>' . htmlspecialchars($task->domain) . '</strong>' : 'your domain';
                    $m      = $method ? ' via <strong>' . htmlspecialchars($method) . '</strong> validation' : '';

                    switch ($task->type) {
                        case 'validate':
                            $msgs = [
                                'pending'    => "Domain ownership verification for {$d}{$m} has been scheduled.",
                                'processing' => "Domain ownership for {$d}{$m} is currently being verified.",
                                'done'       => "Domain ownership for {$d}{$m} has been verified successfully.",
                                'error'      => "Domain verification for {$d}{$m} requires attention — please check your validation settings.",
                            ];
                            break;
                        case 'install':
                            $msgs = [
                                'pending'    => "SSL certificate installation for {$d} has been scheduled.",
                                'processing' => "Your SSL certificate for {$d} is currently being installed on your server.",
                                'done'       => "Your SSL certificate for {$d} has been installed on your server successfully.",
                                'error'      => "SSL certificate installation for {$d} encountered an issue — please contact our support team.",
                            ];
                            break;
                        case 'renew':
                            $msgs = [
                                'pending'    => "Certificate renewal for {$d} has been scheduled.",
                                'processing' => "Your SSL certificate for {$d} is currently being renewed.",
                                'done'       => "Your SSL certificate for {$d} has been renewed successfully.",
                                'error'      => "Certificate renewal for {$d} encountered an issue — please contact our support team.",
                            ];
                            break;
                        case 'reissue':
                            $msgs = [
                                'pending'    => "Automatic certificate reissue for {$d} has been scheduled.",
                                'processing' => "Your SSL certificate for {$d} is currently being reissued automatically.",
                                'done'       => "Your SSL certificate for {$d} has been reissued automatically.",
                                'error'      => "Automatic reissue for {$d} encountered an issue — please contact our support team.",
                            ];
                            break;
                        default:
                            $msgs = [
                                'pending'    => "A task for {$d} has been scheduled.",
                                'processing' => "A task for {$d} is in progress.",
                                'done'       => "A task for {$d} has been completed.",
                                'error'      => "A task for {$d} encountered an issue.",
                            ];
                    }

                    $statusKey = isset($msgs[$task->status]) ? $task->status : 'pending';

                    if (in_array($task->status, ['pending', 'processing'])) {
                        $activeCronTypes[] = $task->type;
                    }

                    $cronTasks[] = (object)[
                        'type'       => $task->type,
                        'status'     => $task->status,
                        'msg'        => $msgs[$statusKey],
                        'created_at' => $task->created_at,
                    ];
                }

                // Map log prefixes to cron task types for deduplication
                $prefixToCronType = [
                    '[Install]'     => 'install',
                    '[Validate]'    => 'validate',
                    '[Renew]'       => 'renew',
                    '[AutoReissue]' => 'reissue',
                    '[Reissue]'     => 'reissue',
                ];

                // Message templates with {domain}, {method}, {expiry} placeholders
                $logTemplates = [
                    '[Install]' => [
                        'success' => 'Your SSL certificate for {domain} has been successfully installed on your server.',
                        'error'   => 'SSL certificate installation for {domain} encountered an issue. Please contact our support team.',
                        'info'    => 'SSL certificate installation for {domain} is currently in progress.',
                    ],
                    '[Validate]' => [
                        'success' => 'Domain ownership verification for {domain}{method} completed successfully.',
                        'error'   => 'Domain verification for {domain}{method} encountered an issue. Please check your validation settings and try again.',
                        'info'    => 'Domain ownership verification for {domain}{method} is in progress.',
                    ],
                    '[Renew]' => [
                        'success' => 'Your SSL certificate for {domain} has been renewed successfully.',
                        'error'   => 'Certificate renewal for {domain} encountered an issue. Please contact our support team.',
                        'info'    => 'Certificate renewal for {domain} is currently being processed.',
                    ],
                    '[AutoReissue]' => [
                        'success' => 'Your SSL certificate for {domain} has been reissued automatically.',
                        'error'   => 'Automatic reissue for {domain} encountered an error. Please contact our support team.',
                        'info'    => 'Your certificate for {domain} is expiring{expiry} — an automatic reissue has been scheduled.',
                    ],
                    '[Reissue]' => [
                        'success' => 'Your SSL certificate for {domain} has been reissued successfully.',
                        'error'   => 'Certificate reissue for {domain} encountered an issue. Please contact our support team.',
                        'info'    => 'Certificate reissue for {domain} is in progress.',
                    ],
                    '[ActiveCheck]' => [
                        'success' => 'Your SSL certificate for {domain} is active and valid.',
                        'error'   => 'Your SSL certificate for {domain} requires immediate attention. Please contact our support team.',
                        'info'    => 'Certificate status for {domain} has been updated.',
                    ],
                    '[Sync]' => [
                        'success' => 'Certificate data for {domain} has been synchronized with the Certificate Authority.',
                        'error'   => 'Data synchronization for {domain} encountered an issue.',
                        'info'    => 'Certificate data for {domain} has been refreshed.',
                    ],
                ];

                foreach ($rawLogs as $log) {
                    $raw = (string) $log->msg;

                    // Extract domain
                    $domain = null;
                    if (preg_match('/(?:domain[:\s]+|for domain[:\s]+|for\s+)(\*?[a-zA-Z0-9](?:[a-zA-Z0-9\-]*[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9\-]*[a-zA-Z0-9])?)+)/i', $raw, $m)) {
                        $domain = '<strong>' . htmlspecialchars($m[1]) . '</strong>';
                    }

                    // Extract validation method
                    $method = null;
                    if (preg_match('/\b(DNS|HTTP|HTTPS|email)\b(?:\s+validation)?/i', $raw, $m)) {
                        $method = ' via <strong>' . htmlspecialchars(strtoupper($m[1])) . '</strong> validation';
                    }

                    // Extract expiry date
                    $expiry = null;
                    if (preg_match('/(?:expires?|expiry)[:\s]+(\d{4}-\d{2}-\d{2})/i', $raw, $m)) {
                        $expiry = ' on <strong>' . htmlspecialchars($m[1]) . '</strong>';
                    }

                    $friendly       = null;
                    $matchedCronType = null;

                    foreach ($logTemplates as $prefix => $typeMap) {
                        if (strpos($raw, $prefix) === 0) {
                            $matchedCronType = $prefixToCronType[$prefix] ?? null;

                            // Skip log if an active (pending/processing) cron task of the same type exists
                            if ($matchedCronType && in_array($matchedCronType, $activeCronTypes)) {
                                break;
                            }

                            $tplType  = isset($typeMap[$log->type]) ? $log->type : 'info';
                            $friendly = $typeMap[$tplType];
                            $friendly = str_replace('{domain}', $domain ?? 'your certificate', $friendly);
                            $friendly = str_replace('{method}', $method ?? '', $friendly);
                            $friendly = str_replace('{expiry}', $expiry ?? '', $friendly);
                            break;
                        }
                    }

                    // Skip entry if it would duplicate an active cron task
                    if ($friendly === null && $matchedCronType && in_array($matchedCronType, $activeCronTypes)) {
                        continue;
                    }

                    if ($friendly === null) {
                        $stripped = preg_replace('/^\[[^\]]+\]\s*/', '', $raw);
                        $friendly = htmlspecialchars(trim(preg_split('/\s+[—|]\s+/', $stripped, 2)[0]));
                    }

                    $serviceLogs[] = (object)[
                        'type' => $log->type,
                        'msg'  => $friendly,
                        'date' => $log->date,
                    ];
                }
            } catch (\Exception $e) {}

            $showValidationWarning = false;
            foreach ($cronTasks as $cronTask) {
                if ($cronTask->type === 'validate' && in_array($cronTask->status, ['pending', 'processing', 'error'])) {
                    $rawCronRow = null;
                    foreach ($rawCronTasks as $r) {
                        if ($r->type === 'validate' && $r->status === $cronTask->status) { $rawCronRow = $r; break; }
                    }
                    if ($rawCronRow) {
                        $td = json_decode($rawCronRow->data, true) ?: [];
                        if (!empty($td['files_uploaded']) &&
                            (!empty($td['validation_warning_sent']) || (isset($td['post_upload_checks']) && (int)$td['post_upload_checks'] >= 5))
                        ) {
                            $showValidationWarning = true;
                            break;
                        }
                    }
                }
            }

            $vars['certState']        = $certState;
            $vars['certSerial']       = $certSerial;
            $vars['certOrg']          = $certOrg;
            $vars['certOrgUnit']      = $certOrgUnit;
            $vars['certAddress']      = $certAddress;
            $vars['certType']         = $certType;
            $vars['certIsWildcard']   = isset($vars['domain']) && strpos($vars['domain'], '*.') === 0;
            $vars['purchasedDate']    = isset($moduleOrderRow->date) ? date('M j, Y', strtotime((string) $moduleOrderRow->date)) : '';
            $vars['sslOrderApiId']    = isset($sslService) ? $sslService->remoteid : '';
            $vars['sslOrderLocalId']  = isset($sslService) ? $sslService->id : '';
            $vars['serviceLogs']            = $serviceLogs;
            $vars['cronTasks']              = $cronTasks;
            $vars['certIssued']             = in_array($certState, ['active', 'active_installed', 'expired', 'cancelled', 'reissue']);
            $vars['showValidationWarning']  = $showValidationWarning;

            return array(
                'tpl'  => 'home_ssl_modern',
                'vars' => $vars,
            );
        }

        return array(
            'tpl'  => 'home',
            'vars' => $vars,
        );
    }

    function testHTML($input, $vars = array()) {
        return array(
            'tpl'  => 'test',
            'vars' => $vars,
        );
    }
}
