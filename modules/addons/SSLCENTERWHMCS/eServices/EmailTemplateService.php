<?php

namespace MGModule\SSLCENTERWHMCS\eServices;

use WHMCS\Database\Capsule;
use MGModule\SSLCENTERWHMCS\eServices\emailTemplates\StandardTemplates;
use MGModule\SSLCENTERWHMCS\eServices\emailTemplates\AcmeTemplates;
use MGModule\SSLCENTERWHMCS\eServices\emailTemplates\AutoInstallTemplates;

class EmailTemplateService
{
    use StandardTemplates;
    use AcmeTemplates;
    use AutoInstallTemplates;

    // ---- Standard SSL templates ----
    const CONFIGURATION_TEMPLATE_ID          = 'SSLCenter - Configuration Required';
    const EXPIRATION_TEMPLATE_ID             = 'SSLCenter - Service Expiration';
    const SEND_CERTIFICATE_TEMPLATE_ID       = 'SSLCenter - Send Certificate';
    const RENEWAL_TEMPLATE_ID                = 'SSLCenter - Renewal';
    const REISSUE_TEMPLATE_ID                = 'SSLCenter - Reissue';

    // ---- ACME Subscription templates ----
    const SUBSCRIPTION_TEMPLATE_ID              = 'SSLCenter - ACME Subscription Configuration';
    const SUBSCRIPTION_EXPIRATION_TEMPLATE_ID   = 'SSLCenter - ACME Subscription Expiration';
    const SUBSCRIPTION_RENEWAL_TEMPLATE_ID      = 'SSLCenter - ACME Subscription Renewal';
    const SUBSCRIPTION_RENEWAL_NOTICE_TEMPLATE_ID = 'SSLCenter – Subscription renewal';

    // ---- AutoInstall / Cron templates ----
    const RENEWAL_INVOICE_TEMPLATE_ID        = 'SSLCenter - Renewal Invoice';
    const RENEWAL_STARTED_TEMPLATE_ID        = 'SSLCenter - Renewal Started';
    const RENEWAL_COMPLETE_TEMPLATE_ID       = 'SSLCenter - Renewal Complete';
    const RENEWAL_AUTORENEWED_TEMPLATE_ID    = 'SSLCenter - Certificate Autorenewed';
    const CERT_REVOKED_TEMPLATE_ID           = 'SSLCenter - Certificate Revoked';
    const DNS_TO_EMAIL_SWITCH_TEMPLATE_ID    = 'SSLCenter - DNS Validation Switched to Email';
    const AUTO_REISSUE_FAILED_TEMPLATE_ID    = 'SSLCenter - Auto Reissue Failed';
    const CERT_EXPIRED_TEMPLATE_ID           = 'SSLCenter - Certificate Expired';
    const CERT_INSTALLED_TEMPLATE_ID         = 'SSLCenter - Certificate Installed';
    const VALIDATION_WARNING_TEMPLATE_ID     = 'SSLCenter - Validation Warning';

    // ---- Generic lookup helpers ----

    /**
     * The master template (language ''), never one of its translations.
     */
    public static function getTemplate($name)
    {
        return \MGModule\SSLCENTERWHMCS\eModels\whmcs\EmailTemplate::whereName($name)->master()->orderBy('id')->first();
    }

    public static function getTemplateName($id)
    {
        $template = \MGModule\SSLCENTERWHMCS\eModels\whmcs\EmailTemplate::whereId($id)->first();
        return $template->name;
    }

    public static function getGeneralTemplates()
    {
        return \MGModule\SSLCENTERWHMCS\eModels\whmcs\EmailTemplate::whereType('product')->master()->get();
    }

    // ---- Generic internal helpers used by traits ----

    protected static function createTemplate($name, $subject, $message, $type = 'product')
    {
        // Only the master row counts: translations alone cannot be sent, so a missing
        // master is (re)created even when translation rows of the template exist.
        if (!is_null(self::getTemplate($name))) {
            return;
        }

        $tpl              = new \MGModule\SSLCENTERWHMCS\eModels\whmcs\EmailTemplate();
        $tpl->type        = $type;
        $tpl->name        = $name;
        $tpl->subject     = $subject;
        $tpl->message     = $message;
        $tpl->attachments = '';
        $tpl->fromname    = '';
        $tpl->fromemail   = '';
        $tpl->disabled    = '0';
        $tpl->custom      = 1;
        $tpl->language    = '';
        $tpl->copyto      = '';
        $tpl->plaintext   = '0';
        $tpl->created_at  = date('Y-m-d H:i:s');
        $tpl->updated_at  = date('Y-m-d H:i:s');

        $hasBcc = Capsule::connection()->select("SHOW COLUMNS FROM `tblemailtemplates` LIKE 'blind_copy_to';");
        if (!empty($hasBcc)) {
            $tpl->blind_copy_to = '';
        }

        $tpl->save();
    }

    protected static function updateTemplate($name, $subject, $message)
    {
        $tpl = self::getTemplate($name);

        if (empty($tpl)) {
            self::createTemplate($name, $subject, $message);
            return;
        }

        $tpl->subject = $subject;
        $tpl->message = $message;
        $tpl->save();
    }

    /**
     * Deletes the template together with all its translations, so no orphan
     * translation rows are left behind.
     */
    protected static function deleteTemplate($name)
    {
        \MGModule\SSLCENTERWHMCS\eModels\whmcs\EmailTemplate::whereName($name)->delete();
    }
}
