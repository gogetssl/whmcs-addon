<?php

namespace MGModule\GGSSLWHMCS\eRepository\whmcs\service;

class SSLTemplorary {

    private static $instance;
    private $ssl = [];

    /**
     * @return SSLTemplorary
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SSLTemplorary();
        }
        return self::$instance;
    }

    public function setByParams(&$params) {
        if ($params['moduletype'] !== 'GGSSLWHMCS') {
            return;
        }
        $this->set($_GET['cert'], true);
    }

    public function set($md5, $conf) {
        $this->ssl[$md5] = $conf;
    }

    public function get($md5) {
        return $this->ssl[$md5];
    }
}
