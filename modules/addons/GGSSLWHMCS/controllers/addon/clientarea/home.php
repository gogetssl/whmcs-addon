<?php

namespace MGModule\GGSSLWHMCS\controllers\addon\clientarea;
use MGModule\GGSSLWHMCS as main;

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
