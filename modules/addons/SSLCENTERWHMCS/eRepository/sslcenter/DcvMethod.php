<?php

namespace MGModule\SSLCENTERWHMCS\eRepository\sslcenter;

use Exception;

    
class DcvMethod {
    /**
     * Types:
     * 
     * * text
     * * password
     * * yesno
     * * dropdown
     * * radio
     * * textarea
     */
    public static function getTitle() {
        return 'Domain Control Validation';
    }
    public static function getFields($methodTypesArray) {
        $fields                 = [];
        $fields['dcv_method'] = [
            'FriendlyName' => 'DCV Type',
            'Type'         => 'dropdown',
            'Options'      => $methodTypesArray,
            'Required'     => true,

        ];
        return $fields;
    }
}
