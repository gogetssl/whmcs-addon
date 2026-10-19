<?php

use MGModule\SSLCENTERWHMCS\eModels\cpanelservices\Service;
use WHMCS\Database\Capsule as DB;
use MGModule\SSLCENTERWHMCS\eHelpers\Cpanel;
use MGModule\SSLCENTERWHMCS\models\cronQueue\Repository as CronQueueRepo;
use MGModule\SSLCENTERWHMCS\models\orders\Repository as OrderRepo;
use MGModule\SSLCENTERWHMCS\models\logs\Repository as LogsRepo;
use MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService;

define('DS', DIRECTORY_SEPARATOR);
define('WHMCS_MAIN_DIR', substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), 'modules' . DS . 'addons')));
define('ADDON_DIR', substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), DS . 'cron')));

require_once WHMCS_MAIN_DIR . DS . 'init.php';
require_once ADDON_DIR . DS . 'Loader.php';
new \MGModule\SSLCENTERWHMCS\Loader();

$cronQueueRepo = new CronQueueRepo();
$cronQueueRepo->createTable();

$logsRepo = new LogsRepo();
$logsRepo->createLogsTable();

$orderRepo = new OrderRepo();

$apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
$wildcardDnsTimeoutMinutes = (int) (isset($apiConf->wildcard_dns_timeout_minutes) && $apiConf->wildcard_dns_timeout_minutes > 0
    ? $apiConf->wildcard_dns_timeout_minutes
    : 65);
$autoReissueDaysBeforeExpire = isset($apiConf->auto_reissue_days_before_expire)
    ? (int) $apiConf->auto_reissue_days_before_expire
    : 7;

// Reset any tasks stuck in 'processing' for more than 15 minutes (e.g. after a crash)
$cronQueueRepo->resetStuckProcessing(15);

// ---- Process validate tasks ---- //

$validateTasks = $cronQueueRepo->getPendingTasks('validate', 20);

