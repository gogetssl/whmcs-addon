<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

class Exception {

    public static function e($ex) {
         
        if($_SESSION['adminid']) {
            return $ex->getMessage();
        }
        
        $class = get_class($ex);

        if ($class === 'MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterException') {
            return \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('anErrorOccurred');
        }
        
        if ($class === 'MGModule\SSLCENTERWHMCS\mgLibs\SSLCenterApiException') {
            return \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('anErrorOccurred');
        }

        if ($class === 'Exception') {
            return $ex->getMessage();
        }
    }

}
