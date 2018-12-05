<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\clientarea;
use MGModule\SSLCENTERWHMCS as main;

/**
 * Description of home
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Home extends main\mgLibs\process\AbstractController{
    
    public function indexHTML($input = array()){

        return array(
           'tpl'    => 'home'
           ,'vars'  => []
        );        
    }
}
