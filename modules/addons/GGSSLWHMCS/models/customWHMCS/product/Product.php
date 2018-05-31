<?php

namespace MGModule\GGSSLWHMCS\models\customWHMCS\product;
use MGModule\GGSSLWHMCS as main;

/**
 * @SuppressWarnings(PHPMD)
 */
class Product extends MGModule\GGSSLWHMCS\models\whmcs\product\product{
    function loadConfiguration($params){
        return new Configuration($this->id);
    }
}