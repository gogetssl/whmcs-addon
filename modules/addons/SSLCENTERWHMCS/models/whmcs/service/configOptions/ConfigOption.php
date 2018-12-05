<?php

namespace MGModule\SSLCENTERWHMCS\models\whmcs\service\configOptions;
use MGModule\SSLCENTERWHMCS as main;

class ConfigOption{
    public $id;
    public $name;
    public $type;
    public $frendlyName;
    public $value;
    public $options = array();
    public $optionsIDs = array();
}