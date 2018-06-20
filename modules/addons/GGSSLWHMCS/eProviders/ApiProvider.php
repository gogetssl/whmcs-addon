<?php

namespace MGModule\GGSSLWHMCS\eProviders;

use Exception;

class ApiProvider {

    /**
     *
     * @var type 
     */
    private static $instance;
    
    /**
     *
     * @var \MGModule\GGSSLWHMCS\mgLibs\GoGetSSLApi 
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
     * @return \MGModule\GGSSLWHMCS\mgLibs\GoGetSSLApi
     */
    public function getApi($exception = true) {
        if ($this->api === null) {
            $this->initApi();
        }
        
        if($exception) {
            $this->api->setGoGetSSLApiException(); 
        } else {
            $this->api->setNoneException();
        }
        
        return $this->api;
    }

    /**
     * @throws Exception
     */
    private function initApi() {
        new \MGModule\GGSSLWHMCS\mgLibs\GoGetSSLApi(); // need fix and remove that line xD
        $apiData = $this->getCredencials();
        $this->api = new \MGModule\GGSSLWHMCS\mgLibs\GoGetSSLApi();
        $this->api->auth($apiData->api_login, $apiData->api_password);
    }
    
    private function getCredencials() {
        $apiConfigRepo = new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository();
        $apiData       = $apiConfigRepo->get();
        if (empty($apiData->api_login) || empty($apiData->api_password)) {
            throw new \MGModule\GGSSLWHMCS\mgLibs\GoGetSSLException('api_configuration_empty');
        }
        return $apiData;
    }
}
