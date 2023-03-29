<?php

namespace MGModule\SSLCENTERWHMCS\eRepository\sslcenter;

use Exception;
use MGModule\SSLCENTERWHMCS\eServices\provisioning\ConfigOptions;

class San {

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
        return \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('sansTitle');
    }

    public static function getFields($limit, $limitwildcard = 0, $params) {

        $sanEnabledForWHMCSProduct = $params[ConfigOptions::PRODUCT_ENABLE_SAN] === 'on';
        $sanWildcardEnabledForWHMCSProduct = $params[ConfigOptions::PRODUCT_ENABLE_SAN_WILDCARD] === 'on';

        $fields                 = [];

        if($sanEnabledForWHMCSProduct == 'on') {
            $fields['sans_domains'] = [
                'FriendlyName' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('sansFreindlyName') . sprintf(' (%s)', $limit),
                'Type' => 'textarea',
                'Size' => '30',
                'Description' => '<br>' . \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('sansDescription'),
                'Required' => false,

            ];
        }
        if($sanWildcardEnabledForWHMCSProduct == 'on') {
            $fields['wildcard_san'] = [
                'FriendlyName' => \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('wildcardSansFreindlyName') . sprintf(' (%s)', $limitwildcard),
                'Type' => 'textarea',
                'Size' => '30',
                'Description' => '<br>' . \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('sansDescription'),
                'Required' => false,

            ];
        }
        return $fields;
    }
}
