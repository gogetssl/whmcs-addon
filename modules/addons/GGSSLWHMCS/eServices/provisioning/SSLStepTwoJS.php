<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class SSLStepTwoJS {

    private $p;
    private $domainsEmailApprovals = [];
    private $brand = '';

    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        
        if (!$this->canRun()) {
            return '';
        }

        if (!$this->isValidModule()) {
            return '';
        }
        try {
            $this->setBrand($_POST);
            $this->SSLStepTwoJS();
            return \MGModule\GGSSLWHMCS\eServices\ScriptService::getSanEmailsScript(json_encode($this->domainsEmailApprovals), json_encode(\MGModule\GGSSLWHMCS\eServices\FlashService::getFieldsMemory($_GET['cert'])), json_encode($this->brand));
        } catch (Exception $ex) {
            return '';
        }

    }

    private function canRun() {
        if ($this->p['filename'] !== 'configuressl') {
            return false;
        }
        if ($_GET['step'] != 2) {
            return false;
        }
        return true;
    }    

    private function setBrand($params) {
        if(isset($params['brand']) &&  $params['brand'] != null){
            $this->$params['brand'];
        }
    }
    
    private function isValidModule() {
        return \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSLTemplorary::getInstance()->get($_GET['cert']) === true;

    }

    private function SSLStepTwoJS() {
        $decodedCSR   = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($_POST['csr']);
        if($decodedCSR['error']) {
            throw new Exception('Incorrect CSR');
        }
        $mainDomain       = $decodedCSR['csrResult']['CN'];
        $domains = $mainDomain . PHP_EOL . $_POST['fields']['sans_domains'];
        $sansDomains = \MGModule\GGSSLWHMCS\eHelpers\SansDomains::parseDomains(strtolower($domains));
        $this->fetchApprovalEmailsForSansDomains($sansDomains);
    }

    public function fetchApprovalEmailsForSansDomains($sansDomains) {
        foreach ($sansDomains as $sansDomain) {
            $apiDomainEmails             = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($sansDomain);
            $this->domainsEmailApprovals[$sansDomain] = $apiDomainEmails['ComodoApprovalEmails'];
        }
        return $this->domainsEmailApprovals;
    }
}
