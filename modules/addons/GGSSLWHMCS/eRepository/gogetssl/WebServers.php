<?php

namespace MGModule\GGSSLWHMCS\eRepository\gogetssl;

use Exception;

class WebServers {
    public static function getAll($id) {
        $webServers = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getWebServers($id);
        return $webServers = $webServers['webservers'];
    }
}
