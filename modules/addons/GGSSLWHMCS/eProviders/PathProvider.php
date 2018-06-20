<?php

namespace MGModule\GGSSLWHMCS\eProviders;

use Exception;

class PathProvider {

    public static function getWhmcsPath() {
        $currentDir = __DIR__;
        for ($i = 1; $i < 5; $i++) {
            $currentDir = dirname($currentDir);
        }
        return $currentDir;

    }

    public static function getPath($path = array()) {
        return implode(DIRECTORY_SEPARATOR, array_merge(array(self::getWhmcsPath()), $path));

    }

    public static function getWhmcs7CountriesPatch() {
        return self::getPath(['resources', 'country', 'dist.countries.json']);
    }

    public static function getWhmcs6CountriesPatch() {
        return self::getPath(['includes', 'countries.php']);
    }

    public static function getWhmcsCounriesPatch($whmcsVersion) {
        if ($whmcsVersion === 7) {
            return self::getWhmcs7CountriesPatch();
        } elseif ($whmcsVersion === 6) {
            return self::getWhmcs6CountriesPatch();
        } else {
            throw new Exception('WHMCS version not supported');
        }

    }

}
