<?php

namespace MGModule\GGSSLWHMCS\mgLibs\forms;
use MGModule\GGSSLWHMCS as main;

/**
 * Test Form Field
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class NumberField extends AbstractField{
    public $enablePlaceholder = false;
    public $type    = 'number';
    public $min    = false;
    public $max    = false;
}