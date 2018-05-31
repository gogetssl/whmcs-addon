<?php

namespace MGModule\GGSSLWHMCS;

use MGModule\GGSSLWHMCS as main;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Module Configuration
 *
 * @author Michal Czech <michael@modulesgarden.com>
 * @SuppressWarnings("unused")
 */
class Configuration extends main\mgLibs\process\AbstractConfiguration {

    /**
     * Enable or disable debug mode in your module.
     * @var bool
     */
    public $debug = false;

    /**
     * Module name in WHMCS configuration
     * @var string
     */
    public $systemName = 'GGSSLWHMCS';

    /**
     * Module name visible on addon module page
     * @var string
     */
    public $name = 'GGSSL WHMCS';

    /**
     * Module description
     * @var string
     */
    public $description = '';

    /**
     * Module name in client area
     * @var string
     */
    public $clientareaName = 'GGSSL WHMCS';

    /**
     * Encryption hash. Used in ORM 
     * @var string
     */
    public $encryptHash = 'uUc1Y8cWxDOAzlq11lBwelqzo6PGMTA0dbHaKQ109psefoJgIFMOgmReKCZbpCYpDSnrtfjmCIUyplaBJaUh40auDALprOHtj1g92ZRBS6S94IbZWaeZRYkG1f81h6qLMYEOr016RurCnmodFCWdMkTqrlVBvH249gzXPduKQVXpN9hooComaRPY5jZD6s8GdfR5E_BNP3v8Ui8RrdqMPST_8quMW48LhHY88xCvSWwDNjkC2tCAaK67Id2NjzIdoNTHUMISRg81nHX8ZGcbP74mxixo_ASd8YoWnDCAs8yiT4t0PwKRO_y3C1kDo69Nxz1YYt4tY1VzOD_DFBulAA5NCJLfogroo';

    /**
     * Module version
     * @var string
     */
    public $version = '1.0.3';

    /**
     * Module author
     * @var string
     */
    public $author = '<a href="http://www.modulesgarden.com" targer="_blank">ModulesGarden</a>';

    /**
     * Table prefix. This prefix is used in database models. You have to change it! 
     * @var type 
     */
    public $tablePrefix = 'mgfw_';
    public $modelRegister = array(
        'models\testGroup\testItem\TestItem'
        , 'models\testGroup\simpleItem\SimpleItem'
        , 'models\categories\Category'
        , 'models\accessDetails\AccessDetail'
    );

    function __construct() {
        /*
          models\whmcs\product\configOptions\Repository::setConfiguration(array(

          ));

          $product = new models\whmcs\product\product($id); */
    }

    /**
     * Addon module visible in module
     * @return array
     */
    function getAddonMenu() {
        return array(
            'apiConfiguration' => array
                (
                'icon' => 'fa fa-key',
            ),
            'productsCreator' => array
                (
                'icon' => 'fa fa-magic',
            ),
            'productsConfiguration' => array
                (
                'icon' => 'fa fa-edit',
            ),
        );
    }

    /**
     * Addon module visible in client area
     * @return array
     */
    function getClienMenu() {
        return array(
            'home' => array(
                'icon' => 'glyphicon glyphicon-home'
            ),
            'shared' => array
                (
                'icon' => 'fa fa-key'
            ),
            'product' => array
                (
                'icon' => 'fa fa-key'
            ),
            'categories' => array
                (
                'icon' => 'glyphicon glyphicon-th-list'
            )
        );
    }

    /**
     * Provisioning menu visible in admin area
     * @return array
     */
    function getServerMenu() {
        return array(
            'configuration' => array(
                'icon' => 'glyphicon glyphicon-cog'
            )
        );
    }

    /**
     * Return names of WHMCS product config fields
     * required if you want to use default WHMCS product configuration
     * max 20 fields
     * 
     * if you want to use own product configuration use example 
     * /models/customWHMCS/product to define own configuration model
     * 
     * @return array
     */
    public function getServerWHMCSConfig() {
        return array(
            'text_name'
            , 'text_name2'
            , 'checkbox_name'
            , 'onoff'
            , 'pass'
            , 'some_option'
            , 'some_option2'
            , 'radio_field'
        );
    }

    /**
     * Addon module configuration visible in admin area. This is standard WHMCS configuration
     * @return array
     */
    public function getAddonWHMCSConfig() {
        return [];
    }

    /**
     * Run When Module Install
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return array
     */
    function activate() {
        $apiConfigRepo = new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository();
        $apiConfigRepo->createApiConfigurationTable();
        eServices\EmailTemplateService::createConfigurationTemplate();
        eServices\EmailTemplateService::createCertyficateTemplate();
    }

    /**
     * Do something after module deactivate. You can status and description
     * @return array
     */
    function deactivate() {
        $apiConfigRepo = new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository();
        $apiConfigRepo->dropApiConfigurationTable();
        eServices\EmailTemplateService::deleteConfigurationTemplate();
        eServices\EmailTemplateService::deleteCertyficateTemplate();
    }

    /**
     * Do something after module upgrade
     * @param type $vars
     */
    function upgrade($vars) {
        $version = $vars['version'];
    }

}
