<?php

namespace MGModule\GGSSLWHMCS\mgLibs\forms;
use MGModule\GGSSLWHMCS as main;

/**
 * Password Form Field
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class PasswordField extends AbstractField{
    public $showPassword = false;
    public $type    = 'password';
    
    static function asteriskVar($input){
        $num = strlen($input);
        $input = '';
        
        for($i=0;$i<$num;$i++)
        {
            $input .= '*';
        }
        
        return $input;
    }
    
    function prepare() {
        if(!$this->showPassword)
        {
            self::asteriskVar($this->value);
        }
    }
}