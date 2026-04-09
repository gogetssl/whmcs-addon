<?php

namespace MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain;

use Illuminate\Database\Capsule\Manager as Capsule;
use MGModule\SSLCENTERWHMCS\mgLibs\models\Repository as MainRepository;

class Repository extends MainRepository
{
    public $tableName = 'SSLCENTER_acme_subscription_domains';

    public function getModelClass()
    {
        return __NAMESPACE__ . '\AcmeSubscriptionDomain';
    }

    public function getByServiceId($serviceId)
    {
        return Capsule::table($this->tableName)
            ->where('service_id', (int) $serviceId)
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function addDomain($serviceId, $domain, $type = 'single')
    {
        $domain = strtolower(trim($domain));
        $exists = Capsule::table($this->tableName)
            ->where('service_id', (int) $serviceId)
            ->where('domain', $domain)
            ->first();

        if ($exists)
        {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        Capsule::table($this->tableName)->insert([
            'service_id'  => (int) $serviceId,
            'domain'      => $domain,
            'domain_type' => $type,
            'status'      => 'added',
            'added_at'    => $now,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return true;
    }

    public function removeDomain($serviceId, $domain)
    {
        $now = date('Y-m-d H:i:s');
        return Capsule::table($this->tableName)
            ->where('service_id', (int) $serviceId)
            ->where('domain', strtolower(trim($domain)))
            ->where('status', 'added')
            ->update([
                'status'     => 'removed',
                'removed_at' => $now,
                'updated_at' => $now,
            ]);
    }

    public function createTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('service_id');
                $table->string('domain');
                $table->string('domain_type')->default('single');
                $table->string('status')->default('added');
                $table->datetime('added_at')->nullable();
                $table->datetime('removed_at')->nullable();
                $table->timestamps();
                $table->index(['service_id']);
                $table->unique(['service_id', 'domain']);
            });
        }
    }

    public function updateTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            $this->createTable();
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
