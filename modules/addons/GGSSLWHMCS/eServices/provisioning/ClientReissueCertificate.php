<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class ClientReissueCertificate {

    // allow *.domain.com as SAN for products
    const PRODUCTS_WITH_ADDITIONAL_SAN_VALIDATION = array(100, 99, 63);
    /**
     *
     * @var array 
     */
    private $p;

    /**
     *
     * @var array 
     */
    private $get;

    /**
     *
     * @var array 
     */
    private $post;

    /**
     *
     * @var array 
     */
    private $vars;

    /**
     *
     * @var \MGModule\GGSSLWHMCS\eModels\whmcs\service\SSL 
     */
    private $sslService;

    /**
     *
     * @var array 
     */
    private $orderStatus;

    const STEP_ONE = 'pages/reissue/stepOne';
    const STEP_TWO = 'pages/reissue/stepTwo';
    const SUCCESS  = 'pages/reissue/stepSuccess';

    function __construct(&$params, &$post, &$get) {
        $this->p              = &$params;
        $this->get            = &$get;
        $this->post           = &$post;
        $this->vars           = [];
        $this->vars['errors'] = []; 

    }
    public function run() {
        
        $this->setMainDomainDcvMethod($_POST); 
        $this->setSansDomainsDcvMethod($_POST); 
        return $this->miniControler();

    }

    private function miniControler() {
        
        try {
            $this->validateService();
        } catch (Exception $ex) {     
            return '- ' . \MGModule\GGSSLWHMCS\eHelpers\Exception::e($ex);
        }
        if (isset($this->post['stepOneForm'])) {           
            try {
                $this->stepOneForm();
                return $this->build(self::STEP_TWO);
            } catch (Exception $ex) {
                $this->vars['errors'][] = \MGModule\GGSSLWHMCS\eHelpers\Exception::e($ex);
            }
        }
        

        if (isset($this->post['stepTwoForm'])) {
            try {
                $this->stepTwoForm();
                return $this->build(self::SUCCESS);
            } catch (Exception $ex) {
                $this->vars['errors'][] = \MGModule\GGSSLWHMCS\eHelpers\Exception::e($ex);
            }
        }
    
        $this->loadServerList(); 
        $this->vars['sansLimit'] = $this->getSansLimit();       
        
        return $this->build(self::STEP_ONE);

    }

    private function setMainDomainDcvMethod($post) {
        $this->post['dcv_method']  = $post['dcvmethodMainDomain']; 
    }

    private function setSansDomainsDcvMethod($post) {
        
        if(isset($post['dcvmethod']) && is_array($post['dcvmethod'])) {            
            $this->post['sansDomansDcvMethod'] = $post['dcvmethod'];
        }
    }
    
    private function stepOneForm() {
        $this->validateWebServer();
        $this->validateSanDomains();
        $decodeCSR = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->post['csr']);
        if ($decodeCSR['error']) {
            if(isset($decodeCSR['description']))
                throw new Exception($decodeCSR['description']);
            
            throw new Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('incorrectCSR'));
        }
        $mainDomain                   = $decodeCSR['csrResult']['CN'];
        $domains                      = $mainDomain . PHP_EOL . $this->post['sans_domains'];
        $parseDomains                 = \MGModule\GGSSLWHMCS\eHelpers\SansDomains::parseDomains(strtolower($domains));
        $SSLStepTwoJS                 = new SSLStepTwoJS($this->p);
        $this->vars['approvalEmails'] = json_encode($SSLStepTwoJS->fetchApprovalEmailsForSansDomains($parseDomains));
        $this->vars['brand'] = json_encode($this->getCertificateBrand());
    }
    
    private function stepTwoForm() {
        $data['dcv_method'] = strtolower($this->post['dcv_method']);
        $data['webserver_type'] = $this->post['webservertype'];
        $data['approver_email'] = ($data['dcv_method'] == 'email') ? $this->post['approveremail'] : '';
        $data['csr'] = $this->post['csr'];        
        
        $brandsWithOnlyEmailValidation = ['geotrust','thawte','rapidssl','symantec'];   
        
        $sansDomains = [];
        
        $this->validateWebServer();
        
        $decodedCSR   = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($this->post['csr']);
        if ($this->getSansLimit() AND count($_POST['approveremails'])) {
            $this->validateSanDomains();
            $sansDomains             = \MGModule\GGSSLWHMCS\eHelpers\SansDomains::parseDomains($this->post['sans_domains']);
            //if entered san is the same as main domain
            if(count($sansDomains) != count($_POST['approveremails'])) {
                foreach($sansDomains as $key => $domain) {                    
                    if($decodedCSR['csrResult']['CN'] == $domain) {
                        unset($sansDomains[$key]);   
                    }                     
                }
            }
            $data['dns_names']       = implode(',', $sansDomains);
            $data['approver_emails'] = implode(',', $_POST['approveremails']);
            
            if(!empty($sanDcvMethods = $this->getSansDomainsValidationMethods())) {
                $i = 0;
                foreach($_POST['approveremails'] as $domain => $approveremail) {
                    if($sanDcvMethods[$i] != 'EMAIL') {
                        $_POST['approveremails']["$domain"] = strtolower($sanDcvMethods[$i]);
                    }
                    $i++;
                }
                $data['approver_emails'] = implode(',', $_POST['approveremails']);
            } 
        }
        
        //if brand is 'geotrust','thawte','rapidssl','symantec' do not send dcv method for sans        
        if(in_array($brand, $brandsWithOnlyEmailValidation)) {
            unset($data['approver_emails']);
        }

        $orderStatus = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($this->sslService->remoteid);        
        
        if (count($sansDomains) > $orderStatus['total_domains'] AND $orderStatus['total_domains'] >= 0) {
            $count = count($sansDomains) - $orderStatus['total_domains'];
            \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->addSslSan($this->sslService->remoteid, $count);
        }
        
        \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->reIssueOrder($this->sslService->remoteid, $data);

        $this->sslService->setConfigdataKey('servertype', $data['webserver_type']);
        $this->sslService->setConfigdataKey('csr', $data['csr']);
        $this->sslService->setConfigdataKey('approveremail', $data['approver_email']);
        $this->sslService->setConfigdataKey('private_key', '');
        $this->sslService->setApproverEmails($data['approver_emails']);
        $this->sslService->setSansDomains($data['dns_names']);
        $this->sslService->save();

    }
    
    private function getSansDomainsValidationMethods() {  
        $data = [];
        foreach ($this->post['sansDomansDcvMethod'] as $newMethod) { 
            $data[] = $newMethod;   
        }
        return $data;
    }
    
    private function validateWebServer() {
        if($this->post['webservertype'] == 0) {
            throw new Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('mustSelectServer'));
        }
    }
    
    
    private function getCertificateBrand()
    {
        if(!empty($this->p[ConfigOptions::API_PRODUCT_ID])) {
            $apiRepo       = new \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products();
            $apiProduct    = $apiRepo->getProduct($this->p[ConfigOptions::API_PRODUCT_ID]);
            return $apiProduct->brand;
        }
    }

    private function validateSanDomains() {
        $sansDomains = $this->post['sans_domains'];
        $sansDomains = \MGModule\GGSSLWHMCS\eHelpers\SansDomains::parseDomains($sansDomains);
        
        $apiProductId     = $this->p[ConfigOptions::API_PRODUCT_ID];
        
        $invalidDomains = \MGModule\GGSSLWHMCS\eHelpers\Domains::getInvalidDomains($sansDomains, in_array($apiProductId, self::PRODUCTS_WITH_ADDITIONAL_SAN_VALIDATION));
        if (count($invalidDomains)) {
            throw new Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('incorrectSans') . implode(', ', $invalidDomains));
        }

        $includedSans = $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        $sansLimit    = $this->getSansLimit();
        if (count($sansDomains) > $sansLimit) {
            throw new Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('exceededLimitOfSans'));
        }

    }

    private function validateService() {
        $ssl              = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $this->sslService = $ssl->getByServiceId($this->p['serviceid']);        
        if (is_null($this->sslService)) {
            throw new Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('createNotInitialized'));
        }

        $this->orderStatus = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($this->sslService->remoteid);

        if ($this->orderStatus['status'] !== 'active') {
            throw new Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('notAllowToReissue'));
        }

    }

    private function loadServerList() {
        $apiWebServers = \MGModule\GGSSLWHMCS\eServices\FlashService::get('GGSSLWHMCS_SERVER_LIST_' . \MGModule\GGSSLWHMCS\eServices\provisioning\ConfigOptions::API_PRODUCT_ID);

        if (!is_null($apiWebServers)) {
            $this->vars['webServers'] = $apiWebServers;
            return;
        }

        try {
            $apiRepo                  = new \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products();
            $apiProduct               = $apiRepo->getProduct($this->p[\MGModule\GGSSLWHMCS\eServices\provisioning\ConfigOptions::API_PRODUCT_ID]);
            $apiWebServers            = \MGModule\GGSSLWHMCS\eRepository\gogetssl\WebServers::getAll($apiProduct->getWebServerTypeId());
            $this->vars['webServers'] = $apiWebServers;
            \MGModule\GGSSLWHMCS\eServices\FlashService::set('GGSSLWHMCS_SERVER_LIST_' . \MGModule\GGSSLWHMCS\eServices\provisioning\ConfigOptions::API_PRODUCT_ID, $apiWebServers);
        } catch (Exception $ex) {
            $this->vars['errors'][] .= \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('canNotFetchWebServer');
        }

    }

    private function build($template) {
        $this->vars['error'] = implode('<br>', $this->vars['errors']);
        $content             = \MGModule\GGSSLWHMCS\eServices\TemplateService::buildTemplate($template, $this->vars);
        return [
            'templatefile' => 'main',
            'vars'         => ['content' => $content],
        ];

    }

    private function getSansLimit() {
        $sanEnabledForWHMCSProduct = $this->p[ConfigOptions::PRODUCT_ENABLE_SAN] === 'on';
        if (!$sanEnabledForWHMCSProduct) {
            return 0;
        }
        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = (int) $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        return $includedSans + $boughtSans;

    }

}
