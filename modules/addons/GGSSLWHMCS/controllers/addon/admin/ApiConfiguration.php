<?php

namespace MGModule\GGSSLWHMCS\controllers\addon\admin;

use MGModule\GGSSLWHMCS as main;

/*
 * Base example
 */

class ApiConfiguration extends main\mgLibs\process\AbstractController {

    /**
     * This is default page. 
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function indexHTML($input = [], $vars = []) {
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $apiConfigRepo = new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository();
            $input         = (array) $apiConfigRepo->get();
        }
        $form = new main\mgLibs\forms\Creator('item');
        
        $field = new main\mgLibs\forms\TextField();        
        $field->name = 'api_login';
        $field->value = $input['api_login'];
        $field->error = $this->getFieldError('api_login');
        $form->addField($field);

        $field = new main\mgLibs\forms\PasswordField();
        $field->name = 'api_password';
        $field->value = $input['api_password'];
        $field->error = $this->getFieldError('api_password');
        $form->addField($field);
        
        $form->addField('button', 'testConnection', array(
            'value' => 'testConnection',
        ));
        
        $field        = new main\mgLibs\forms\LegendField();
        $field->name  = 'csr_generator_legend';
        $form->addField($field);
        
        $field = new main\mgLibs\forms\CheckboxField();
        $field->name = 'display_csr_generator';
        $field->options = ['displayCsrGenerator'];
        $field->value = $input['display_csr_generator'] ? ['displayCsrGenerator'] : [''];
        $form->addField($field);
        
        $field        = new main\mgLibs\forms\LegendField();
        $field->name  = 'tech_legend';
        $form->addField($field);

        $field = new main\mgLibs\forms\CheckboxField();
        $field->name = 'use_admin_contact';
        $field->options = ['useAdministrative'];
        $field->value = $input['use_admin_contact'] ? ['useAdministrative'] : [''];
        $form->addField($field);
        
        $field = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_firstname';        
        $field->required = true;
        $field->options = ['1','2'];
        $field->value = $input['tech_firstname'];
        $field->error = $this->getFieldError('tech_firstname');
        $form->addField($field);
        
        $field = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_lastname';
        $field->required = true;
        $field->value = $input['tech_lastname'];
        $field->error = $this->getFieldError('tech_lastname');
        $form->addField($field);

        $field = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_organization';
        $field->required = true;
        $field->value = $input['tech_organization'];
        $field->error = $this->getFieldError('tech_organization');
        $form->addField($field);
        
        $field = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_addressline1';
        $field->required = true;
        $field->value = $input['tech_addressline1'];
        $field->error = $this->getFieldError('tech_addressline1');
        $form->addField($field);

        $field = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_phone';
        $field->required = true;
        $field->value = $input['tech_phone'];
        $field->error = $this->getFieldError('tech_phone');
        $form->addField($field);

        $field = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_title';
        $field->required = true;
        $field->value = $input['tech_title'];
        $field->error = $this->getFieldError('tech_title');
        $form->addField($field);

        $field = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_email';
        $field->required = true;
        $field->value = $input['tech_email'];
        $field->error = $this->getFieldError('tech_email');
        $form->addField($field);

        $field = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_city';
        $field->required = true;
        $field->value = $input['tech_city'];
        $field->error = $this->getFieldError('tech_city');
        $form->addField($field);

        $field = new main\mgLibs\forms\SelectField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name = 'tech_country';
        $field->required = true;
        $field->value = $input['tech_country'];
        $field->translateOptions = false;
        $field->options = \MGModule\GGSSLWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountriesForMgAddonDropdown();         
        $field->error = $this->getFieldError('tech_country');
        $form->addField($field);

        $field        = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name  = 'tech_fax';
        $field->required = true;
        $field->value = $input['tech_fax'];
        $field->error = $this->getFieldError('tech_fax');
        $form->addField($field);

        $field        = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name  = 'tech_postalcode';
        $field->required = true;
        $field->value = $input['tech_postalcode'];
        $field->error = $this->getFieldError('tech_postalcode');
        $form->addField($field);

        $field        = new main\mgLibs\forms\TextField();
        $field->readonly = $input['use_admin_contact'] ? true : false;
        $field->name  = 'tech_region';
        $field->required = true;
        $field->value = $input['tech_region'];
        $field->error = $this->getFieldError('tech_region');
        $form->addField($field);
        
        $form->addField('submit', 'mg-action', array(
            'value' => 'saveItem'
        ));
        
        $vars['form'] = $form->getHTML();
        
        //get cron command line
        $vars['cronCommandLine'] = 'php -q '.ROOTDIR.DS.'modules'.DS.'addons'.DS.'GGSSLWHMCS'.DS.'cron'.DS.'cron.php';

        return array
            (
            //You have to create tpl file  /modules/addons/GGSSLWHMCS/templates/admin/pages/example1/example.1tpl
            'tpl' => 'api_configuration',
            'vars' => $vars
        );
    }

    function saveItemHTML($input, $vars = array()) {
        if ($this->checkToken()) {
            try {
                if(isset($input['use_admin_contact'])) {
                    $input['use_admin_contact'] = true;
                } else {
                    $input['use_admin_contact'] = false;
                }
                if(isset($input['display_csr_generator'])) {
                    $input['display_csr_generator'] = true;
                } else {
                    $input['display_csr_generator'] = false;
                }
                $apiConfigRepo = new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository();
                $apiConfigRepo->setConfiguration($input);
            } catch (\Exception $ex) {
                $vars['formError'] = main\mgLibs\Lang::T('messages', $ex->getMessage());
            }
        }

        return $this->indexHTML($input, $vars);
    }

    public function testConnectionJSON($input = [], $vars = []) {

        $api = new \MGModule\GGSSLWHMCS\mgLibs\GoGetSSLApi();
        
        $authKey = $api->auth($input['api_login'], $input['api_password']);

        return [
            'success' => main\mgLibs\Lang::T('messages', 'api_connection_success')
        ];
    }

    /**
     * This is custom page. 
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function pageHTML() {
        $vars = array();

        return array
            (
            //You have to create tpl file  /modules/addons/GGSSLWHMCS/templates/admin/pages/example1/page.1tpl
            'tpl' => 'page',
            'vars' => $vars
        );
    }

    /*     * ************************************************************************
     * AJAX USING ARRAY
     * ************************************************************************ */

