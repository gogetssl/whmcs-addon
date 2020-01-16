<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class CreateAccount {

    private $p;

    function __construct(&$params) {
        $this->p = &$params;

    }

    public function run() {
        try {
            $this->CreateAccount();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return 'success';

    }

    public function CreateAccount() {
        $repo       = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $repo->getByServiceId($this->p['serviceid']);

        if (!is_null($serviceSSL)) {
            throw new Exception('Already created');
        }

        $sslModel                 = new \MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL();
        $sslModel->userid         = $this->p['clientsdetails']['userid'];
        $sslModel->serviceid      = $this->p['serviceid'];
        $sslModel->remoteid       = '';
        $sslModel->module         = 'SSLCENTERWHMCS';
        $sslModel->certtype       = '';
        $sslModel->completiondate = '';
        $sslModel->status         = 'Awaiting Configuration';        
        $sslModel->save();

        sendMessage(\MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::CONFIGURATION_TEMPLATE_ID, $this->p['serviceid'], [
            'ssl_configuration_link' => \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Config::getInstance()->getConfigureSSLLink($sslModel->id, $sslModel->serviceid ),
            'ssl_configuration_url'  => \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Config::getInstance()->getConfigureSSLUrl($sslModel->id, $sslModel->serviceid ),
        ]);

    }

}
