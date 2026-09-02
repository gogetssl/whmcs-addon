<?php

namespace MGModule\SSLCENTERWHMCS\models\aisSubscription;

use Illuminate\Database\Capsule\Manager as Capsule;
use MGModule\SSLCENTERWHMCS\mgLibs\models\Repository as MainRepository;

class Repository extends MainRepository
{
    public $tableName = 'SSLCENTER_ais_subscriptions';

    public function getModelClass()
    {
        return __NAMESPACE__ . '\AisSubscription';
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

        $data['service_id'] = (int) $serviceId;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $data['status']     = isset($data['status']) ? $data['status'] : 'pending';

        Capsule::table($this->tableName)->insert($data);

        return $this->getByServiceId($serviceId);
    }

    public function createTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function ($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('service_id')->unique();
                $table->integer('client_id')->nullable();
                $table->integer('api_order_id')->nullable();
                $table->integer('ais_item_id')->nullable();
                $table->string('status')->default('pending');
                $table->text('login_sso_link')->nullable();
                $table->text('manage_sso_link')->nullable();
                $table->string('autoinstall_status')->nullable();
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->date('renewal_date')->nullable();
                $table->boolean('auto_renew')->default(1);
                $table->timestamp('cancelled_at')->nullable();
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
            Capsule::schema()->table($this->tableName, function ($table) {
                $table->boolean('auto_renew')->default(1)->after('renewal_date');
            });
        }

        if (!Capsule::schema()->hasColumn($this->tableName, 'cancelled_at'))
        {
            Capsule::schema()->table($this->tableName, function ($table) {
                $table->timestamp('cancelled_at')->nullable()->after('auto_renew');
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