foreach ($validateTasks as $task) {
    $cronQueueRepo->markProcessing($task->id);
    $taskData = json_decode($task->data, true) ?: [];

    try {
        $apiOrderId = isset($taskData['api_order_id']) ? (int) $taskData['api_order_id'] : 0;
        if ($apiOrderId <= 0) {
            throw new \Exception('Missing api_order_id in task data — task cannot be processed');
        }

        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Validate] Checking certificate status for domain: ' . $task->domain . ' (API order #' . $apiOrderId . ')');

        $orderStatus       = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($apiOrderId);
        $status            = strtolower(isset($orderStatus['status'])             ? $orderStatus['status']             : '');
        $statusDescription = isset($orderStatus['status_description']) ? trim($orderStatus['status_description']) : '';
        $dcvMethod         = strtolower(isset($orderStatus['dcv_method'])         ? $orderStatus['dcv_method']         : '');
        $approverMethod    = isset($orderStatus['approver_method'])    ? $orderStatus['approver_method']    : null;
        $san               = isset($orderStatus['san'])                ? $orderStatus['san']                : [];

        $statusMsg = '[Validate] API response — Status: ' . strtoupper($status);
        if ($statusDescription) {
            $statusMsg .= ' | ' . $statusDescription;
        }
        if ($dcvMethod) {
            $statusMsg .= ' | Validation method: ' . strtoupper($dcvMethod);
        }
        $logsRepo->addLog($task->client_id, $task->service_id, 'info', $statusMsg);

        if ($status === 'active' || $status === 'issued') {

            $crtCode   = isset($orderStatus['crt_code'])  ? $orderStatus['crt_code']  : '';
            $caCode    = isset($orderStatus['ca_code'])   ? $orderStatus['ca_code']   : '';
            $validFrom = isset($orderStatus['valid_from']) ? $orderStatus['valid_from'] : '';
            $validTill = isset($orderStatus['valid_till']) ? $orderStatus['valid_till'] : '';

            $logsRepo->addLog($task->client_id, $task->service_id, 'success',
                '[Validate] Certificate issued for domain: ' . $task->domain .
                ' | Valid: ' . ($validFrom ?: '?') . ' — ' . ($validTill ?: '?') .
                ' | Certificate: ' . (empty($crtCode) ? 'MISSING' : 'OK (' . strlen($crtCode) . ' bytes)') .
                ' | CA bundle: '   . (empty($caCode)  ? 'MISSING' : 'OK'));

            $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
            $sslConfig = $sslRepo->getByServiceId($task->service_id);

            if ($sslConfig) {
                $sslConfig->setCa($caCode);
                $sslConfig->setCrt($crtCode);
                $sslConfig->setValidFrom($validFrom);
                $sslConfig->setValidTill($validTill);
                $sslConfig->setSSLStatus($orderStatus['status']);
                $sslConfig->save();
                $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                    '[Validate] Certificate data saved to database for service #' . $task->service_id);
            } else {
                $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                    '[Validate] SSL config record not found for service #' . $task->service_id . ' — certificate data could not be saved');
            }

            $orderRepo->updateStatus($task->service_id, 'Pending Installation');

            if (isset($taskData['install_type']) && $taskData['install_type'] === 'autoinstall') {
                $installTaskData = [
                    'domain'       => $task->domain,
                    'api_order_id' => $apiOrderId,
                    'is_renewal'   => !empty($taskData['is_renewal']),
                ];
                if (!empty($taskData['tls_item_id'])) {
                    $installTaskData['tls_item_id'] = (int) $taskData['tls_item_id'];
                }
                $cronQueueRepo->addTask($task->client_id, $task->service_id, 'install', $installTaskData);
                $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                    '[Validate] Auto-install task created — certificate will be installed on cPanel automatically');
            }

            $logsRepo->addLog($task->client_id, $task->service_id, 'success',
                '[Validate] Certificate issued — task completed. Service #' . $task->service_id . ' is ready for installation.');
            $cronQueueRepo->markDone($task->id);

        } elseif (in_array($status, ['processing', 'pending', 'new_order', 'new'])) {

            // Log detailed validation instructions so both admin and client know what to do
            $validationDetail = buildCronValidationDetail($dcvMethod, $approverMethod, $task->domain);
            if ($validationDetail) {
                $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                    '[Validate] Awaiting domain validation. ' . $validationDetail);
            }

            if (!empty($san) && is_array($san)) {
                $sanParts = [];
                foreach ($san as $sanItem) {
                    $sanItem = (array) $sanItem;
                    $sanName   = isset($sanItem['san_name'])          ? $sanItem['san_name']          : '';
                    $sanMethod = isset($sanItem['validation_method'])  ? $sanItem['validation_method']  : '';
                    if ($sanName) {
                        $sanParts[] = $sanName . ' [' . strtoupper($sanMethod) . ']';
                    }
                }
                if (!empty($sanParts)) {
                    $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                        '[Validate] Domains awaiting validation: ' . implode(', ', $sanParts));
                }
            }

            // Track total validation attempts
            $taskData['validate_attempts'] = (isset($taskData['validate_attempts']) ? (int) $taskData['validate_attempts'] : 0) + 1;

            // DNS → Email auto-switch logic (runs for both wildcard timeout and max-attempts)
            if ($dcvMethod === 'dns' && empty($taskData['dns_switched_to_email'])) {
                $shouldSwitch = false;
                $switchReason = '';

                // Wildcard: time-based timeout (dns_check_start is set at task creation)
                if (!empty($taskData['is_wildcard']) && !empty($taskData['dns_check_start'])) {
                    $elapsedMinutes = (int) round((time() - (int) $taskData['dns_check_start']) / 60);
                    $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                        '[Validate] Wildcard DNS check — elapsed ' . $elapsedMinutes . ' / ' . $wildcardDnsTimeoutMinutes . ' minutes');
                    if ($elapsedMinutes >= $wildcardDnsTimeoutMinutes) {
                        $shouldSwitch = true;
                        $switchReason = 'Wildcard DNS timeout reached (' . $elapsedMinutes . ' min)';
                    }
                }

                // All DNS orders: max attempts threshold (48 runs ≈ 24h at 30min cron intervals)
                if (!$shouldSwitch && $taskData['validate_attempts'] >= 48) {
                    $shouldSwitch = true;
                    $switchReason = 'Maximum DNS validation attempts reached (' . $taskData['validate_attempts'] . ')';
                }

                if ($shouldSwitch) {
                    try {
                        // Use domain from API order status — correct format expected by the API
                        $apiDomain = isset($orderStatus['domain']) ? $orderStatus['domain'] : $task->domain;

                        // Prefer pre-configured fallback email from order setup
                        $approverEmail = !empty($taskData['dns_fallback_email'])
                            ? $taskData['dns_fallback_email']
                            : '';

                        if (empty($approverEmail)) {
                            $approverEmailsResult = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()
                                ->getDomainEmails($apiDomain);
                            if (!empty($approverEmailsResult['ComodoApprovalEmails']) && is_array($approverEmailsResult['ComodoApprovalEmails'])) {
                                $approverEmail = reset($approverEmailsResult['ComodoApprovalEmails']);
                            }
                        }

                        if (empty($approverEmail)) {
                            throw new \Exception('No approver emails available for domain: ' . $apiDomain);
                        }

                        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()
                            ->changeValidationMethod($apiOrderId, [
                                'new_method' => $approverEmail,
                                'domain'     => $apiDomain,
                            ]);

                        $taskData['dns_switched_to_email'] = true;
                        $taskData['dns_switch_time']       = time();
                        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                            '[Validate] ' . $switchReason . '. Switched validation method to EMAIL (' . $approverEmail . ') via API.');

                        $sent = EmailTemplateService::sendDnsToEmailSwitchEmail(
                            $task->service_id, $task->domain, $approverEmail
                        );
                        if (!$sent) {
                            $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                                '[Validate] Failed to send DNS→Email switch notification to client');
                        }
                    } catch (\Exception $switchEx) {
                        $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                            '[Validate] Failed to switch DNS→Email: ' . $switchEx->getMessage());
                    }
                }
            }

            // Auto-upload HTTP/HTTPS validation file to cPanel if autoinstall
            if (in_array($dcvMethod, ['http', 'https']) && isset($taskData['install_type']) && $taskData['install_type'] === 'autoinstall') {
                $taskData = uploadHttpValidationFiles($task, $orderStatus, $logsRepo, $taskData);
            }

            // Increment post-upload check counter; send warning email after threshold
            if (!empty($taskData['files_uploaded'])) {
                $taskData['post_upload_checks'] = (isset($taskData['post_upload_checks']) ? (int) $taskData['post_upload_checks'] : 0) + 1;

                if ($taskData['post_upload_checks'] === 5 && empty($taskData['validation_warning_sent'])) {
                    $warned = EmailTemplateService::sendValidationWarningEmail($task->service_id, $task->domain, $dcvMethod);
                    if ($warned) {
                        $taskData['validation_warning_sent'] = true;
                        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                            '[Validate] Warning email sent to client after ' . $taskData['post_upload_checks'] .
                            ' checks without CA verification. Client advised to switch to email validation method.');
                    }
                }
            }

            // Persist flags and counters for next run
            $cronQueueRepo->updateTaskData($task->id, $taskData);

            $cronQueueRepo->markPending($task->id, 'Awaiting domain validation (status: ' . $status . ')');

        } elseif (in_array($status, ['cancelled', 'expired', 'rejected', 'revoked'])) {
            $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                '[Validate] Certificate order is ' . $status . ' — validation cannot proceed. Task marked as error.');
            $cronQueueRepo->markError($task->id, 'Certificate order is ' . $status);
        } else {
            $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                '[Validate] Unknown status "' . $status . '" — will retry on next cron run.');
            $cronQueueRepo->markPending($task->id, 'Unknown status: ' . $status);
        }

    } catch (\Exception $e) {
        $logsRepo->addLog($task->client_id, $task->service_id, 'error', '[Validate] Error: ' . $e->getMessage());
        $cronQueueRepo->markError($task->id, $e->getMessage());
    }
}

