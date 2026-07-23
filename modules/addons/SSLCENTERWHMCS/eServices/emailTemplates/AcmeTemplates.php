<?php

namespace MGModule\SSLCENTERWHMCS\eServices\emailTemplates;

trait AcmeTemplates
{
    public static function createSubscriptionTemplate()
    {
        self::createTemplate(
            self::SUBSCRIPTION_TEMPLATE_ID,
            'ACME Subscription - configuration required',
            '<p>Dear {$client_name},</p>'
            . '<p>Your ACME subscription service #{$service_id} is ready for configuration in client area. Configuration can be done at the URL below.</p>'
            . '<p>{$ssl_configuration_link}</p>'
            . '<p>Please add your domains and create subscription.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteSubscriptionTemplate()
    {
        self::deleteTemplate(self::SUBSCRIPTION_TEMPLATE_ID);
    }

    public static function createSubscriptionExpirationTemplate()
    {
        self::createTemplate(
            self::SUBSCRIPTION_EXPIRATION_TEMPLATE_ID,
            'ACME Subscription Expiration Notice - {$service_domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Your ACME Subscription service <strong>#{$service_id}</strong> is approaching its expiration date in <strong>{$expireDaysLeft} days</strong>.</p>'
            . '<p>A renewal invoice has been issued for your service. Please ensure it is paid on time to avoid any interruption to your subscription.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function updateSubscriptionExpirationTemplate()
    {
        self::updateTemplate(
            self::SUBSCRIPTION_EXPIRATION_TEMPLATE_ID,
            'ACME Subscription Expiration Notice - {$service_domain}',
            '<p>Dear {$client_name},</p>'
            . '<p>Your ACME Subscription service <strong>#{$service_id}</strong> is approaching its expiration date in <strong>{$expireDaysLeft} days</strong>.</p>'
            . '<p>A renewal invoice has been issued for your service. Please ensure it is paid on time to avoid any interruption to your subscription.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteSubscriptionExpirationTemplate()
    {
        self::deleteTemplate(self::SUBSCRIPTION_EXPIRATION_TEMPLATE_ID);
    }

    public static function createSubscriptionRenewalTemplate()
    {
        self::createTemplate(
            self::SUBSCRIPTION_RENEWAL_TEMPLATE_ID,
            'ACME Subscription Renewed',
            '<p>Dear {$client_name},</p>'
            . '<p>Your renewal invoice has been paid and your ACME Subscription service <strong>#{$service_id}</strong> has been successfully renewed.</p>'
            . '<p>Thank you for your continued use of our service.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function updateSubscriptionRenewalTemplate()
    {
        self::updateTemplate(
            self::SUBSCRIPTION_RENEWAL_TEMPLATE_ID,
            'ACME Subscription Renewed',
            '<p>Dear {$client_name},</p>'
            . '<p>Your renewal invoice has been paid and your ACME Subscription service <strong>#{$service_id}</strong> has been successfully renewed.</p>'
            . '<p>Thank you for your continued use of our service.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteSubscriptionRenewalTemplate()
    {
        self::deleteTemplate(self::SUBSCRIPTION_RENEWAL_TEMPLATE_ID);
    }

    public static function createSubscriptionRenewalNoticeTemplate()
    {
        self::createTemplate(
            self::SUBSCRIPTION_RENEWAL_NOTICE_TEMPLATE_ID,
            'Subscription renewal',
            '<p>Dear {$client_name},</p>'
            . '<p>We would like to inform that your subscription service #{$service_id} is going to be renewed in {$expireDaysLeft} days.</p>'
            . '<p>{$signature}</p>'
        );
    }

    public static function deleteSubscriptionRenewalNoticeTemplate()
    {
        self::deleteTemplate(self::SUBSCRIPTION_RENEWAL_NOTICE_TEMPLATE_ID);
    }
}