    /**
     * Display custom page for ajax errors
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function ajaxErrorHTML() {
        return array
            (
            'tpl' => 'ajaxError'
        );
    }

    /**
     * Return error message using array
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function getErrorArrayJSON() {
        return array
            (
            'error' => 'Custom error'
        );
    }

    /**
     * Return success message using array
     * @param type $input
     * @param type $vars
     * @return type
     */
    public function getSuccessArrayJSON() {
        return array
            (
            'success' => 'Custom success'
        );
    }

    /*     * ************************************************************************
     * AJAX USING DATA-ACT
     * *********************************************************************** */

    public function ajaxErrorDataActHTML() {
        return array
            (
            'tpl' => 'ajaxErrorDataAct'
        );
    }

    /*     * ************************************************************************
     * AJAX CONTENT
     * *********************************************************************** */

    public function ajaxContentHTML() {
        return array
            (
            'tpl' => 'ajaxContent'
        );
    }

    public function ajaxContentJSON() {
        return array
            (
            'html' => main\mgLibs\Smarty::I()->view('ajaxContentJSON')
        );
    }

    /*     * ******************************************************
     * CREATOR
     * ***************************************************** */

    public function getCreatorJSON() {
        $creator = new main\mgLibs\forms\Popup('mymodal');
        $creator->addField(new main\mgLibs\forms\TextField(array(
            'name' => 'customTextField',
            'value' => 'empty_value',
            'placeholder' => 'placeholder!'
        )));
        ;

        return array(
            'modal' => $creator->getHTML()
        );
    }

}