// ---- Process install tasks ---- //

$installTasks = $cronQueueRepo->getPendingTasks('install', 20);

foreach ($installTasks as $task) {
    $cronQueueRepo->markProcessing($task->id);
    $taskData = json_decode($task->data, true) ?: [];
    $domain   = $task->domain;

    try {
        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Install] Starting SSL installation for domain: ' . $domain);

        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslConfig = $sslRepo->getByServiceId($task->service_id);

        if (!$sslConfig) {
            throw new \Exception('SSL config not found for service #' . $task->service_id);
        }

        $details    = (array) $sslConfig->configdata;
        $cert       = isset($details['crt'])         ? $details['crt']         : '';
        $cabundle   = isset($details['ca'])          ? $details['ca']          : '';
        $privateKey = '';

        if (!empty($details['private_key'])) {
            $rawKey     = $details['private_key'];
            $privateKey = (strpos($rawKey, '-----BEGIN') !== false) ? $rawKey : decrypt($rawKey);
        }

        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Install] Data check — Certificate: ' . (empty($cert)       ? 'MISSING' : 'OK (' . strlen($cert) . ' bytes)') .
            ' | CA bundle: '                        . (empty($cabundle)   ? 'MISSING' : 'OK') .
            ' | Private key: '                      . (empty($privateKey) ? 'MISSING' : 'OK'));

        if (empty($cert)) {
            // Not ready yet — reset to pending without incrementing attempts
            $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                '[Install] Certificate data not yet available — will retry on next cron run');
            $cronQueueRepo->markPending($task->id, 'Certificate not yet available');
            continue;
        }

        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Install] Looking up cPanel hosting account for domain: ' . $domain);

        $cpanelService = new Service();
        $cpanelDetails = $cpanelService->getServiceForSsl($task->service_id, $task->client_id, $domain);

        if ($cpanelDetails === false) {
            throw new \Exception(
                'No cPanel hosting account found for domain: ' . $domain .
                ' (client #' . $task->client_id . '). Make sure the domain exists in a cPanel account owned by this client.'
            );
        }

        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Install] cPanel account found — server: ' . $cpanelDetails->hostname .
            ' | cPanel user: ' . $cpanelDetails->user);

        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Install] Sending SSL certificate to cPanel for domain: ' . $domain . '...');

        $cpanel = new Cpanel();
        $cpanel->setService($cpanelDetails);
        $cpanel->installSSL($cpanelDetails->user, $domain, $cert, $privateKey, $cabundle);

        $orderRepo->updateStatus($task->service_id, 'Success');
        $logsRepo->addLog($task->client_id, $task->service_id, 'success',
            '[Install] SSL certificate installed successfully on cPanel — domain: ' . $domain .
            ' | server: ' . $cpanelDetails->hostname);

        if (!empty($taskData['is_renewal'])) {
            $sent = EmailTemplateService::sendRenewalCompleteEmail($task->service_id, $domain);
            if (!$sent) {
                $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                    '[Install] Failed to send Renewal Complete email to client');
            }
        } else {
            $sent = EmailTemplateService::sendCertInstalledEmail($task->service_id, $domain);
            if (!$sent) {
                $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                    '[Install] Failed to send Certificate Installed email to client');
            }
        }

        $cronQueueRepo->markDone($task->id);

    } catch (\Exception $e) {
        $logsRepo->addLog($task->client_id, $task->service_id, 'error', '[Install] Error: ' . $e->getMessage());
        $cronQueueRepo->markError($task->id, $e->getMessage());
    }
}

