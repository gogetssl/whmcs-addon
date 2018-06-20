<?php

namespace MGModule\GGSSLWHMCS\eHelpers;

class Whmcs
{
    public static function isWHMCS73()
    {        
        $version = explode("-", $GLOBALS['CONFIG']['Version'], 2)[0];
        
        return version_compare($version, '7.3.0', '>=');        
    }
}
