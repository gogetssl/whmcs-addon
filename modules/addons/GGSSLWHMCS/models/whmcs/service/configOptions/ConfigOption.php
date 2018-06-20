<?php

namespace MGModule\GGSSLWHMCS\models\whmcs\service\configOptions;
use MGModule\GGSSLWHMCS as main;

class ConfigOption{
    public $id;
    public $name;
    public $type;
    public $frendlyName;
    public $value;
    public $options = array();
    public $optionsIDs = array();
}