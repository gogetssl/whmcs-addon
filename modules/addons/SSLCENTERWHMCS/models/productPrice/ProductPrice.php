<?php

namespace MGModule\SSLCENTERWHMCS\models\productPrice;

use MGModule\SSLCENTERWHMCS as main;

/**
 * @Table(name=SSLCENTER_api_product_prices)
 */
class ProductPrice extends \MGModule\SSLCENTERWHMCS\mgLibs\models\Orm
{
    /**
     * 
     * @Column(id)
     * @var type 
     */
    public $id;

    /**
     * 
     * @Column(api_product_id)
     * @var type 
     */
    public $api_product_id;

    /**
     * @Column(varchar=32)
     * @var type 
     */
    public $price;

    /**
     * @Column(varchar=32)
     * @var type 
     */
    public $period;

    public function getID()
    {
        return $this->id;
    }

    public function getApiProductID()
    {
        return $this->api_product_id;
    }

    public function setApiProductID($id)
    {
        $this->api_product_id = $id;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
    }
}
