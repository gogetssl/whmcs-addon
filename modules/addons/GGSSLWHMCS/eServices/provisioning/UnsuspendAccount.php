<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class UnsuspendAccount
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
            $this->UnsuspendAccount();
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
        return 'success';
    }

    public function UnsuspendAccount()
    {
        $status = $this->p['status'];
        
        if ($status != 'Suspended')
        {
            throw new Exception('Already unsuspended');
        }
    }
}
