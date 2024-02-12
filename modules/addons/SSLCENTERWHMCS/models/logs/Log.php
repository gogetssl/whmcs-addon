<?php

namespace MGModule\SSLCENTERWHMCS\models\logs;

use MGModule\SSLCENTERWHMCS\mgLibs\models\Orm;

/**
 * @Table(name=SSLCENTER_logs,prefixed=true)
 */
class Log extends Orm
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
     * @Column(type)
     * @var type
     */
    public $type;

    /**
     *
     * @Column(msg)
     * @var type
     */
    public $msg;

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

    public function getType()
    {
        return $this->type;
    }

    public function getMsg()
    {
        return $this->msg;
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

    public function setType($value)
    {
        $this->type = $value;
    }

    public function setMsg($value)
    {
        $this->msg = $value;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }
}
