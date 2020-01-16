<?php

namespace MGModule\SSLCENTERWHMCS\models\customWHMCS\product;
use MGModule\SSLCENTERWHMCS as main;

/**
 * @SuppressWarnings(PHPMD)
 */
class Product extends MGModule\SSLCENTERWHMCS\models\whmcs\product\product{
    function loadConfiguration($params){
        return new Configuration($this->id);
    }
}