// ---- Process reissue tasks ---- //

$reissueTasks = $cronQueueRepo->getPendingTasks('reissue', 10);

foreach ($reissueTasks as $task) {
    $cronQueueRepo->markProcessing($task->id);
    $taskData = json_decode($task->data, true) ?: [];

    try {
        $apiOrderId = isset($taskData['api_order_id']) ? (int) $taskData['api_order_id'] : 0;
        if ($apiOrderId <= 0) {
            throw new \Exception('Missing api_order_id in reissue task data');
        }

        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Reissue] Starting automatic reissue for domain: ' . $task->domain . ' (API order #' . $apiOrderId . ')');

        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $sslConfig = $sslRepo->getByServiceId($task->service_id);

        if (!$sslConfig) {
            throw new \Exception('SSL config not found for service #' . $task->service_id);
        }

        $csr       = $sslConfig->getCsr();
        $dcvMethod = $sslConfig->getDcvMethod() ?: 'email';

        if (empty($csr)) {
            throw new \Exception('CSR not found in SSL config for service #' . $task->service_id . ' — cannot reissue automatically');
        }

        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Reissue] Using DCV method: ' . strtoupper($dcvMethod) . ' | CSR length: ' . strlen($csr) . ' bytes');

        $reissueData = [
            'csr'            => $csr,
            'dcv_method'     => $dcvMethod,
            'webserver_type' => '-1',
        ];

        if ($dcvMethod === 'email') {
            $approverMethod = $sslConfig->getApproverMethod();
            $approverEmail  = '';
            if (is_string($approverMethod)) {
                $approverEmail = $approverMethod;
            } elseif (is_array($approverMethod) && isset($approverMethod['email'])) {
                $approverEmail = $approverMethod['email'];
            } elseif (is_object($approverMethod) && isset($approverMethod->email)) {
                $approverEmail = $approverMethod->email;
            }
            if ($approverEmail) {
                $reissueData['approver_email'] = $approverEmail;
            }
        }

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()
            ->getApi()->reIssueOrder($apiOrderId, $reissueData);

        $logsRepo->addLog($task->client_id, $task->service_id, 'success',
            '[Reissue] Reissue submitted successfully for domain: ' . $task->domain . ' — queuing validation and installation tasks');

        $reissueValidateTask = [
            'domain'       => $task->domain,
            'install_type' => 'autoinstall',
            'api_order_id' => $apiOrderId,
            'is_wildcard'  => !empty($taskData['is_wildcard']),
        ];
        if (!empty($taskData['tls_item_id'])) {
            $reissueValidateTask['tls_item_id'] = (int) $taskData['tls_item_id'];
        }
        $cronQueueRepo->addTask($task->client_id, $task->service_id, 'validate', $reissueValidateTask);

        $cronQueueRepo->markDone($task->id);

    } catch (\Exception $e) {
        $logsRepo->addLog($task->client_id, $task->service_id, 'error', '[Reissue] Error: ' . $e->getMessage());
        $cronQueueRepo->markError($task->id, $e->getMessage());

        $validTillForEmail = isset($taskData['cert_expiry']) ? $taskData['cert_expiry'] : '';
        if (!$validTillForEmail) {
            $sslRepoTmp   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
            $sslConfigTmp = $sslRepoTmp->getByServiceId($task->service_id);
            $validTillForEmail = $sslConfigTmp ? (string) $sslConfigTmp->getConfigdataKey('valid_till') : '';
        }
        $daysLeftForEmail = 0;
        if ($validTillForEmail) {
            $daysLeftForEmail = max(0, (int) round((strtotime($validTillForEmail) - time()) / 86400));
        }

        $sent = EmailTemplateService::sendAutoReissueFailedEmail(
            $task->service_id,
            $task->domain,
            $validTillForEmail,
            $daysLeftForEmail,
            $e->getMessage()
        );

        if (!$sent) {
            $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                '[Reissue] Failed to send reissue-failed notification email to client');
        }
    }
}

