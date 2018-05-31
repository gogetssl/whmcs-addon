<?php

namespace MGModule\GGSSLWHMCS\eServices;

class EmailTemplateService {
    
    const CONFIGURATION_TEMPLATE_ID = 'GoGetSSL - Configuration Required';
    const EXPIRATION_TEMPLATE_ID = 'GoGetSSL - Service Expiration';
    const SEND_CERTIFICATE_TEMPLATE_ID = 'GoGetSSL - Send Certificate';

    
    public static function createConfigurationTemplate() {
        if(!is_null(self::getTemplate(self::CONFIGURATION_TEMPLATE_ID))) {
            return 'Template exist, nothing to do here';
        }
        $newTemplate          = new \MGModule\GGSSLWHMCS\eModels\whmcs\EmailTemplate();
        $newTemplate->type    = 'product';
        $newTemplate->name    = self::CONFIGURATION_TEMPLATE_ID;
        $newTemplate->subject = 'SSL Certificate - configuration required';
        $newTemplate->message = '<p>Dear {$client_name},</p><p>Thank you for your order for an SSL Certificate. Before you can use your certificate, it requires configuration which can be done at the URL below.</p><p>{$ssl_configuration_link}</p><p>Instructions are provided throughout the process but if you experience any problems or have any questions, please open a ticket for assistance.</p><p>{$signature}</p>';
        $newTemplate->custom  = 1;
        $newTemplate->save();
    }
    
    public static function deleteConfigurationTemplate() {
        $template = self::getTemplate(self::CONFIGURATION_TEMPLATE_ID);
        if(is_null($template)) {
            return 'Template not exist, nothing to do here';
        }
        $template->delete();
    }
    
    public static function createCertyficateTemplate() {
        if(!is_null(self::getTemplate(self::SEND_CERTIFICATE_TEMPLATE_ID))) {
            return 'Template exist, nothing to do here';
        }
        $newTemplate          = new \MGModule\GGSSLWHMCS\eModels\whmcs\EmailTemplate();
        $newTemplate->type    = 'product';
        $newTemplate->name    = self::SEND_CERTIFICATE_TEMPLATE_ID;
        $newTemplate->subject = 'SSL Certificate';
        $newTemplate->message = '<p>Dear {$client_name},</p><p>{$ssl_certyficate}</p><p>{$signature}</p>';
        $newTemplate->custom  = 1;
        $newTemplate->save();
    }
    
    public static function deleteCertyficateTemplate() {
        $template = self::getTemplate(self::SEND_CERTIFICATE_TEMPLATE_ID);
        if(is_null($template)) {
            return 'Template not exist, nothing to do here';
        }
        $template->delete();
    }
    
    public static function getTemplate($name) {
        return \MGModule\GGSSLWHMCS\eModels\whmcs\EmailTemplate::whereName($name)->first();
    }
    
     
    public static function createExpireNotificationTemplate() {
        if(!is_null(self::getTemplate(self::EXPIRATION_TEMPLATE_ID))) {
            return 'Template exist, nothing to do here';
        }
        $newTemplate          = new \MGModule\GGSSLWHMCS\eModels\whmcs\EmailTemplate();
        $newTemplate->type    = 'product';
        $newTemplate->name    = self::EXPIRATION_TEMPLATE_ID;
        $newTemplate->subject = 'Service Expiration Notification - {$service_domain}';
        $newTemplate->message = '<p>Dear {$client_name},</p><p>We would like to inform You about your service <strong>#{$service_id}</strong>  is going to expire in {$expireDaysLeft} days.</p><p>{$signature}</p>';
        $newTemplate->custom  = 1;
        $newTemplate->save();
    }
    
    public static function deleteExpireNotificationTemplate() {
        $template = self::getTemplate(self::EXPIRATION_TEMPLATE_ID);
        if(is_null($template)) {
            return 'Template not exist, nothing to do here';
        }
        $template->delete();
    }
}
