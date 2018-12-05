<?php

namespace MGModule\SSLCENTERWHMCS\models\userCommission;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Description of repository
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Repository extends \MGModule\SSLCENTERWHMCS\mgLibs\models\Repository
{
    public $tableName = 'mgfw_SSLCENTER_user_commission';

    public function getModelClass()
    {
        return __NAMESPACE__ . '\UserCommission';
    }
    
    /**
     *
     * @return ProductPrices[]
     */
    public function get()
    {
        return parent::get();
    }

    /**
     *
     * @return ProductPrices
     */
    public function fetchOne()
    {
        return parent::fetchOne();
    }

    public function onlyProductID($id)
    {
        $this->_filters['product_id'] = $id;

        return $this;
    }

    public function onlyClientID($id)
    {
        $this->_filters['client_id'] = $id;
        return $this;
    }

    public function onlyPeriod($period)
    {
        $this->_filters['period'] = $period;
        return $this;
    }

    public function createUserCommissionTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('product_id');
                $table->string('commission');
                $table->foreign('client_id')
                  ->references('id')->on('tblclients')
                  ->onDelete('cascade');
                $table->foreign('product_id')
                        ->references('id')->on('tblproducts')
                        ->onDelete('cascade');
            });
        }
    }

    public function updateUserCommissionTable()
    {
        if (Capsule::schema()->hasTable($this->tableName))
        {
            /* if (!Capsule::schema()->hasColumn($this->tableName, 'id'))
              {
              Capsule::schema()->table($this->tableName, function($table)
              {
              $table->integer('id');
              });
              } */
        }
        else
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('product_id');
                $table->string('commission');
                $table->foreign('client_id')
                  ->references('id')->on('tblclients')
                  ->onDelete('cascade');
                $table->foreign('product_id')
                        ->references('id')->on('tblproducts')
                        ->onDelete('cascade');
            });
        }
    }

    public function dropUserCommissionTable()
    {
        if (Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->table($this->tableName, function(Blueprint $table)
            {
                $table->dropForeign('mgfw_SSLCENTER_user_commission_client_id_foreign');
                $table->dropForeign('mgfw_SSLCENTER_user_commission_product_id_foreign');
            });
        }
        Capsule::schema()->dropIfExists($this->tableName);
    }
}
