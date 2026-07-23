<?php

namespace MGModule\SSLCENTERWHMCS\eServices\emailTemplates;

trait AutoInstallTemplates
{
    public static function createRenewalInvoiceTemplate()
    {
        self::createTemplate(
            self::RENEWAL_INVOICE_TEMPLATE_ID,
            'Action Required: SSL Certificate Renewal Invoice – {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Your SSL certificate for <strong>{$domain}</strong> has expired ({$cert_expiry}).</p>'
            . '<p>Please pay renewal invoice <strong>#{$renewal_invoice_id}</strong> to initiate the renewal process.</p>'
            . '<p><a href="{$invoice_url}" style="display:inline-block;padding:10px 22px;background:#e67e22;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View Renewal Invoice</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteRenewalInvoiceTemplate()
    {
        self::deleteTemplate(self::RENEWAL_INVOICE_TEMPLATE_ID);
    }

    /**
     * @param int    $serviceId
     * @param string $domain
     * @param string $certExpiry
     * @param int    $invoiceId
     * @return bool
     */
    public static function sendRenewalInvoiceEmail($serviceId, $domain, $certExpiry, $invoiceId)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $invoiceUrl = rtrim((string) $systemUrl, '/') . '/viewinvoice.php?id=' . (int) $invoiceId;

        $result = localAPI('SendEmail', [
            'messagename' => self::RENEWAL_INVOICE_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'             => $domain,
                'cert_expiry'        => $certExpiry,
                'renewal_invoice_id' => $invoiceId,
                'invoice_url'        => $invoiceUrl,
                'company_name'       => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createRenewalStartedTemplate()
    {
        self::createTemplate(
            self::RENEWAL_STARTED_TEMPLATE_ID,
            'SSL Certificate Renewal Started – {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Renewal has been initiated for your SSL certificate on <strong>{$domain}</strong>.</p>'
            . '<p>Current expiry: <strong>{$cert_expiry}</strong> ({$days_left} days remaining).</p>'
            . '<p>You will receive a confirmation email once the renewed certificate has been installed.</p>'
            . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View SSL Order</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteRenewalStartedTemplate()
    {
        self::deleteTemplate(self::RENEWAL_STARTED_TEMPLATE_ID);
    }

    /**
     * @param int    $serviceId
     * @param string $domain
     * @param string $certExpiry
     * @param int    $daysLeft
     * @return bool
     */
    public static function sendRenewalStartedEmail($serviceId, $domain, $certExpiry, $daysLeft)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $orderUrl = rtrim((string) $systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId;

        $result = localAPI('SendEmail', [
            'messagename' => self::RENEWAL_STARTED_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'       => $domain,
                'cert_expiry'  => $certExpiry,
                'days_left'    => max(0, $daysLeft),
                'order_url'    => $orderUrl,
                'company_name' => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createRenewalCompleteTemplate()
    {
        self::createTemplate(
            self::RENEWAL_COMPLETE_TEMPLATE_ID,
            'SSL Certificate Renewed Successfully – {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Your SSL certificate for <strong>{$domain}</strong> has been renewed and installed successfully. No further action is required.</p>'
            . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#27ae60;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View SSL Order</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteRenewalCompleteTemplate()
    {
        self::deleteTemplate(self::RENEWAL_COMPLETE_TEMPLATE_ID);
    }

    /**
     * @param int    $serviceId
     * @param string $domain
     * @return bool
     */
    public static function sendRenewalCompleteEmail($serviceId, $domain)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $orderUrl = rtrim((string) $systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId;

        $result = localAPI('SendEmail', [
            'messagename' => self::RENEWAL_COMPLETE_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'       => $domain,
                'order_url'    => $orderUrl,
                'company_name' => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createRenewalAutorenewedTemplate()
    {
        self::createTemplate(
            self::RENEWAL_AUTORENEWED_TEMPLATE_ID,
            'SSL Certificate Autorenewed for {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Your SSL certificate order <strong>{$order_number}</strong> for <strong>{$common_name}</strong> has been autorenewed and a new order has been placed with the Certificate Authority.</p>'
            . '<p>If you need to make updates to the order or download the certificate, visit the order page:</p>'
            . '<p><a href="{$order_link}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">{$order_link}</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteRenewalAutorenewedTemplate()
    {
        self::deleteTemplate(self::RENEWAL_AUTORENEWED_TEMPLATE_ID);
    }

    /**
     * @param int    $serviceId
     * @param string $domain
     * @param string $orderNumber  API order ID
     * @param string $commonName   CN from CSR / domain
     * @return bool
     */
    public static function sendRenewalAutorenewedEmail($serviceId, $domain, $orderNumber, $commonName)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $orderLink = rtrim((string) $systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId;

        $result = localAPI('SendEmail', [
            'messagename' => self::RENEWAL_AUTORENEWED_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'       => $domain,
                'order_number' => $orderNumber,
                'common_name'  => $commonName ?: $domain,
                'order_link'   => $orderLink,
                'company_name' => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createCertRevokedTemplate()
    {
        self::createTemplate(
            self::CERT_REVOKED_TEMPLATE_ID,
            'Important: Your SSL Certificate Has Been Revoked – {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Your SSL certificate for <strong>{$domain}</strong> (serial: <strong>{$serial_number}</strong>, order: <strong>{$order_id}</strong>) has been revoked.</p>'
            . '<p>Your website is no longer protected by a valid SSL certificate. Please contact support immediately to arrange a replacement.</p>'
            . '<p><a href="{$support_url}" style="display:inline-block;padding:10px 22px;background:#c0392b;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">Contact Support</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteCertRevokedTemplate()
    {
        self::deleteTemplate(self::CERT_REVOKED_TEMPLATE_ID);
    }

    /**
     * @param int    $serviceId
     * @param string $domain
     * @param string $serialNumber
     * @param string $orderId      API order ID
     * @return bool
     */
    public static function sendCertRevokedEmail($serviceId, $domain, $serialNumber, $orderId)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $supportUrl = rtrim((string) $systemUrl, '/') . '/submitticket.php';

        $result = localAPI('SendEmail', [
            'messagename' => self::CERT_REVOKED_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'        => $domain,
                'serial_number' => $serialNumber ?: '—',
                'order_id'      => $orderId,
                'support_url'   => $supportUrl,
                'company_name'  => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createDnsToEmailSwitchTemplate()
    {
        self::createTemplate(
            self::DNS_TO_EMAIL_SWITCH_TEMPLATE_ID,
            'Action Required: Email Validation Needed for {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>We attempted DNS-based domain validation for <strong>{$domain}</strong> but could not complete it automatically.</p>'
            . '<p>Email validation has been initiated instead. An approval email has been sent to:</p>'
            . '<p><strong>{$dcv_email}</strong></p>'
            . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">Manage SSL Order</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteDnsToEmailSwitchTemplate()
    {
        self::deleteTemplate(self::DNS_TO_EMAIL_SWITCH_TEMPLATE_ID);
    }

    /**
     * @param int    $serviceId
     * @param string $domain
     * @param string $dcvEmail   Email address that the CA approval was sent to
     * @return bool
     */
    public static function sendDnsToEmailSwitchEmail($serviceId, $domain, $dcvEmail)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $orderUrl = rtrim((string) $systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId;

        $result = localAPI('SendEmail', [
            'messagename' => self::DNS_TO_EMAIL_SWITCH_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'       => $domain,
                'dcv_email'    => $dcvEmail ?: '(see your domain registrar contact)',
                'order_url'    => $orderUrl,
                'company_name' => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createCertInstalledTemplate()
    {
        self::createTemplate(
            self::CERT_INSTALLED_TEMPLATE_ID,
            'SSL Certificate Installed for {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Your SSL certificate for <strong>{$domain}</strong> has been successfully issued and installed on your server. No further action is required.</p>'
            . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#27ae60;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View SSL Order</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteCertInstalledTemplate()
    {
        self::deleteTemplate(self::CERT_INSTALLED_TEMPLATE_ID);
    }

    /**
     * @param int    $serviceId
     * @param string $domain
     * @return bool
     */
    public static function sendCertInstalledEmail($serviceId, $domain)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $orderUrl = rtrim((string) $systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId;

        $result = localAPI('SendEmail', [
            'messagename' => self::CERT_INSTALLED_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'       => $domain,
                'order_url'    => $orderUrl,
                'company_name' => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createCertExpiredTemplate()
    {
        self::createTemplate(
            self::CERT_EXPIRED_TEMPLATE_ID,
            'Your SSL Certificate Has Expired – {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Your SSL certificate for <strong>{$domain}</strong> expired on <strong>{$cert_expiry}</strong>.</p>'
            . '<p>Please renew it as soon as possible to restore HTTPS protection for your website.</p>'
            . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#e74c3c;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">Renew SSL Certificate</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteCertExpiredTemplate()
    {
        self::deleteTemplate(self::CERT_EXPIRED_TEMPLATE_ID);
    }

    /**
     * Send the "certificate expired" notification to the client.
     *
     * @param int    $serviceId
     * @param string $domain
     * @param string $certExpiry  e.g. "2026-07-01"
     * @return bool
     */
    public static function sendCertExpiredEmail($serviceId, $domain, $certExpiry)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $orderUrl = rtrim((string) $systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId;

        $result = localAPI('SendEmail', [
            'messagename' => self::CERT_EXPIRED_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'       => $domain,
                'cert_expiry'  => $certExpiry,
                'order_url'    => $orderUrl,
                'company_name' => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createAutoReissueFailedTemplate()
    {
        self::createTemplate(
            self::AUTO_REISSUE_FAILED_TEMPLATE_ID,
            'Action Required: SSL Certificate Reissue Failed – {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>We were unable to automatically reissue your SSL certificate for <strong>{$domain}</strong>.</p>'
            . '<table style="border-collapse:collapse;margin:16px 0;">'
            . '<tr><td style="padding:6px 14px 6px 0;color:#666;font-weight:600;white-space:nowrap;">Certificate expiry:</td>'
            . '<td style="padding:6px 0;">{$cert_expiry} ({$days_left} days remaining)</td></tr>'
            . '<tr><td style="padding:6px 14px 6px 0;color:#666;font-weight:600;white-space:nowrap;">Error:</td>'
            . '<td style="padding:6px 0;color:#c0392b;">{$error_message}</td></tr>'
            . '</table>'
            . '<p>Please contact support so we can resolve this promptly.</p>'
            . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View SSL Order</a></p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteAutoReissueFailedTemplate()
    {
        self::deleteTemplate(self::AUTO_REISSUE_FAILED_TEMPLATE_ID);
    }

    /**
     * Send the "auto reissue failed" notification to the client.
     *
     * @param int    $serviceId
     * @param string $domain
     * @param string $certExpiry   e.g. "2026-07-14"
     * @param int    $daysLeft
     * @param string $errorMessage
     * @return bool
     */
    public static function sendAutoReissueFailedEmail($serviceId, $domain, $certExpiry, $daysLeft, $errorMessage)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $orderUrl = rtrim((string) $systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId;

        $result = localAPI('SendEmail', [
            'messagename'   => self::AUTO_REISSUE_FAILED_TEMPLATE_ID,
            'id'            => (int) $serviceId,
            'customvars'    => base64_encode(serialize([
                'domain'        => $domain,
                'cert_expiry'   => $certExpiry,
                'days_left'     => $daysLeft,
                'error_message' => $errorMessage,
                'order_url'     => $orderUrl,
                'company_name'  => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function createValidationWarningTemplate()
    {
        self::createTemplate(
            self::VALIDATION_WARNING_TEMPLATE_ID,
            'Action Required: SSL Certificate Validation Issue for {$domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>We are having trouble automatically validating your SSL certificate for the domain <strong>{$domain}</strong>.</p>'
            . '<p>The validation file was uploaded to your server using the <strong>{$dcv_method} file method</strong>, '
            . 'but the Certificate Authority has not been able to verify it after several attempts.</p>'
            . '<p>This can happen when:</p>'
            . '<ul>'
            . '<li>The file is blocked by a redirect or <code>.htaccess</code> rule (e.g. forced HTTPS or www redirect)</li>'
            . '<li>A CDN or proxy (e.g. Cloudflare) is caching or blocking the path</li>'
            . '<li>The web server is not configured to serve files without extensions</li>'
            . '</ul>'
            . '<p><strong>What to do:</strong> Please log in to your account and switch the validation method to <strong>Email Approval</strong>. '
            . 'An approval email will be sent to an address at your domain (e.g. admin@{$domain}), '
            . 'and clicking the link in that email will complete validation immediately.</p>'
            . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">Manage SSL Certificate</a></p>'
            . '<p>If you need help, please contact our support team.</p>'
            . '<p>Regards,<br />{$company_name}</p>'
        );
    }

    public static function deleteValidationWarningTemplate()
    {
        self::deleteTemplate(self::VALIDATION_WARNING_TEMPLATE_ID);
    }

    /**
     * @param int    $serviceId
     * @param string $domain
     * @param string $dcvMethod  e.g. "http" or "https"
     * @return bool
     */
    public static function sendValidationWarningEmail($serviceId, $domain, $dcvMethod)
    {
        $companyName = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'CompanyName')->value('value');

        $systemUrl = \WHMCS\Database\Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value');

        $orderUrl = rtrim((string) $systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int) $serviceId;

        $result = localAPI('SendEmail', [
            'messagename' => self::VALIDATION_WARNING_TEMPLATE_ID,
            'id'          => (int) $serviceId,
            'customvars'  => base64_encode(serialize([
                'domain'       => $domain,
                'dcv_method'   => strtoupper($dcvMethod),
                'order_url'    => $orderUrl,
                'company_name' => $companyName,
            ])),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }
}