// ---- Auto-reissue check: scan active autoinstall certs nearing expiry ---- //

if ($autoReissueDaysBeforeExpire > 0) {

    $candidateOrders = DB::table('tblsslorders')
        ->join('tblhosting', 'tblhosting.id', '=', 'tblsslorders.serviceid')
        ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
        ->select([
            'tblsslorders.remoteid',
            'tblsslorders.serviceid',
            'tblsslorders.configdata',
            'tblhosting.userid',
            'tblhosting.domain',
        ])
        ->where('tblproducts.servertype', 'SSLCENTERWHMCS')
        ->whereIn('tblhosting.domainstatus', ['Active'])
        ->whereNotNull('tblsslorders.remoteid')
        ->where('tblsslorders.remoteid', '!=', '')
        ->where('tblsslorders.remoteid', '!=', '0')
        ->where('tblsslorders.configdata', 'like', '%"configssl":1%')
        ->where('tblsslorders.configdata', 'like', '%"install_type":"autoinstall"%')
        ->get();

    $today = new \DateTime('today');

    foreach ($candidateOrders as $order) {
        $serviceId  = (int) $order->serviceid;
        $clientId   = (int) $order->userid;
        $apiOrderId = (int) $order->remoteid;
        $domain     = $order->domain;

        try {
            $configdata = [];
            if ($order->configdata) {
                $decoded = json_decode($order->configdata, true);
                if (is_array($decoded)) {
                    $configdata = $decoded;
                } else {
                    $unserialized = @unserialize($order->configdata);
                    if (is_array($unserialized)) {
                        $configdata = $unserialized;
                    }
                }
            }

            if (empty($configdata['configssl']) || empty($configdata['install_type']) || $configdata['install_type'] !== 'autoinstall') {
                continue;
            }

            $validTill = isset($configdata['valid_till']) ? trim($configdata['valid_till']) : '';
            $endDate   = isset($configdata['end_date'])   ? trim($configdata['end_date'])   : '';

            // Skip if valid_till is missing or is a MySQL null-date (cert not yet issued)
            if (empty($validTill) || $validTill === '0000-00-00' || $validTill === '0000-00-00 00:00:00') {
                continue;
            }

            try {
                $expiryDate = new \DateTime($validTill);
            } catch (\Exception $e) {
                continue;
            }

            // Skip certs not yet issued (expiry date in the distant past means API returned a placeholder)
            if ($expiryDate->getTimestamp() <= 0) {
                continue;
            }

            // Guard: valid_till must differ from end_date (prevents re-triggering the same expiry cycle)
            if ($validTill === $endDate) {
                continue;
            }

            $diff            = $today->diff($expiryDate);
            $daysUntilExpiry = (int) $diff->days;
            if ($expiryDate < $today) {
                $daysUntilExpiry = -$daysUntilExpiry;
            }

            if ($daysUntilExpiry > $autoReissueDaysBeforeExpire) {
                continue;
            }

            // Skip if a reissue task already exists for this service
            $existingTask = DB::table('SSLCENTER_cron_queue')
                ->where('service_id', $serviceId)
                ->where('type', 'reissue')
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if ($existingTask) {
                continue;
            }

            $isWildcard = !empty($configdata['is_wildcard']);
            $autoReissueTask = [
                'domain'       => $domain,
                'api_order_id' => $apiOrderId,
                'install_type' => 'autoinstall',
                'is_wildcard'  => $isWildcard,
            ];
            if (!empty($configdata['tls_item_id'])) {
                $autoReissueTask['tls_item_id'] = (int) $configdata['tls_item_id'];
            }
            $cronQueueRepo->addTask($clientId, $serviceId, 'reissue', $autoReissueTask);

            // Mark end_date = valid_till so we don't re-trigger for this expiry cycle
            $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
            $sslConfig = $sslRepo->getByServiceId($serviceId);
            if ($sslConfig) {
                $sslConfig->setSubscriptionEnds($validTill);
                $sslConfig->save();
            }

            $logsRepo->addLog($clientId, $serviceId, 'info',
                '[AutoReissue] Reissue task created for domain: ' . $domain .
                ' — certificate expires ' . $validTill . ' (' . $daysUntilExpiry . ' days remaining)');

        } catch (\Exception $e) {
            $logsRepo->addLog($clientId, $serviceId, 'error',
                '[AutoReissue] Error checking service #' . $serviceId . ' (domain: ' . $domain . '): ' . $e->getMessage());
        }
    }
}

