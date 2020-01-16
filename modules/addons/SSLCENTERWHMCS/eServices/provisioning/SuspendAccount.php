<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class SuspendAccount
{
    private $p;

    function __construct(&$params)
    {
        $this->p = &$params;
    }

    public function run()
    {
        try
        {
            $this->SuspendAccount();
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
        return 'success';
    }

    public function SuspendAccount()
    {
        $status = $this->p['status'];
        
        if ($status == 'Suspended')
        {
            throw new Exception('Already suspended');
        }
    }
}
