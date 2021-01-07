<?php

namespace MGModule\SSLCENTERWHMCS\eRepository\whmcs\config;

use Exception;

class Countries {

    private static $instance;
    private $countries = [];

    /**
     * 
     * @return Countries
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Countries();
        }
        return self::$instance;

    }

    function __construct() {
        $this->loadCountres();

    }

    private function loadCountres() {
        $whmcsVersion = Config::getInstance()->getVersionMajor();
        if ($whmcsVersion === 6) {
            $this->loadCountriesWhmcs6();
        } elseif ($whmcsVersion === 7 || $whmcsVersion === 8) {
            $this->loadCountriesWhmcs7();
        } else {
            throw new Exception('WHMCS version not supported');
        }
    }

    /**
     * 
     * @param type $name
     * @return string
     * @throws Exception
     */
    public function getCountryCodeByName($name) {
        
        if(strlen($name) <= 2)
        {
            return $name;
        }
        
        foreach ($this->countries as $countryCode => $countryName) {
            if (strtolower($countryName) === strtolower($name)) {
                return $countryCode;
            }
        }
        throw new Exception('Can not match country name to country code');

    }

    /**
     * 
     * @param type $code
     * @return string
     * @throws Exception
     */
    public function getCountryNameByCode($code) {
        $code = strtoupper($code);
        
        if (isset($this->countries[$code])) {
            return $this->countries[$code];
        }

        throw new Exception('Can not match country code to country name');

    }
    
    public function getCountriesForWhmcsDropdownOptions() {
        return implode(',', $this->countries);
    }
    
    public function getCountriesForMgAddonDropdown() {
        return $this->countries;
    }

    private function loadCountriesWhmcs6() {
        $ccPath = \MGModule\SSLCENTERWHMCS\eProviders\PathProvider::getWhmcsCounriesPatch(6);

        if (!file_exists($ccPath)) {
            throw new Exception('Countries file not exist');
        }

        require $ccPath;

        foreach ($countries as $countryCode => $countryName) {
            $this->countries[$countryCode] = $countryName;
        }

    }

    private function loadCountriesWhmcs7() {
        $ccPath = \MGModule\SSLCENTERWHMCS\eProviders\PathProvider::getWhmcsCounriesPatch(7);

        if (!file_exists($ccPath)) {
            throw new Exception('Countries file not exist');
        }

        $countries = json_decode(file_get_contents($ccPath));

        if (is_null($countries)) {
            throw new Exception('Can not decode countries JSON');
        }

        foreach ($countries as $countryCode => $country) {
            $this->countries[$countryCode] = str_replace(',', ' -', $country->name);
        }

    }

}
