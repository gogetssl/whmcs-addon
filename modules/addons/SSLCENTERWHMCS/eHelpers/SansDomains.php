<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

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
    
    public static function decodeSanAprroverEmailsAndMethods(&$post) {
        if(isset($post['dcvmethod']))
        {
            $newDcvMethodArray = array();
            foreach($post['dcvmethod'] as $domain => $method)
            {
                if(strpos($domain, '___') !== FALSE)
                {

                    $domain = str_replace('___', '*', $domain);
                }
                $newDcvMethodArray[$domain] = $method;
            }
            
            $post['dcvmethod'] = $newDcvMethodArray;
        }
        if(isset($post['approveremails']))
        {
            $newApproverEmailsArray = array();
            foreach($post['approveremails'] as $domain => $method)
            {
                if(strpos($domain, '___') !== FALSE)
                {

                    $domain = str_replace('___', '*', $domain);
                }
                $newApproverEmailsArray[$domain] = $method;
            }
            
            $post['approveremails'] = $newApproverEmailsArray;
        }        
    }
}
