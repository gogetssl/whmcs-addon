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
        return 'SANs';
    }

    public static function getFields($limit) {
        $fields                 = [];
        $fields['sans_domains'] = [
            'FriendlyName' => 'SAN Domains' . sprintf(' (%s)', $limit),
            'Type'         => 'textarea',
            'Size'         => '30',
            'Description'  => '<br>If you want add any SANs put them here (every SAN in separate line)',
            'Required'     => false,

        ];
        return $fields;
    }
}
