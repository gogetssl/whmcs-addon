<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

/**
 * Organization details an OV/EV order was placed with, kept on the service (configdata) so the certificate page
 * can show them before the certificate is issued: the API order status does not return the organization name.
 */
class OrganizationDetails
{
    const CONFIGDATA_KEY = 'org_details';

    private static $fields = ['org_name', 'org_division', 'org_addressline1', 'org_city', 'org_region', 'org_postalcode', 'org_country', 'org_phone'];

    /**
     * @param array $order the order parameters sent to the API (org_* keys)
     * @return array|null null when the order has no organization
     */
    public static function fromOrder(array $order)
    {
        if (!isset($order['org_name']) || trim((string) $order['org_name']) === '') {
            return null;
        }
        $details = [];
        foreach (self::$fields as $field) {
            $details[$field] = isset($order[$field]) ? trim((string) $order[$field]) : '';
        }

        return $details;
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

        return is_array($details) && !empty($details['org_name']) ? $details : null;
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
}
