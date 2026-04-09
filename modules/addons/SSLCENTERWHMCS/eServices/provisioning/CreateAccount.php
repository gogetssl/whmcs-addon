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
        $isAcmeSubscriptionProduct = \MGModule\SSLCENTERWHMCS\eHelpers\AcmeSubscription::isAcmeByServiceParams($this->p);

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

        if ($isAcmeSubscriptionProduct)
        {
            (new \MGModule\SSLCENTERWHMCS\models\acmeSubscription\Repository())->upsertByServiceId($this->p['serviceid'], [
                'client_id' => $this->p['clientsdetails']['userid'],
                'status'    => 'pending',
                'auto_renew'=> 1,
            ]);

            sendMessage(\MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::SUBSCRIPTION_TEMPLATE_ID, $this->p['serviceid'], []);
            return;
        }

        sendMessage(\MGModule\SSLCENTERWHMCS\eServices\EmailTemplateService::CONFIGURATION_TEMPLATE_ID, $this->p['serviceid'], [
            'ssl_configuration_link' => \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Config::getInstance()->getConfigureSSLLink($sslModel->id, $sslModel->serviceid ),
            'ssl_configuration_url'  => \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Config::getInstance()->getConfigureSSLUrl($sslModel->id, $sslModel->serviceid ),
        ]);

    }

}
