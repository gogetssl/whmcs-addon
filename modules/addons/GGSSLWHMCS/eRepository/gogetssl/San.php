<?php

namespace MGModule\GGSSLWHMCS\eRepository\gogetssl;

use Exception;

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
        return \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('sansTitle');
    }

    public static function getFields($limit) {
        $fields                 = [];
        $fields['sans_domains'] = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('sansFreindlyName') . sprintf(' (%s)', $limit),
            'Type'         => 'textarea',
            'Size'         => '30',
            'Description'  => '<br>' . \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('sansDescription'),
            'Required'     => false,

        ];
        return $fields;
    }
}
