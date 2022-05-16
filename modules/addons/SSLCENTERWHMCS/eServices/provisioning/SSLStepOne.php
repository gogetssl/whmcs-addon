<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class SSLStepOne {

    private $p;

    function __construct(&$params) {        
        $this->p = &$params;
    }

    public function run() {
        try {            
            return $this->SSLStepOne();
        } catch (Exception $e) {
            \MGModule\SSLCENTERWHMCS\eServices\FlashService::setStepOneError($this->getErrorForClient());
        }
    }

    private function SSLStepOne() {    
        
        $fields['additionalfields'] = [];
        $apiProductId  = $this->p[ConfigOptions::API_PRODUCT_ID];
        $apiRepo       = new \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products();
        $apiProduct    = $apiRepo->getProduct($apiProductId);
        //$apiWebServers = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\WebServers::getAll($apiProduct->getWebServerTypeId());
        if($apiProduct->brand == 'comodo')
        {
            $apiWebServers = array(
                array('id' => '35', 'software' => 'IIS'),
                array('id' => '-1', 'software' => 'Any Other')
            );
        }
        else 
        {
            $apiWebServers = array(
                array('id' => '18', 'software' => 'IIS'),
                array('id' => '18', 'software' => 'Any Other')
            );
        }

        $apiWebServersJSON         = json_encode($apiWebServers);
        $fillVarsJSON              = json_encode(\MGModule\SSLCENTERWHMCS\eServices\FlashService::getFieldsMemory($_GET['cert']));
        $sanEnabledForWHMCSProduct = $this->p[ConfigOptions::PRODUCT_ENABLE_SAN] === 'on';

        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $includedSansWildcard = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS_WILDCARD];
        
        $boughtSans   = (int) $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        
        $orderTypes = ['new', 'renew'];
        
        $sansLimit    = $includedSans + $boughtSans;        

        
        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();        
        $displayCsrGenerator = $apiConf->display_csr_generator;    
        
        if (!$sanEnabledForWHMCSProduct) {
            $sansLimit = 0;
        } 
        //$fields['additionalfields'][\MGModule\SSLCENTERWHMCS\eRepository\sslcenter\OrderType::getTitle()] = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\OrderType::getFields();
        
        if ($sansLimit > 0) {
            $fields['additionalfields'][\MGModule\SSLCENTERWHMCS\eRepository\sslcenter\San::getTitle()] = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\San::getFields($sansLimit, $this->p['configoptions']['sans_wildcard_count']+$includedSansWildcard);
        }
        if ($apiProduct->isOrganizationRequired()) {
            $fields['additionalfields'][\MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Organization::getTitle()] = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Organization::getFields();
        }
        $countriesForGenerateCsrForm = \MGModule\SSLCENTERWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountriesForMgAddonDropdown();
        
        //get selected default country for CSR Generator
        $defaultCsrGeneratorCountry = ($displayCsrGenerator) ? $apiConf->default_csr_generator_country : '';
        if(key_exists($defaultCsrGeneratorCountry, $countriesForGenerateCsrForm) AND $defaultCsrGeneratorCountry != NULL)
        {
            //get country name
            $elementValue = $countriesForGenerateCsrForm[$defaultCsrGeneratorCountry]/* . ' (default)'*/;            
            //remove country from list
            unset($countriesForGenerateCsrForm[$defaultCsrGeneratorCountry]);
            //insert default country on the begin of countries list
            $countriesForGenerateCsrForm = array_merge(array($defaultCsrGeneratorCountry => $elementValue), $countriesForGenerateCsrForm);
        }
        
        $wildCard = false;
        $apiProducts = \MGModule\SSLCENTERWHMCS\eRepository\sslcenter\Products::getInstance()->getAllProducts();
        if(isset($apiProducts[$this->p['configoption1']]->wildcard_enabled) && $apiProducts[$this->p['configoption1']]->wildcard_enabled == '1')
        {
            $wildCard = true;
        }
        
        $stepOneBaseScript    = \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getStepOneBaseScript($apiProduct->brand);
        $orderTypeScript    = \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getOrderTypeScript($orderTypes, $fillVarsJSON);
        $webServerTypeSctipt  = \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getWebServerTypeSctipt($apiWebServersJSON);
        $autoFillFieldsScript = \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getAutoFillFieldsScript($fillVarsJSON);        
        $generateCsrModalScript = ($displayCsrGenerator) ? \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getGenerateCsrModalScript($fillVarsJSON, $countriesForGenerateCsrForm, array('wildcard' => $wildCard)) : '';
        //when server type is not selected exception
        if(isset($_POST['privateKey']) && $_POST['privateKey'] != null && empty(json_decode($fillVarsJSON))) {
            $autoFillPrivateKeyField = \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getAutoFillPrivateKeyField($_POST['privateKey']);
        }
        //auto fill order type field
        if(isset($_POST['fields']['order_type']) && $_POST['fields']['order_type'] != null) {
            $autoFillOrderTypeField = \MGModule\SSLCENTERWHMCS\eServices\ScriptService::getAutoFillOrderTypeField($_POST['fields']['order_type']);
        }
        
        $fields['additionalfields']['<br />']['<br />'] = [
            'Description' => $stepOneBaseScript . $webServerTypeSctipt . $orderTypeScript . $autoFillFieldsScript . $generateCsrModalScript .$autoFillPrivateKeyField . $autoFillOrderTypeField,
        ];
        
        return $fields;

    }
    private function getErrorForClient() {
        return \MGModule\SSLCENTERWHMCS\mgLibs\Lang::getInstance()->T('canNotFetchWebServer');

    }  
}