die();

// ---- Helper: extract http/https validation data from approver_method ---- //

function extractHttpValidationData($dcvMethod, $approverMethod)
{
    $methodData = null;
    if (is_array($approverMethod) && isset($approverMethod[$dcvMethod])) {
        $methodData = (array) $approverMethod[$dcvMethod];
    } elseif (is_object($approverMethod) && isset($approverMethod->$dcvMethod)) {
        $methodData = (array) $approverMethod->$dcvMethod;
    }
    if (!$methodData) {
        return null;
    }
    $content = isset($methodData['content']) ? $methodData['content'] : '';
    if (is_array($content)) {
        $content = implode('', $content);
    }

    $filename = isset($methodData['filename']) ? $methodData['filename'] : '';

    // Some CAs (e.g. Comodo/Sectigo) return a generic name in 'filename'
    // (like 'fileauth.txt') but the real hash-based filename is embedded
    // in the 'url' field (e.g. '...pki-validation/50FC565434A9C6B677435251A1C01D8C.txt').
    // Always prefer the filename extracted from the URL when available.
    if (!empty($methodData['url'])) {
        $urlPath     = parse_url($methodData['url'], PHP_URL_PATH);
        $urlFilename = $urlPath ? basename($urlPath) : '';
        if (!empty($urlFilename)) {
            $filename = $urlFilename;
        }
    }

    return [
        'filename' => $filename,
        'content'  => $content,
        'url'      => isset($methodData['url']) ? $methodData['url'] : '',
    ];
}

