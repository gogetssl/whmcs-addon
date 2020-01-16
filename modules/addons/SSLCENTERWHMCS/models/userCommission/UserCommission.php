<?php

namespace MGModule\SSLCENTERWHMCS\models\userCommission;

use MGModule\SSLCENTERWHMCS as main;

/**
 * @Table(name=SSLCENTER_user_commission,prefixed=true)
 */
class UserCommission extends \MGModule\SSLCENTERWHMCS\mgLibs\models\Orm
{
    /**
     * 
     * @Column(id)
     * @var type 
     */
    public $id;

    /**
     * 
     * @Column(product_id)
     * @var type 
     */
    public $product_id;

    /**
     * 
     * @Column(client_id)
     * @var type 
     */
    public $client_id;

    /**
     * 
     * @Column(commission)
     * @var type 
     */
    public $commission;

    public function getID()
    {
        return $this->id;
    }

    public function getProductID()
    {
        return $this->product_id;
    }

    public function setProductID($id)
    {
        $this->product_id = $id;
    }

    public function getClientID()
    {
        return $this->client_id;
    }

    public function setClientID($id)
    {
        $this->client_id = $id;
    }

    public function getCommission()
    {
        return $this->commission;
    }

    public function setCommission($value)
    {
        $this->commission = $value;
    }
}
