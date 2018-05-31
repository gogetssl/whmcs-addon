<?php

namespace MGModule\GGSSLWHMCS\eHelpers;

class Domains {

    public static function validateDomain($domain) {     
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain)
            && preg_match("/^.{1,253}$/", $domain)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain)
            && preg_match('/[^.\d]/', $domain));
    }
    
    public static function additionalValidation($domain)
    {

        return (preg_match("/(\*[1]*)(\.[a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain) 
                && substr_count($domain, '.') == 2 
                && substr_count($domain, '*') == 1);
    }

    public static function getInvalidDomains(array $domains, $additionalValidation = false) {
        $invalidDomains = [];
        $item = -1;
        foreach ($domains as $domain) {
            if (!Domains::validateDomain($domain)) {
                $invalidDomains[] = $domain;
                $item++;
            }

            if($additionalValidation && Domains::additionalValidation($domain))
            {
                unset($invalidDomains[$item]);
            }
        }
        return $invalidDomains;

    }

}
