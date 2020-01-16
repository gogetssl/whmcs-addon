<?php

namespace MGModule\SSLCENTERWHMCS\models\customWHMCS\product;
use MGModule\SSLCENTERWHMCS as main;

/**
 * @Table(name=custom_configuration)
 */
class Configuration extends \MGModule\SSLCENTERWHMCS\mgLibs\models\Orm{
    /**
     * 
     * @Column(id)
     * @var type 
     */
    public $id;
    
    /**
     * @Column(varchar=32)
     * @var type 
     */
    public $name;
    
    /**
     * @Column(varchar=32)
     * @var type 
     */
    public $confa;
}