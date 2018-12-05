<?php

namespace MGModule\SSLCENTERWHMCS\eProviders;

use Exception;

class ApiProvider {

    /**
     *
     * @var type 
     */
    private static $instance;
    
    /**
     *
     * @var \MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterApi 
     */
    private $api;

    /**
     * @return ApiProvider
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ApiProvider();
        }
        return self::$instance;
    }

    /**
     * @return \MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterApi
     */
    public function getApi($exception = true) {
        if ($this->api === null) {
            $this->initApi();
        }
        
        if($exception) {
            $this->api->setSSLCenterApiException(); 
        } else {
            $this->api->setNoneException();
        }
        
        return $this->api;
    }

    /**
     * @throws Exception
     */
    private function initApi() {
        new \MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterApi(); // need fix and remove that line xD
        $apiData = $this->getCredencials();
        $this->api = new \MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterApi();
        $this->api->auth($apiData->api_login, $apiData->api_password);
    }
    
    private function getCredencials() {
        $apiConfigRepo = new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository();
        $apiData       = $apiConfigRepo->get();
        if (empty($apiData->api_login) || empty($apiData->api_password)) {
            throw new \MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterException('api_configuration_empty');
        }
        return $apiData;
    }
}
