<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

/**
 * Checks that the private key stored with a service belongs to its CSR / certificate.
 */
class PrivateKey
{
    /**
     * Stored keys are encrypted with WHMCS encrypt(); older entries may be plain PEM.
     */
    public static function decode($stored)
    {
        if (empty($stored)) {
            return '';
        }
        if (strpos($stored, '-----BEGIN') !== false) {
            return $stored;
        }
        return function_exists('decrypt') ? (string) decrypt($stored) : '';
    }

    /**
     * @return bool|null true = matches, false = does not match, null = could not be checked
     */
    public static function matchesCertificate($cert, $key)
    {
        if (empty($cert) || empty($key) || !function_exists('openssl_x509_check_private_key')) {
            return null;
        }
        $x509 = @openssl_x509_read($cert);
        if ($x509 === false) {
            return null;
        }
        $pkey = @openssl_pkey_get_private($key);
        if ($pkey === false) {
            return false;
        }
        return (bool) @openssl_x509_check_private_key($x509, $pkey);
    }

    /**
     * @return bool|null true = matches, false = does not match, null = could not be checked
     */
    public static function matchesCsr($csr, $key)
    {
        if (empty($csr) || empty($key) || !function_exists('openssl_csr_get_public_key')) {
            return null;
        }
        $csrPublic = @openssl_csr_get_public_key($csr);
        if ($csrPublic === false) {
            return null;
        }
        $pkey = @openssl_pkey_get_private($key);
        if ($pkey === false) {
            return false;
        }
        $csrDetails = openssl_pkey_get_details($csrPublic);
        $keyDetails = openssl_pkey_get_details($pkey);
        if (empty($csrDetails['key']) || empty($keyDetails['key'])) {
            return null;
        }
        return trim($csrDetails['key']) === trim($keyDetails['key']);
    }

    /**
     * Removes the stored private key when it does not belong to the CSR, e.g. when the
     * client switches to their own CSR and the key of an earlier WHMCS-generated CSR is
     * still stored. Does not save the service.
     *
     * @param object $sslService service with configdata and setConfigdataKey()
     * @return bool true when a key was removed
     */
    public static function dropIfNotMatchingCsr($sslService, $csr)
    {
        $configdata = (array) $sslService->configdata;
        if (empty($configdata['private_key']) || empty($csr)) {
            return false;
        }
        if (self::matchesCsr($csr, self::decode($configdata['private_key'])) === false) {
            $sslService->setConfigdataKey('private_key', '');
            return true;
        }
        return false;
    }
}
