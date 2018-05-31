<?php

namespace MGModule\GGSSLWHMCS\eHelpers;

class Exception {

    public static function e($ex) {
         
        if($_SESSION['adminid']) {
            return $ex->getMessage();
        }
        
        $class = get_class($ex);

        if ($class === 'MGModule\GGSSLWHMCS\mgLibs\GoGetSSLException') {
            return \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('anErrorOccurred');
        }
        
        if ($class === 'MGModule\GGSSLWHMCS\mgLibs\GoGetSSLApiException') {
            return \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('anErrorOccurred');
        }

        if ($class === 'Exception') {
            return $ex->getMessage();
        }
    }

}