// ---- Helper: upload HTTP validation file to cPanel ---- //
// Returns updated $taskData with 'files_uploaded' flag per domain.
// Domains already flagged are skipped on subsequent cron runs.

function uploadHttpValidationFiles($task, $orderStatus, $logsRepo, array $taskData)
{
    $dcvMethod      = strtolower(isset($orderStatus['dcv_method'])      ? $orderStatus['dcv_method']      : '');
    $approverMethod = isset($orderStatus['approver_method']) ? $orderStatus['approver_method'] : null;
    $san            = isset($orderStatus['san'])             ? $orderStatus['san']             : [];

    // Domains where file was already successfully uploaded (persisted in task data)
    $uploadedDomains = isset($taskData['files_uploaded']) ? (array) $taskData['files_uploaded'] : [];

    // Build list of domains + their validation data
    $uploads = [];

    $mainData = extractHttpValidationData($dcvMethod, $approverMethod);
    if ($mainData && $mainData['filename']) {
        $uploads[] = ['domain' => $task->domain, 'filename' => $mainData['filename'], 'content' => $mainData['content']];
    }

    foreach ($san as $sanItem) {
        $sanItem   = (array) $sanItem;
        $sanName   = isset($sanItem['san_name'])             ? $sanItem['san_name']             : '';
        $sanMethod = strtolower(isset($sanItem['validation_method']) ? $sanItem['validation_method'] : '');
        if (!$sanName || !in_array($sanMethod, ['http', 'https'])) {
            continue;
        }
        $sanValidation = isset($sanItem['validation']) ? $sanItem['validation'] : null;
        if (!$sanValidation) {
            continue;
        }
        $sanData = null;
        if (is_array($sanValidation) && isset($sanValidation[$sanMethod])) {
            $sanData = (array) $sanValidation[$sanMethod];
        } elseif (is_object($sanValidation) && isset($sanValidation->$sanMethod)) {
            $sanData = (array) $sanValidation->$sanMethod;
        }
        if ($sanData && isset($sanData['filename'])) {
            $content = isset($sanData['content']) ? $sanData['content'] : '';
            if (is_array($content)) { $content = implode('', $content); }
            $uploads[] = ['domain' => $sanName, 'filename' => $sanData['filename'], 'content' => $content];
        }
    }

    if (empty($uploads)) {
        $logsRepo->addLog($task->client_id, $task->service_id, 'info',
            '[Validate] No HTTP validation file data found in API response — skipping auto-upload');
        return $taskData;
    }

    $cpanelServiceModel = new Service();

    foreach ($uploads as $upload) {
        $domain   = $upload['domain'];
        $filename = $upload['filename'];
        $content  = $upload['content'];

        // Already done on a previous cron run — skip
        if (in_array($domain, $uploadedDomains)) {
            $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                '[Validate] Validation file already uploaded for: ' . $domain . ' — skipping (waiting for CA to verify)');
            continue;
        }

        try {
            $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                '[Validate] Looking up cPanel account for domain: ' . $domain);

            $cpanelDetails = $cpanelServiceModel->getServiceForSsl($task->service_id, $task->client_id, $domain);
            if ($cpanelDetails === false) {
                $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                    '[Validate] cPanel account not found for domain: ' . $domain .
                    ' — please upload the file manually to: /.well-known/pki-validation/' . $filename);
                continue;
            }

            $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                '[Validate] cPanel account found — server: ' . $cpanelDetails->hostname . ' | user: ' . $cpanelDetails->user);

            $cpanel = new Cpanel();
            $cpanel->setService($cpanelDetails);

            $docRoot     = $cpanel->getRootDirectory($cpanelDetails->user, $domain);
            $wellKnown   = $docRoot . '/.well-known';
            $pkiDir      = $wellKnown . '/pki-validation';

            $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                '[Validate] Document root: ' . $docRoot .
                ' | Creating: ' . $wellKnown . ' and ' . $pkiDir);

            $cpanel->addDirectory($cpanelDetails->user, [
                ['dir' => $docRoot,    'name' => '.well-known'],
                ['dir' => $wellKnown,  'name' => 'pki-validation'],
            ]);

            $logsRepo->addLog($task->client_id, $task->service_id, 'info',
                '[Validate] Uploading file: ' . $pkiDir . '/' . $filename . ' (' . strlen($content) . ' bytes)');

            $cpanel->saveFile($cpanelDetails->user, $filename, $pkiDir . '/', $content);

            $verifyUrl = $dcvMethod . '://' . $domain . '/.well-known/pki-validation/' . $filename;
            $logsRepo->addLog($task->client_id, $task->service_id, 'success',
                '[Validate] Validation file uploaded successfully — domain: ' . $domain .
                ' | Verify URL: ' . $verifyUrl);

            // Mark this domain as done so we don't re-upload next run
            $uploadedDomains[] = $domain;

        } catch (\Exception $e) {
            $logsRepo->addLog($task->client_id, $task->service_id, 'error',
                '[Validate] Failed to upload validation file for domain: ' . $domain .
                ' — ' . $e->getMessage() .
                ' | Manual upload required: /.well-known/pki-validation/' . $filename);
        }
    }

    $taskData['files_uploaded'] = $uploadedDomains;
    return $taskData;
}

