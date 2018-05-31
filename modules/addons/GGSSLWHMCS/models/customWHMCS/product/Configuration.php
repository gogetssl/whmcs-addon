<?php

namespace MGModule\GGSSLWHMCS\models\customWHMCS\product;
use MGModule\GGSSLWHMCS as main;

/**
 * @Table(name=custom_configuration)
 */
class Configuration extends \MGModule\GGSSLWHMCS\mgLibs\models\Orm{
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