<?php

namespace MGModule\GGSSLWHMCS\eHelpers;

class SansDomains {
    public static function parseDomains($sansDomains) {
        $exSansDomains = explode(PHP_EOL, $sansDomains);
        foreach ($exSansDomains as &$sansDomain) {
            $sansDomain = trim($sansDomain);
        }
        foreach ($exSansDomains as $key => &$sansDomain) {
            if (empty($sansDomain)) {
                unset($exSansDomains[$key]);
            }
        }
        return array_unique($exSansDomains);
    }
    

}