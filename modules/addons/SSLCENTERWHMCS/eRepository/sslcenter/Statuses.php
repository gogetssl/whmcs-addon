<?php

namespace MGModule\SSLCENTERWHMCS\eRepository\sslcenter;

use Exception;

class WebServers {
    public static function getAll($id) {
        $webServers = \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getWebServers($id);
        return $webServers = $webServers['webservers'];
    }
}
