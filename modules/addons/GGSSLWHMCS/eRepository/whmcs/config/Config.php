<?php

namespace MGModule\GGSSLWHMCS\eRepository\whmcs\config;

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

    public function getConfigureSSLUrl($id) {
        return $this->config["SystemURL"] . '/configuressl.php?cert=' . md5($id);
    }

    public function getConfigureSSLLink($id, $text = null) {
        $url = $this->getConfigureSSLUrl($id);
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
