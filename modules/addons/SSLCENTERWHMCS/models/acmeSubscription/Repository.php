<?php

namespace MGModule\SSLCENTERWHMCS\models\acmeSubscription;

use Illuminate\Database\Capsule\Manager as Capsule;
use MGModule\SSLCENTERWHMCS\mgLibs\models\Repository as MainRepository;

class Repository extends MainRepository
{
    public $tableName = 'SSLCENTER_acme_subscriptions';

    public function getModelClass()
    {
        return __NAMESPACE__ . '\AcmeSubscription';
    }

    public function getByServiceId($serviceId)
    {
        return Capsule::table($this->tableName)
            ->where('service_id', (int) $serviceId)
            ->first();
    }

    public function upsertByServiceId($serviceId, array $data)
    {
        $existing = $this->getByServiceId($serviceId);
        $now      = date('Y-m-d H:i:s');

        if ($existing)
        {
            $data['updated_at'] = $now;
            Capsule::table($this->tableName)
                ->where('service_id', (int) $serviceId)
                ->update($data);

            return $this->getByServiceId($serviceId);
        }

        $data['service_id']  = (int) $serviceId;
        $data['created_at']  = $now;
        $data['updated_at']  = $now;
        $data['status']      = isset($data['status']) ? $data['status'] : 'pending';
        $data['auto_renew']  = isset($data['auto_renew']) ? (int) $data['auto_renew'] : 1;

        Capsule::table($this->tableName)->insert($data);

        return $this->getByServiceId($serviceId);
    }

    public function createTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('service_id')->unique();
                $table->integer('client_id')->nullable();
                $table->integer('api_order_id')->nullable();
                $table->integer('acme_id')->nullable();
                $table->string('status')->default('pending');
                $table->string('acme_account_id')->nullable();
                $table->string('eab_kid')->nullable();
                $table->text('eab_hmac_key')->nullable();
                $table->text('server_url')->nullable();
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->date('renewal_date')->nullable();
                $table->boolean('auto_renew')->default(1);
                $table->datetime('cancelled_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function updateTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            $this->createTable();
            return;
        }

        if (!Capsule::schema()->hasColumn($this->tableName, 'auto_renew'))
        {
            Capsule::schema()->table($this->tableName, function($table)
            {
                $table->boolean('auto_renew')->default(1);
            });
        }

        if (!Capsule::schema()->hasColumn($this->tableName, 'acme_id'))
        {
            Capsule::schema()->table($this->tableName, function($table)
            {
                $table->integer('acme_id')->nullable()->after('api_order_id');
            });
        }
    }

    public function dropTable()
    {
        if (Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->dropIfExists($this->tableName);
        }
    }
}
