<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use MGModule\SSLCENTERWHMCS as main;
use Exception;

class GenerateCSR
{
    private $params;
    private $post;

    function __construct(&$params, &$post)
    {
        $this->params = &$params;
        $this->post   = &$post;
    }

    public function run()
    {
        try
        {
            return $this->GenerateCSR();
        }
        catch (\Exception $ex)
        {
            return json_encode(
                    [
                        'success' => 0,
                        'msg'     => main\mgLibs\Lang::getInstance()->T($ex->getMessage()),
                    ]
            );
        }
    }

    private function validateForm()
    {
        /* if(!preg_match("/^([-a-z0-9]{2,100})\.([a-z\.]{2,8})$/i", $this->post['commonName'])) {
          throw new Exception('invalidCommonName');
          } */
        if (!filter_var($this->post['emailAddress'], FILTER_VALIDATE_EMAIL))
        {
            throw new Exception('invalidEmailAddress');
        }
        if (!preg_match("/^[A-Z]{2}$/i", $this->post['countryName']))
        {
            throw new Exception('invalidCountryCode');
        }
    }

    private function GenerateCSR()
    {

        $this->validateForm();

        $dn      = array(
            'countryName'            => strtoupper($this->post['countryName']),
            'stateOrProvinceName'    => $this->post['stateOrProvinceName'],
            'localityName'           => $this->post['localityName'],
            'organizationName'       => $this->post['organizationName'],
            'organizationalUnitName' => $this->post['organizationalUnitName'],
            'commonName'             => $this->post['commonName'],
            'emailAddress'           => $this->post['emailAddress'],
        );
        $privKey = openssl_pkey_new();
        if ($privKey)
        {
            $serviceid = $this->params['serviceid'];
            if($serviceid == NULL)
            {
                $serviceid = $this->post['serviceID'];
            }
            
            $saveToDatabase = true;
            if(isset($this->post['doNotSaveToDatabase']) && $this->post['doNotSaveToDatabase'])
                $saveToDatabase = false;

            openssl_pkey_export($privKey, $pKeyOut);
            if($saveToDatabase)
                $this->savePrivateKeyToDatabase($serviceid, $pKeyOut);

            $csr = openssl_csr_new($dn, $privKey);

            if (!$csr)
            {
                throw new Exception('csrCodeGeneraterFailed');
            }

            openssl_csr_export($csr, $csrOut);
        }
        else
        {
            throw new Exception('csrCodeGeneraterFailed');
        }

        return json_encode(
                [
                    'success'     => 1,
                    'msg'         => main\mgLibs\Lang::getInstance()->T('csrCodeGeneraterdSuccessfully'),
                    'public_key'  => $csrOut,
                    'private_key' => encrypt($pKeyOut)
                ]
        );
    }

    public function savePrivateKeyToDatabase($serviceid, $privKey)
    {
        try
        {
            $sslRepo = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();

            $sslService = $sslRepo->getByServiceId((int) $serviceid);
           
            $sslService->setConfigdataKey('private_key', encrypt($privKey));
            $sslService->save();
        }
        catch (\Exception $ex)
        {
            throw new Exception('csrCodeGeneraterFailed');
        }
    }
}
