<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\clientarea;

use MGModule\SSLCENTERWHMCS\mgLibs\process\AbstractController;

/**
 * Description of home
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Home extends AbstractController {
    
    public function indexHTML($input = []){
        return ['tpl' => 'home', 'vars' => []];
    }

    public function generateCSRJSON($input = []){

        $dn = [
            'countryName'            => strtoupper($input['country']),
            'stateOrProvinceName'    => $input['state'],
            'localityName'           => $input['locality'],
            'organizationName'       => $input['organization'],
            'organizationalUnitName' => $input['organizationUnit'],
            'commonName'             => $input['domain'],
            'emailAddress'           => $input['email']
        ];

        $privKey = openssl_pkey_new(["private_key_bits" => 2048,"private_key_type" => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($privKey, $pKeyOut);
        $csr = openssl_csr_new($dn, $privKey, ['digest_alg' => 'sha256']);
        openssl_csr_export($csr, $csrOut);

        echo json_encode(
            [
                'public_key'  => $csrOut,
                'private_key' => encrypt($pKeyOut)
            ]
        );
        die();

    }
}
