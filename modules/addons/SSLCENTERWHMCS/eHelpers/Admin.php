<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MGModule\SSLCENTERWHMCS\eHelpers;

/**
 * Description of Invoice
 *
 * @author Rafal Sereda <rafal.se at modulesgarden.com>
 */
class Admin
{
    protected static $adminUserName = null;
    
    public static function getAdminUserName() {
        if (!self::$adminUserName) {
            self::$adminUserName = \MGModule\SSLCENTERWHMCS\mgLibs\MySQL\Query::query('SELECT username FROM tbladmins LIMIT 1')->fetchColumn('username');
        }
        return self::$adminUserName;
    }
}
