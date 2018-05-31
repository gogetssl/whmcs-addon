<?php

namespace MGModule\GGSSLWHMCS\eServices;

class ScriptService {

    const WEB_SERVER    = 'scripts/webServerType';
    const SAN_EMAILS    = 'scripts/sanApprovals';
    const ADMIN_SERVICE = 'scripts/adminService';
    const AUTO_FILL     = 'scripts/autoFill';
    const PRIVATE_KEY_FILL = 'scripts/privateKeyFill';
    const OPTION_ERROR  = 'scripts/configOptionsError';
    const STEP_ONE_BASE = 'scripts/stepOneBase';
    const DCV_METHOD    = 'scripts/dcvMethod';
    const GENERATE_CSR_MODAL    = 'scripts/generateCsrModal';


    public static function getWebServerTypeSctipt($apiWebServersJSON) {
        $servertype = \MGModule\GGSSLWHMCS\eServices\FlashService::getFieldsMemory($_GET['cert'], 'servertype');
        if(count($servertype)=== 0) {
            $servertype = 0;
        }
        if(!empty($_POST['servertype'])) {
            $servertype = $_POST['servertype'];
        }
        return TemplateService::buildTemplate(self::WEB_SERVER, [
                    'serverTypes'      => addslashes($apiWebServersJSON),
                    'selectedServerId' => $servertype,
        ]);
    }
    
    public static function getStepOneBaseScript($brand) {
        return TemplateService::buildTemplate(self::STEP_ONE_BASE, ['brand' => json_encode($brand)]);
    }
    public static function getDcvMethodScript($methodsJSON, $fillVarsJSON, $brand) {
        return TemplateService::buildTemplate(self::DCV_METHOD,[
                    'methodTypes'       => addslashes($methodsJSON),
                    'fillVars'          => addslashes($fillVarsJSON),
                    'brand'             => addslashes($brand)
        ]);
    }
    public static function getGenerateCsrModalScript($fillVarsJSON, $countriesForGenerateCsrForm) {
        return TemplateService::buildTemplate(self::GENERATE_CSR_MODAL, [
                    'fillVars' => addslashes($fillVarsJSON),
                    'countries'=> json_encode($countriesForGenerateCsrForm)
        ]);
    }    
    public static function getAutoFillPrivateKeyField($privateKey) {
        return TemplateService::buildTemplate(self::PRIVATE_KEY_FILL, ['privateKey' => $privateKey]);
    }
    public static function getAutoFillFieldsScript($fillVarsJSON) {
        return TemplateService::buildTemplate(self::AUTO_FILL, ['fillVars' => addslashes($fillVarsJSON)]);
    }

    public static function getSanEmailsScript($apiSanEmailsJSON, $fillVarsJSON = null) {
        return TemplateService::buildTemplate(self::SAN_EMAILS, ['sanEmails' => addslashes($apiSanEmailsJSON), 'fillVars' => addslashes($fillVarsJSON)]);
    }

    public static function getAdminServiceScript($vars) {
        return TemplateService::buildTemplate(self::ADMIN_SERVICE, $vars);
    }
    
    public static function getConfigOptionErrorScript($error) {
        return TemplateService::buildTemplate(self::OPTION_ERROR, ['error' => $error]);
    }
}
