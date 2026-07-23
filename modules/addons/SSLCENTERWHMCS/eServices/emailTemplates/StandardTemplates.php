<?php

namespace MGModule\SSLCENTERWHMCS\eServices\emailTemplates;

trait StandardTemplates
{
    public static function createRenewalTemplate()
    {
        self::createTemplate(
            self::RENEWAL_TEMPLATE_ID,
            'SSL Certificate - Renew',
            '<p>Dear {$client_name},</p>'
            . '<p>Your current SSL certificate #{$service_id} expires within next 30-days. Please login to the client area and click "Renew" button to start the renewal process.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function updateRenewalTemplate()
    {
        self::updateTemplate(
            self::RENEWAL_TEMPLATE_ID,
            'SSL Certificate - Renew',
            '<p>Dear {$client_name},</p>'
            . '<p>Your current SSL certificate #{$service_id} expires within next 30-days. Please login to the client area and click "Renew" button to start the renewal process.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteRenewalTemplate()
    {
        self::deleteTemplate(self::RENEWAL_TEMPLATE_ID);
    }

    public static function createConfigurationTemplate()
    {
        self::createTemplate(
            self::CONFIGURATION_TEMPLATE_ID,
            'SSL Certificate - configuration required',
            '<p>Dear {$client_name},</p>'
            . '<p>Thank you for your order for an SSL Certificate{if $service_domain} related to domain: {$service_domain}{/if}. Before you can use your certificate, it requires configuration which can be done at the URL below.</p>'
            . '<p>{$ssl_configuration_link}</p>'
            . '<p>Instructions are provided throughout the process but if you experience any problems or have any questions, please open a ticket for assistance.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function updateConfigurationTemplate()
    {
        self::updateTemplate(
            self::CONFIGURATION_TEMPLATE_ID,
            'SSL Certificate - configuration required',
            '<p>Dear {$client_name},</p>'
            . '<p>Thank you for your order for an SSL Certificate{if $service_domain} related to domain: {$service_domain}{/if}. Before you can use your certificate, it requires configuration which can be done at the URL below.</p>'
            . '<p>{$ssl_configuration_link}</p>'
            . '<p>Instructions are provided throughout the process but if you experience any problems or have any questions, please open a ticket for assistance.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteConfigurationTemplate()
    {
        self::deleteTemplate(self::CONFIGURATION_TEMPLATE_ID);
    }

    public static function createCertyficateTemplate()
    {
        self::createTemplate(
            self::SEND_CERTIFICATE_TEMPLATE_ID,
            'SSL Certificate',
            '<p>Dear {$client_name},</p>'
            . '<p>Domain: </p><p>{$domain}</p>'
            . '<p>Intermediate certificate: </p><p>{$ssl_certyficate}</p>'
            . '<p>CRT: </p><p>{$crt_code}</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteCertyficateTemplate()
    {
        self::deleteTemplate(self::SEND_CERTIFICATE_TEMPLATE_ID);
    }

    public static function createExpireNotificationTemplate()
    {
        self::createTemplate(
            self::EXPIRATION_TEMPLATE_ID,
            'Service Expiration Notification - {$service_domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>We would like to inform You about your service <strong>#{$service_id}</strong> is going to expire in {$expireDaysLeft} days.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteExpireNotificationTemplate()
    {
        self::deleteTemplate(self::EXPIRATION_TEMPLATE_ID);
    }

    public static function createReissueTemplate()
    {
        self::createTemplate(
            self::REISSUE_TEMPLATE_ID,
            'SSL Certificate - Reissue',
            '<p>Dear {$client_name},</p>'
            . '<p>Your current SSL certificate #{$service_id} expires within next 30-days. You are using an SSL subscription and no other payments are required. However, you need to reissue your SSL certificate to receive new files for the next period.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function updateReissueTemplate()
    {
        self::updateTemplate(
            self::REISSUE_TEMPLATE_ID,
            'SSL Certificate - Reissue',
            '<p>Dear {$client_name},</p>'
            . '<p>Your current SSL certificate #{$service_id} expires within next 30-days. You are using an SSL subscription and no other payments are required. However, you need to reissue your SSL certificate to receive new files for the next period.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteReissueTemplate()
    {
        self::deleteTemplate(self::REISSUE_TEMPLATE_ID);
    }
}
