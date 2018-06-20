<?php

namespace MGModule\GGSSLWHMCS\eRepository\gogetssl;

use Exception;

class Organization {

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
        return \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationTitle');
    }

    public static function getFields() {
        $org                     = [];
        $org['org_name']         = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationName'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => true,
        ];
        $org['org_division']     = [
            'FriendlyName' => 'Division',
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationDivision'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => true
            
        ];
        $org['org_duns']         = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationDuns'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => false
        ];
        $org['org_addressline1'] = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationAddress'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => true
        ];
        $org['org_city']         = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationCity'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => true
        ];
        $org['org_country']      = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationCountry'),
            'Type'         => 'dropdown',
            'Description'  => '',
            'Required'     => true,
            'Options'      => \MGModule\GGSSLWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountriesForWhmcsDropdownOptions(),
        ];
        $org['org_fax']          = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationFax'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => false
        ];
        $org['org_phone']        = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationPhoneNumber'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => true
        ];
        $org['org_postalcode']   = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationZipCode'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => true
        ];
        $org['org_regions']       = [
            'FriendlyName' => \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('confOrganizationStateRegion'),
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => '',
            'Required'     => true
        ];
        return $org;
    }
}
