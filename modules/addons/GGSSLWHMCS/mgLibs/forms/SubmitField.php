<?php

namespace MGModule\GGSSLWHMCS\mgLibs\forms;
use MGModule\GGSSLWHMCS as main;


/**
 * Submit Form Button  
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class SubmitField extends AbstractField{   
    public $icon;
    public $color           = 'success btn-inverse';
    public $type            = 'submit';
    public $enableContent   = true;
}