<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class AdminServicesTabFields {

    private $p;

    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        try {
            return $this->adminServicesTabFields();
        } catch (Exception $ex) {
            return [];
        }
        return [];
    }

    private function adminServicesTabFields() {
        $return = [];
        $return['JS/HTML'] = \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getAdminServiceScript($this->getServiceVars());
        return array_merge($return, $this->getCertificateDetails());
    }
    
    private function getCertificateDetails() {
        
        try {
            $ssl        = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
            $sslService = $ssl->getByServiceId($this->p['serviceid']);
            if (is_null($sslService)) {
                throw new Exception('Create has not been initialized');
            }
            
            if ($sslService->status === 'Awaiting Configuration') {
                return ['Configuration Status' => 'Awaiting Configuration'];
            }

            if (\MGModule\SSLCENTERWHMCS\eServices\SmimeService::isSmimeOrder($sslService)) {
                return $this->getSmimeDetails($sslService);
            }

            if (\MGModule\SSLCENTERWHMCS\eServices\CodeSigningService::isCodeSigningOrder($sslService)) {
                return $this->getCodeSigningDetails($sslService);
            }

            if(empty($sslService->remoteid)) {
                throw new Exception('Order id not exist');
            }
            
            $return = [];
            $return['SSLCenter API Order ID'] = $sslService->remoteid;

            $orderDetails = (array)$sslService->configdata;
            
            if(!$orderDetails['domain'])
            {
                $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService);
                $orderStatus = $configDataUpdate->run();
                
                $sslService = $ssl->getByServiceId($this->p['serviceid']);    
                $orderDetails = (array)$sslService->configdata;
            }
 
            $return['Cron Synchronized'] = isset($orderDetails['synchronized']) && !empty($orderDetails['synchronized']) ? $orderDetails['synchronized'] : 'Not synchronized';
            $return['Comodo Order ID'] = $orderDetails['partner_order_id']?:"-"; 
            $return['Configuration Status'] = $sslService->status;  
            $return['Domain'] = $orderDetails['domain'];
            $return['Order Status'] = ucfirst($orderDetails['ssl_status']);   
            if(isset($orderDetails['approver_method']->email) && !empty($orderDetails['approver_method']->email))
            {
                $return['Approver email'] = $orderDetails['approver_method']->email;
            }
            $return['Order Status Description'] = $orderDetails['order_status_description'] ? : '-';
            $x9Usages = \MGModule\SSLCENTERWHMCS\eHelpers\X9Product::getUsagesOfService($sslService);
            if ($x9Usages !== null) {
                $return['Key Usage (X9)'] = \MGModule\SSLCENTERWHMCS\eHelpers\X9Product::describeUsages($x9Usages);
            }
            // OV / EV: the organization the customer submitted at enrollment
            $orgDetails = \MGModule\SSLCENTERWHMCS\eHelpers\OrganizationDetails::getOfService($sslService);
            if ($orgDetails !== null) {
                $return['Organization'] = htmlspecialchars($orgDetails['org_name'] !== '' ? $orgDetails['org_name'] : '-', ENT_QUOTES, 'UTF-8')
                    . ($orgDetails['org_division'] !== '' ? ' (' . htmlspecialchars($orgDetails['org_division'], ENT_QUOTES, 'UTF-8') . ')' : '');
                $orgAddress = \MGModule\SSLCENTERWHMCS\eHelpers\OrganizationDetails::describeAddress($orgDetails);
                $return['Organization Address'] = $orgAddress !== '' ? htmlspecialchars($orgAddress, ENT_QUOTES, 'UTF-8') : '-';
                if ($orgDetails['org_phone'] !== '') {
                    $return['Organization Phone'] = htmlspecialchars($orgDetails['org_phone'], ENT_QUOTES, 'UTF-8');
                }
            }
            
            if($orderDetails['ssl_status'] == 'active') {                
                $return['Valid From'] = $orderDetails['valid_from'];
                $return['Expires'] = $orderDetails['valid_till'];
            }
       
            foreach ($orderDetails['san_details'] as $key => $san) {
                $return['SAN ' . ($key + 1)] = sprintf('%s / %s', $san->san_name, $san->status_description);
            }

            if (!\MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeByServiceParams($this->p)
                && !\MGModule\SSLCENTERWHMCS\eHelpers\AisSubscription::isAisByServiceParams($this->p)) {
                $return['Install certificate on'] = $this->getInstallTargetField($sslService);
            }

            return $return;
            
        } catch (Exception $ex) {
            return ['SSLCenter Error' => $ex->getMessage()];
        }
    }

    /**
     * Code signing certificate (API v2) details - saved data, refreshed by the "Refresh Code Signing Details" button and the cron.
     */
    private function getCodeSigningDetails($sslService) {
        $config = json_decode(json_encode($sslService->configdata), true);
        $config = is_array($config) ? $config : [];

        $method   = isset($config['cs_method']) ? (string) $config['cs_method'] : '';
        $delivery = '-';
        if ($method === 'ship-token') {
            $delivery = 'New hardware token, ' . strtolower(!empty($config['cs_shipping']) ? $config['cs_shipping'] : 'standard') . ' shipping';
        } elseif ($method === 'existing-token') {
            $delivery = 'Existing hardware token' . (!empty($config['cs_token_model']) ? ' (' . $config['cs_token_model'] . ')' : '');
        } elseif ($method === 'hsm') {
            $delivery = 'Own HSM (CSR provided)';
        }

        $status = isset($config['ssl_status']) ? (string) $config['ssl_status'] : '';

        $return = [
            'Product Type'               => 'Code signing certificate (API v2)',
            'SSLCenter API Order ID'     => htmlspecialchars((string) $sslService->remoteid, ENT_QUOTES),
            'Code Signing Certificate ID' => (int) $config['cs_item_id'],
            'CA Order ID'                => !empty($config['partner_order_id']) ? htmlspecialchars($config['partner_order_id'], ENT_QUOTES) : '-',
            'Configuration Status'       => htmlspecialchars((string) $sslService->status, ENT_QUOTES),
            'Order Status'               => $status !== '' ? htmlspecialchars(ucfirst($status), ENT_QUOTES) : '-',
            'Organization'               => !empty($config['cs_organization']) ? htmlspecialchars($config['cs_organization'], ENT_QUOTES) : '-',
            'Certificate Delivery'       => htmlspecialchars($delivery, ENT_QUOTES),
            'Term'                       => !empty($config['cs_term']) ? (int) $config['cs_term'] . ' months' : '-',
        ];

        if (!empty($config['valid_from'])) {
            $return['Valid From'] = htmlspecialchars($config['valid_from'], ENT_QUOTES);
        }
        if (!empty($config['valid_till'])) {
            $return['Expires'] = htmlspecialchars($config['valid_till'], ENT_QUOTES);
        }
        if (!empty($config['cs_refreshed_at'])) {
            $return['Last Refreshed'] = htmlspecialchars($config['cs_refreshed_at'], ENT_QUOTES);
        }

        return $return;
    }

    /**
     * S/MIME certificate (API v2) details - saved data, refreshed by the "Refresh S/MIME Details" button and the cron.
     */
    private function getSmimeDetails($sslService) {
        $config = json_decode(json_encode($sslService->configdata), true);
        $config = is_array($config) ? $config : [];

        $emailsHtml = [];
        foreach (\MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct::normalizeEmails(isset($config['smime_emails']) ? $config['smime_emails'] : []) as $entry) {
            $emailsHtml[] = htmlspecialchars($entry['email'], ENT_QUOTES)
                . ($entry['approved']
                    ? ' <span class="label label-success">validated</span>'
                    : ' <span class="label label-warning">awaiting validation</span>');
        }

        $status = isset($config['ssl_status']) ? (string) $config['ssl_status'] : '';

        $return = [
            'Product Type'           => 'S/MIME certificate (API v2)',
            'SSLCenter API Order ID' => htmlspecialchars((string) $sslService->remoteid, ENT_QUOTES),
            'S/MIME Certificate ID'  => (int) $config['smime_item_id'],
            'CA Order ID'            => !empty($config['partner_order_id']) ? htmlspecialchars($config['partner_order_id'], ENT_QUOTES) : '-',
            'Configuration Status'   => htmlspecialchars((string) $sslService->status, ENT_QUOTES),
            'Order Status'           => $status !== '' ? htmlspecialchars(ucfirst($status), ENT_QUOTES) : '-',
            'Emails'                 => !empty($emailsHtml) ? implode('<br>', $emailsHtml) : '-',
            'Term'                   => !empty($config['smime_term']) ? (int) $config['smime_term'] . ' months' : '-',
            'Own CSR'                => !empty($config['smime_csr_provided']) ? 'Yes' : 'No',
        ];

        if (!empty($config['valid_from'])) {
            $return['Valid From'] = htmlspecialchars($config['valid_from'], ENT_QUOTES);
        }
        if (!empty($config['valid_till'])) {
            $return['Expires'] = htmlspecialchars($config['valid_till'], ENT_QUOTES);
        }
        if (!empty($config['smime_refreshed_at'])) {
            $return['Last Refreshed'] = htmlspecialchars($config['smime_refreshed_at'], ENT_QUOTES);
        }

        return $return;
    }

    /**
     * "Install certificate on" selector and automatic cPanel installation checkbox,
     * saved by SSLCENTERWHMCS_AdminServicesTabFieldsSave().
     */
    private function getInstallTargetField($sslService) {
        $options = \MGModule\SSLCENTERWHMCS\eHelpers\InstallTarget::getOptions($sslService->userid);
        $state   = \MGModule\SSLCENTERWHMCS\eHelpers\InstallTarget::getState($sslService);

        if (empty($options)) {
            return '<span class="text-muted">The client has no cPanel hosting services (cPanel or cPanel Extended).</span>';
        }

        $html = '<input type="hidden" name="sslcenter_install_target_form" value="1">'
            . '<select name="sslcenter_install_target" class="form-control select-inline">'
            . '<option value="0">Find by domain (automatic)</option>';
        foreach ($options as $id => $label) {
            $html .= '<option value="' . (int) $id . '"' . ($state['linked_id'] === (int) $id ? ' selected' : '') . '>'
                . htmlspecialchars($label, ENT_QUOTES) . '</option>';
        }
        $html .= '</select>';

        $html .= '<div class="checkbox" style="margin:6px 0 0;"><label>'
            . '<input type="checkbox" name="sslcenter_install_auto" value="1"'
            . ($state['automatic'] ? ' checked' : '')
            . ($state['can_automate'] || $state['automatic'] ? '' : ' disabled')
            . '> Automatic cPanel installation (install, validation file upload and reissue via cron)</label></div>';

        if (!$state['can_automate']) {
            $html .= '<div class="text-muted" style="font-size:11px;">Automatic installation is not available: the private key of this certificate is not stored in WHMCS (the order was placed with the client\'s own CSR). Reissue it with a CSR generated by the module first. Manual "Install on Server" also needs the private key.</div>';
        }

        return $html;
    }

    private function getServiceVars() {
        global $CONFIG;
        
        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = (int) $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        $sansLimit = $boughtSans;

        $includedSansWildcard = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS_WILDCARD];
        $boughtSansWildcard   = (int) $this->p['configoptions'][ConfigOptions::OPTION_SANS_WILDCARD_COUNT];
        $sansLimitWildcard = $boughtSansWildcard;
        
        require dirname(dirname(dirname(dirname(dirname(__DIR__))))).DIRECTORY_SEPARATOR.'configuration.php';

        $adminpath = 'admin';
        if(isset($customadminpath))
        {
            $adminpath = $customadminpath;
        }
         
        return [
            'serviceid' => $this->p['serviceid'],
            'email'     => $this->p['clientsdetails']['email'],
            'userid'    => $this->p['userid'],
            'sansLimit' => $sansLimit,
            'sansLimitWildcard' => $sansLimitWildcard,
            'adminpath' => $adminpath,
            'version'   => substr($CONFIG['Version'],0,1)
        ];
    }
}
