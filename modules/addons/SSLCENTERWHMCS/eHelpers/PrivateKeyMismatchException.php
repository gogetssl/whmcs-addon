<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

/**
 * The stored private key does not belong to the certificate. Retrying cannot help:
 * only a reissue (new certificate) or another key fixes it.
 */
class PrivateKeyMismatchException extends \Exception
{
    const MESSAGE = 'The private key stored in WHMCS does not match the certificate (it belongs to another CSR). '
        . 'Reissue the certificate with a CSR generated in WHMCS, or install it manually with the private key of its CSR.';

    public function __construct($message = self::MESSAGE)
    {
        parent::__construct($message);
    }
}
