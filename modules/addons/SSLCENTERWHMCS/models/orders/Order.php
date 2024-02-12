<?php

namespace MGModule\SSLCENTERWHMCS\models\orders;

use MGModule\SSLCENTERWHMCS\mgLibs\models\Orm;

/**
 * @Table(name=SSLCENTER_orders,prefixed=true)
 */
class Order extends Orm
{
    /**
     *
     * @Column(id)
     * @var type
     */
    public $id;

    /**
     *
     * @Column(client_id)
     * @var type
     */
    public $client_id;


    /**
     *
     * @Column(service_id)
     * @var type
     */
    public $service_id;

    /**
     *
     * @Column(ssl_order_id)
     * @var type
     */
    public $ssl_order_id;

    /**
     *
     * @Column(verification_method)
     * @var type
     */
    public $verification_method;

    /**
     *
     * @Column(status)
     * @var type
     */
    public $status;

    /**
     *
     * @Column(data)
     * @var type
     */
    public $data;


    /**
     *
     * @Column(date)
     * @var type
     */
    public $date;

    public function getID()
    {
        return $this->id;
    }

    public function getClientID()
    {
        return $this->client_id;
    }

    public function getServiceID()
    {
        return $this->service_id;
    }

    public function getSSLOrderID()
    {
        return $this->ssl_order_id;
    }

    public function getVerificationMethod()
    {
        return $this->verification_method;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setServiceID($id)
    {
        $this->service_id = $id;
    }

    public function setClientID($id)
    {
        $this->client_id = $id;
    }

    public function setSSLOrderID($id)
    {
        $this->ssl_order_id = $id;
    }

    public function setVerificationMethod($value)
    {
        $this->verification_method = $value;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function setData($value)
    {
        $this->data = $value;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }
}
