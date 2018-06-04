<?php

namespace MGModule\GGSSLWHMCS\models\apiConfiguration;

use Illuminate\Database\Capsule\Manager as Capsule;

class Repository extends \MGModule\GGSSLWHMCS\mgLibs\models\Repository
{
    public $tableName = 'mgfw_ggssl_api_configuration';

    public function getModelClass()
    {
        return __NAMESPACE__ . '\ApiConfigurationItem';
    }

    public function get()
    {
        return Capsule::table($this->tableName)->first();
    }

    public function setConfiguration($params)
    {
        if (is_null($this->get()))
        {
            Capsule::table($this->tableName)->insert(
                    [
                        'api_login'                              => $params['api_login'],
                        'api_password'                           => $params['api_password'],
                        'use_admin_contact'                      => $params['use_admin_contact'],
                        'display_csr_generator'                  => $params['display_csr_generator'],
                        'tech_firstname'                         => $params['tech_firstname'],
                        'tech_lastname'                          => $params['tech_lastname'],
                        'tech_organization'                      => $params['tech_organization'],
                        'tech_addressline1'                      => $params['tech_addressline1'],
                        'tech_phone'                             => $params['tech_phone'],
                        'tech_title'                             => $params['tech_title'],
                        'tech_email'                             => $params['tech_email'],
                        'tech_city'                              => $params['tech_city'],
                        'tech_country'                           => $params['tech_country'],
                        'tech_fax'                               => $params['tech_fax'],
                        'tech_postalcode'                        => $params['tech_postalcode'],
                        'tech_region'                            => $params['tech_region'],
                        'auto_renew_invoice_one_time'            => $params['auto_renew_invoice_one_time'],
                        'auto_renew_invoice_reccuring'           => $params['auto_renew_invoice_reccuring'],
                        'send_expiration_notification_reccuring' => $params['send_expiration_notification_reccuring'],
                        'send_expiration_notification_one_time'  => $params['send_expiration_notification_one_time'],                        
                        'renew_invoice_days_reccuring'           => $params['renew_invoice_days_reccuring'],
                        'renew_invoice_days_one_time'            => $params['renew_invoice_days_one_time'],
            ]);
        }
        else
        {
            Capsule::table($this->tableName)->update(
                    [
                        'api_login'                              => $params['api_login'],
                        'api_password'                           => $params['api_password'],
                        'use_admin_contact'                      => $params['use_admin_contact'],
                        'display_csr_generator'                  => $params['display_csr_generator'],
                        'tech_firstname'                         => $params['tech_firstname'],
                        'tech_lastname'                          => $params['tech_lastname'],
                        'tech_organization'                      => $params['tech_organization'],
                        'tech_addressline1'                      => $params['tech_addressline1'],
                        'tech_phone'                             => $params['tech_phone'],
                        'tech_title'                             => $params['tech_title'],
                        'tech_email'                             => $params['tech_email'],
                        'tech_city'                              => $params['tech_city'],
                        'tech_country'                           => $params['tech_country'],
                        'tech_fax'                               => $params['tech_fax'],
                        'tech_postalcode'                        => $params['tech_postalcode'],
                        'tech_region'                            => $params['tech_region'],
                        'auto_renew_invoice_one_time'            => $params['auto_renew_invoice_one_time'], //
                        'auto_renew_invoice_reccuring'           => $params['auto_renew_invoice_reccuring'],
                        'send_expiration_notification_reccuring' => $params['send_expiration_notification_reccuring'],
                        'send_expiration_notification_one_time'  => $params['send_expiration_notification_one_time'],
                        'renew_invoice_days_reccuring'           => $params['renew_invoice_days_reccuring'],
                        'renew_invoice_days_one_time'            => $params['renew_invoice_days_one_time'],
            ]);
        }
    }

    public function createApiConfigurationTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->string('api_login');
                $table->string('api_password');
                $table->boolean('use_admin_contact');
                $table->boolean('display_csr_generator');
                $table->boolean('auto_renew_invoice_one_time');
                $table->boolean('auto_renew_invoice_reccuring');
                $table->boolean('send_expiration_notification_reccuring');
                $table->boolean('send_expiration_notification_one_time');
                $table->string('tech_firstname');
                $table->string('tech_lastname');
                $table->string('tech_organization');
                $table->string('tech_addressline1');
                $table->string('tech_phone');
                $table->string('tech_title');
                $table->string('tech_email');
                $table->string('tech_city');
                $table->string('tech_country');
                $table->string('tech_fax');
                $table->string('tech_postalcode');
                $table->string('tech_region');
                $table->string('renew_invoice_days_reccuring')->nullable();
                $table->string('renew_invoice_days_one_time')->nullable();
            });
        }
    }

    public function updateApiConfigurationTable()
    {
        if (Capsule::schema()->hasTable($this->tableName))
        {
            if (!Capsule::schema()->hasColumn($this->tableName, 'auto_renew_invoice_one_time'))
            {
                Capsule::schema()->table($this->tableName, function($table)
                {
                    $table->boolean('auto_renew_invoice_one_time');
                });
            }
            if (!Capsule::schema()->hasColumn($this->tableName, 'auto_renew_invoice_reccuring'))
            {
                Capsule::schema()->table($this->tableName, function($table)
                {
                    $table->boolean('auto_renew_invoice_reccuring');
                });
            }
            if (!Capsule::schema()->hasColumn($this->tableName, 'send_expiration_notification_reccuring'))
            {
                Capsule::schema()->table($this->tableName, function($table)
                {
                    $table->boolean('send_expiration_notification_reccuring');
                });
            }
            if (!Capsule::schema()->hasColumn($this->tableName, 'send_expiration_notification_one_time'))
            {
                Capsule::schema()->table($this->tableName, function($table)
                {
                    $table->boolean('send_expiration_notification_one_time');
                });
            }
            if (!Capsule::schema()->hasColumn($this->tableName, 'renew_invoice_days_reccuring'))
            {
                Capsule::schema()->table($this->tableName, function($table)
                {
                    $table->string('renew_invoice_days_reccuring')->nullable();
                });
            }
            if (!Capsule::schema()->hasColumn($this->tableName, 'renew_invoice_days_one_time'))
            {
                Capsule::schema()->table($this->tableName, function($table)
                {
                    $table->string('renew_invoice_days_one_time')->nullable();
                });
            }
           /* 'renew_invoice_days_reccuring'          
                        'renew_invoice_days_one_time'*/
        }
    }

    public function dropApiConfigurationTable()
    {
        Capsule::schema()->dropIfExists($this->tableName);
    }
}
