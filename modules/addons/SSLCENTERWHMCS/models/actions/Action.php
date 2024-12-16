<?php

namespace MGModule\SSLCENTERWHMCS\models\actions;

use MGModule\SSLCENTERWHMCS\mgLibs\models\Orm;

/**
 * @Table(name=SSLCENTER_actions,prefixed=true)
 */
class Action extends Orm
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
     * @Column(action)
     * @var type
     */
    public $action;

    /**
     *
     * @Column(status)
     * @var type
     */
    public $status;

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

    public function getAction()
    {
        return $this->action;
    }

    public function getStatus()
    {
        return $this->status;
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

    public function setAction($value)
    {
        $this->action = $value;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }
}
