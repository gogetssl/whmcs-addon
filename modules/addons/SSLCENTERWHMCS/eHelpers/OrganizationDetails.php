<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

/**
 * What the customer submitted at enrollment (organization of an OV/EV order, administrative and technical contact),
 * kept on the service (configdata) and shown on the certificate pages. The API is only a fallback for orders placed
 * before this data was stored.
 */
class OrganizationDetails
{
    const CONFIGDATA_KEY = 'org_details';

    private static $fields = ['org_name', 'org_division', 'org_addressline1', 'org_city', 'org_region', 'org_postalcode', 'org_country', 'org_phone'];

    private static $contactFields = [
        'admin_firstname', 'admin_lastname', 'admin_email', 'admin_phone', 'admin_organization', 'admin_title',
        'tech_firstname',  'tech_lastname',  'tech_email',  'tech_phone',  'tech_organization',  'tech_title',
    ];

    /**
     * @param array $order the order parameters sent to the API (org_* keys)
     * @return array|null null when the order has no organization
     */
    public static function fromOrder(array $order)
    {
        if (!isset($order['org_name']) || trim((string) $order['org_name']) === '') {
            return null;
        }

        return self::pick($order);
    }

    /**
     * Orders placed before the submitted organization was stored: the organization address the API order status
     * returns (it has no organization name).
     *
     * @return array|null null when the status has no organization data (DV)
     */
    public static function fromApiStatus(array $status)
    {
        $details = self::pick($status);
        foreach (['org_addressline1', 'org_city', 'org_postalcode', 'org_country'] as $field) {
            if ($details[$field] !== '') {
                return $details;
            }
        }

        return null;
    }

    /**
     * @param mixed $sslService service with getConfigdataKey()
     * @return array|null
     */
    public static function getOfService($sslService)
    {
        if (!$sslService || !method_exists($sslService, 'getConfigdataKey')) {
            return null;
        }
        $details = $sslService->getConfigdataKey(self::CONFIGDATA_KEY);
        if (is_object($details)) {
            $details = json_decode(json_encode($details), true);
        }
        if (!is_array($details)) {
            return null;
        }
        $details = self::pick($details);

        return implode('', $details) !== '' ? $details : null;
    }

    /**
     * Store the submitted administrative and technical contact on the service.
     *
     * @param mixed $sslService service with setConfigdataKey()
     */
    public static function storeContacts($sslService, array $order)
    {
        foreach (self::$contactFields as $field) {
            $sslService->setConfigdataKey($field, isset($order[$field]) ? trim((string) $order[$field]) : '');
        }
    }

    /**
     * Fill in contact fields the service does not have yet from the API order status; submitted values are kept.
     *
     * @return bool true when something was filled in
     */
    public static function fillMissingContacts($sslService, array $status)
    {
        $changed = false;
        foreach (self::$contactFields as $field) {
            $current = $sslService->getConfigdataKey($field);
            if ((string) $current === '' && !empty($status[$field])) {
                $sslService->setConfigdataKey($field, $status[$field]);
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * Store the organization from the API order status when the service has none stored yet.
     *
     * @return bool true when it was stored
     */
    public static function fillMissingOrganization($sslService, array $status)
    {
        if (self::getOfService($sslService) !== null) {
            return false;
        }
        $details = self::fromApiStatus($status);
        if ($details === null) {
            return false;
        }
        $sslService->setConfigdataKey(self::CONFIGDATA_KEY, $details);

        return true;
    }

    /**
     * Address line for display: street, city, region, postal code, country (empty and "None" parts left out).
     */
    public static function describeAddress(array $details)
    {
        $parts = [];
        foreach (['org_addressline1', 'org_city', 'org_region', 'org_postalcode', 'org_country'] as $field) {
            $value = isset($details[$field]) ? trim((string) $details[$field]) : '';
            if ($value !== '' && strcasecmp($value, 'None') !== 0) {
                $parts[] = $value;
            }
        }

        return implode(', ', $parts);
    }

    private static function pick(array $source)
    {
        $details = [];
        foreach (self::$fields as $field) {
            $details[$field] = isset($source[$field]) && is_scalar($source[$field]) ? trim((string) $source[$field]) : '';
        }

        return $details;
    }
}