// ---- Helper: human-readable validation instructions ---- //

function buildCronValidationDetail($dcvMethod, $approverMethod, $domain = '')
{
    if (!$dcvMethod) {
        return '';
    }

    $parts = ['Validation method: ' . strtoupper($dcvMethod)];

    if ($dcvMethod === 'http' || $dcvMethod === 'https') {
        $data = extractHttpValidationData($dcvMethod, $approverMethod);
        if ($data) {
            if ($data['filename']) {
                $url = $dcvMethod . '://' . $domain . '/.well-known/pki-validation/' . $data['filename'];
                $parts[] = 'Verify URL: ' . $url;
                $parts[] = 'Directory: /.well-known/pki-validation/ | File: ' . $data['filename'];
            }
            if ($data['content']) {
                $parts[] = 'File content: ' . substr($data['content'], 0, 120);
            }
        }
    } elseif ($dcvMethod === 'dns') {
        $record  = '';
        $dnsData = null;
        if (is_array($approverMethod) && isset($approverMethod['dns'])) {
            $dnsData = $approverMethod['dns'];
        } elseif (is_object($approverMethod) && isset($approverMethod->dns)) {
            $dnsData = (array) $approverMethod->dns;
        }
        if (is_array($dnsData) && isset($dnsData['record'])) {
            $record = $dnsData['record'];
        } elseif (is_string($dnsData)) {
            $record = $dnsData;
        } elseif (is_string($approverMethod)) {
            $record = $approverMethod;
        }
        if ($record) {
            $parts[] = 'Add DNS record: ' . $record;
        }
    } elseif ($dcvMethod === 'email') {
        $email = '';
        if (is_string($approverMethod)) {
            $email = $approverMethod;
        } elseif (is_array($approverMethod) && isset($approverMethod['email'])) {
            $email = $approverMethod['email'];
        }
        if ($email) {
            $parts[] = 'Approval email sent to: ' . $email . ' — click the link in the email to approve';
        }
    }

    return implode(' | ', $parts);
}
