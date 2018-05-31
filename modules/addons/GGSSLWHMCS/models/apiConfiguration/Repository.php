<?php

namespace MGModule\GGSSLWHMCS\models\apiConfiguration;
use Illuminate\Database\Capsule\Manager as Capsule;


class Repository extends \MGModule\GGSSLWHMCS\mgLibs\models\Repository {

    public $tableName = 'mgfw_ggssl_api_configuration';

    public function getModelClass() {
        return __NAMESPACE__ . '\ApiConfigurationItem';
    }

    public function get() {
        return Capsule::table($this->tableName)->first();
    }

    public function setConfiguration($params) {
        if (is_null($this->get())) {
            Capsule::table($this->tableName)->insert(
                    [
                        'api_login'         => $params['api_login'],
                        'api_password'      => $params['api_password'],
                        'use_admin_contact' => $params['use_admin_contact'],
                        'tech_firstname'    => $params['tech_firstname'],
                        'tech_lastname'     => $params['tech_lastname'],
                        'tech_organization' => $params['tech_organization'],
                        'tech_addressline1' => $params['tech_addressline1'],
                        'tech_phone'        => $params['tech_phone'],
                        'tech_title'        => $params['tech_title'],
                        'tech_email'        => $params['tech_email'],
                        'tech_city'         => $params['tech_city'],
                        'tech_country'      => $params['tech_country'],
                        'tech_fax'          => $params['tech_fax'],
                        'tech_postalcode'   => $params['tech_postalcode'],
                        'tech_region'       => $params['tech_region'],
            ]);
        } else {
            Capsule::table($this->tableName)->update(
                    [
                        'api_login'         => $params['api_login'],
                        'api_password'      => $params['api_password'],
                        'use_admin_contact' => $params['use_admin_contact'],
                        'tech_firstname'    => $params['tech_firstname'],
                        'tech_lastname'     => $params['tech_lastname'],
                        'tech_organization' => $params['tech_organization'],
                        'tech_addressline1' => $params['tech_addressline1'],
                        'tech_phone'        => $params['tech_phone'],
                        'tech_title'        => $params['tech_title'],
                        'tech_email'        => $params['tech_email'],
                        'tech_city'         => $params['tech_city'],
                        'tech_country'      => $params['tech_country'],
                        'tech_fax'          => $params['tech_fax'],
                        'tech_postalcode'   => $params['tech_postalcode'],
                        'tech_region'       => $params['tech_region'],
            ]);
        }
    }

    public function createApiConfigurationTable() {
        if (!Capsule::schema()->hasTable($this->tableName)) {
            Capsule::schema()->create($this->tableName, function($table) {
                $table->string('api_login');
                $table->string('api_password');
                $table->boolean('use_admin_contact');
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
            });
        }
    }

    public function dropApiConfigurationTable() {
        Capsule::schema()->dropIfExists($this->tableName);
    }

}
