<?php

namespace MGModule\SSLCENTERWHMCS\mgLibs\MySQL;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of PdoWrapper
 */
class PdoWrapper {

    public static function query($query, $params=array()) {
        $statement = DB::connection()
                ->getPdo()
                ->prepare($query);
        $statement->execute($params);
        return $statement;
    }

    public static function realEscapeString($string) {
        return substr(DB::connection()->getPdo()->quote($string), 1, -1);
    }

    public static function fetchAssoc($query) {
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    public static function fetchArray($query) {
        return $query->fetch(\PDO::FETCH_BOTH);
    }

    public static function fetchObject($query) {
        return $query->fetch(\PDO::FETCH_OBJ);
    }

    public static function numRows($query) {
        $query->fetch(\PDO::FETCH_BOTH);
        return $query->rowCount();
    }

    public static function insertId() {
        return DB::connection()
                        ->getPdo()
                        ->lastInsertId();
    }

}
