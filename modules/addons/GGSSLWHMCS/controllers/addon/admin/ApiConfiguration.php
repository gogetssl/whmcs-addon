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
        $field->name  = 'renewal_settings_legend';
        $form->addField($field);
        
        $field = new main\mgLibs\forms\CheckboxField();
        $field->name = 'auto_renew_invoice_reccuring';
        $field->options = ['auto_renew_invoice_reccuring'];
        $field->value = $input['auto_renew_invoice_reccuring'] ? ['auto_renew_invoice_reccuring'] : [''];
        $field->inline = true;
        $field->colWidth = 3;
        $field->continue = true;
        $field->enableDescription = true;
        $form->addField($field);
        
        $field = new main\mgLibs\forms\SelectField();
        $field->disabled = $input['auto_renew_invoice_reccuring'] ? false : true;
        $field->name = 'renew_invoice_days_reccuring';
        $field->required = true;
        $field->value = $input['renew_invoice_days_reccuring'];
        $field->translateOptions = false;
        $field->inline = true;
        $field->colWidth = 2;
        $field->continue = false;
        $field->enableDescription = true;
        $field->options = array('90'=>'90', '60'=>'60', '45'=>'45', '30'=>'30', '15'=>'15');         
        $field->error = $this->getFieldError('renew_invoice_days_reccuring');
        $form->addField($field);
        
        $field = new main\mgLibs\forms\CheckboxField();
        $field->name = 'send_expiration_notification_reccuring';
        $field->options = ['send_expiration_notification_reccuring'];
        $field->value = $input['send_expiration_notification_reccuring'] ? ['send_expiration_notification_reccuring'] : [''];
        $field->inline = true;
        $field->enableLabel = true;               
        $field->colWidth = 5;
        $field->continue = false;
        $field->enableDescription = true;
        $form->addField($field);
        
        $field = new main\mgLibs\forms\CheckboxField();
        $field->name = 'auto_renew_invoice_one_time';
        $field->options = ['auto_renew_invoice_one_time'];
        $field->value = $input['auto_renew_invoice_one_time'] ? ['auto_renew_invoice_one_time'] : [''];
        $field->inline = true;
        $field->colWidth = 3;
        $field->continue = true;
        $field->enableDescription = true;
        $form->addField($field);
                
        $field = new main\mgLibs\forms\SelectField();
        $field->disabled = $input['renew_invoice_days_one_time'] ? false : true;
        $field->name = 'renew_invoice_days_one_time';
        $field->required = true;
        $field->value = $input['renew_invoice_days_one_time'];
        $field->translateOptions = false;
        $field->inline = true;
        $field->colWidth = 2;
        $field->continue = false;        
        $field->enableDescription = true;
        $field->options = array('90'=>'90', '60'=>'60', '45'=>'45', '30'=>'30', '15'=>'15');         
        $field->error = $this->getFieldError('renew_invoice_days_one_time');
        $form->addField($field);
        
        $field = new main\mgLibs\forms\CheckboxField();
        $field->name = 'send_expiration_notification_one_time';
        $field->options = ['send_expiration_notification_one_time'];
        $field->value = $input['send_expiration_notification_one_time'] ? ['send_expiration_notification_one_time'] : [''];
        $field->inline = true;
        $field->colWidth = 5;
        $field->continue = false;
        $field->enableDescription = true;
        $form->addField($field);
        
        $field        = new main\mgLibs\forms\LegendField();
        $field->name  = 'csr_generator_legend';
        $form->addField($field);
        
        $field = new main\mgLibs\forms\CheckboxField();
        $field->name = 'display_csr_generator';
        $field->options = ['displayCsrGenerator'];
        $field->value = $input['display_csr_generator'] ? ['displayCsrGenerator'] : [''];
        $form->addField($field);
        $field->inline = true;
        $field->colWidth = 2;
        $field->continue = true;
        
        $field = new main\mgLibs\forms\SelectField();
        $field->disabled = $input['display_csr_generator'] ? false : true;
        $field->name = 'default_csr_generator_country';
        $field->required = true;
        $field->value = $input['default_csr_generator_country'];
        $field->translateOptions = false;
        $field->inline = true;
        $field->colWidth = 3;
        $field->continue = false;        
        $field->enableDescription = true;
        $field->options = \MGModule\GGSSLWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountriesForMgAddonDropdown();         
        $field->error = $this->getFieldError('default_csr_generator_country');
        $form->addField($field);
        
        $field        = new main\mgLibs\forms\LegendField();
        $field->name  = 'send_certificate_template';
        $form->addField($field);
        
        $field = new main\mgLibs\forms\SelectField();
        $field->disabled =  false ;
        $field->name = 'send_certificate_template';
        $field->required = true;
        $field->value = ($input['send_certificate_template'] == NULL) ? \MGModule\GGSSLWHMCS\eServices\EmailTemplateService::getTemplate('GoGetSSL - Send Certificate')->id : $input['send_certificate_template'] ;
        $field->translateOptions = false;
        $field->inline = true;
        $field->colWidth = 4;
        $field->continue = false;        
        $field->enableDescription = true;
        $field->options = $this->prepareGeneralEmailTemplatedArray(\MGModule\GGSSLWHMCS\eServices\EmailTemplateService::getGeneralTemplates());         
        $field->error = $this->getFieldError('send_certificate_template');
        $form->addField($field); 
        
        $field        = new main\mgLibs\forms\LegendField();
        $field->name  = 'client_area_summary_orders';
        $form->addField($field);        
               
        $field = new main\mgLibs\forms\SelectField();
        $field->disabled =  false ;
        $field->name = 'summary_expires_soon_days';
        $field->required = true;
        $field->value = $input['summary_expires_soon_days'];
        $field->translateOptions = false;
        $field->inline = true;
        $field->colWidth = 3;
        $field->continue = false;        
        $field->enableDescription = true;
        $field->options = array('30'=>'30', '15'=>'15', '10'=>'10');         
        $field->error = $this->getFieldError('summary_expires_soon_days');
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
        $vars['cronCommandLine2'] = 'php -q '.ROOTDIR.DS.'modules'.DS.'addons'.DS.'GGSSLWHMCS'.DS.'cron'.DS.'certificateStatsLoader.php';
        $vars['cronCommandLine3'] = 'php -q '.ROOTDIR.DS.'modules'.DS.'addons'.DS.'GGSSLWHMCS'.DS.'cron'.DS.'notifier.php';
        $vars['cronCommandLine4'] = 'php -q '.ROOTDIR.DS.'modules'.DS.'addons'.DS.'GGSSLWHMCS'.DS.'cron'.DS.'certificateSender.php';
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
                $checkFieldsArray = array(
                    'use_admin_contact', 
                    'display_csr_generator',
                    'auto_renew_invoice_one_time',
                    'auto_renew_invoice_reccuring',
                    'send_expiration_notification_reccuring',
                    'send_expiration_notification_one_time',
                    );
                foreach($checkFieldsArray as $field)
                {
                    if(isset($input[$field])) {
                        $input[$field] = true;
                    } else {
                        $input[$field] = false;
                    }
                }
                if(!$input['auto_renew_invoice_reccuring'])
                {
                    $input['renew_invoice_days_reccuring'] = NULL;
                }
                if(!$input['auto_renew_invoice_one_time'])
                {
                    $input['renew_invoice_days_one_time'] = NULL;
                }                
                if(!$input['display_csr_generator'])
                {
                    $input['default_csr_generator_country'] = NULL;
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
    
    private function prepareGeneralEmailTemplatedArray($templates)
    {
        $templatesArray = array();
        
        foreach ($templates as  $template)
        {
            $templatesArray[$template->id] = $template->name;           
        }
        
        return $templatesArray;
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
