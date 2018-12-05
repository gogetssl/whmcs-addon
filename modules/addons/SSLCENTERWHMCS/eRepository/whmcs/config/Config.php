<?php

namespace MGModule\SSLCENTERWHMCS\eRepository\whmcs\config;

class Config {

    private static $instance;
    private $config;
            
    function __construct() {
        GLOBAL $CONFIG;
        $this->config = &$CONFIG;
    }

    /**
     * 
     * @return Config
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    public function getConfigureSSLUrl($id, $serviceID = null) {
        $WHMCSUrl = (function_exists('MultibrandFunctionalityAutoLoader') ? \MGModule\SSLCENTERWHMCS\eHelpers\Multibrand::getBrandData($serviceID)['systemURL'] : $this->config["SystemURL"]);
       
        return $WHMCSUrl . '/configuressl.php?cert=' . md5($id);
    }


    public function getConfigureSSLLink($id, $serviceID = null, $text = null) {
        $url = $this->getConfigureSSLUrl($id, $serviceID);
        if ($text === null) {
            $text = $url;
        }
        return sprintf('<a href="%s">%s</a>', $url, $text);
    }
    
    public function getVersionMajor() {
        $exp = explode('.', $this->config['Version']);
        return (int) $exp[0];
    }

}
