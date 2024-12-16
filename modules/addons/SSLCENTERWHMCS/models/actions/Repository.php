<?php

namespace MGModule\SSLCENTERWHMCS\models\actions;

use MGModule\SSLCENTERWHMCS\mgLibs\models\Repository as MainRepository;
use Illuminate\Database\Capsule\Manager as Capsule;


class Repository extends MainRepository {

    public $tableName = 'SSLCENTER_actions';

    public function getModelClass() {
        return __NAMESPACE__ . '\Action';
    }

    public function addAction($client_id, $service_id, $action, $status)
    {
        $check = Capsule::table($this->tableName)
            ->where('client_id', $client_id)
            ->where('service_id', $service_id)
            ->where('action', $action)
            ->first();

        if(isset($check->id))
        {
            Capsule::table($this->tableName)
                ->where('client_id', $client_id)
                ->where('service_id', $service_id)
                ->where('action', $action)
                ->update([
                'status' => $status,
                'date' => date('Y-m-d H:i:s'),
            ]);
        }
        else
        {
            Capsule::table($this->tableName)->insert([
                'client_id' => $client_id,
                'service_id' => $service_id,
                'action' => $action,
                'status' => $status,
                'date' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function checkStepThree($service_id)
    {
        $check = Capsule::table($this->tableName)->where('service_id', $service_id)->first();

        if(isset($check->status) && $check->status == 'processing')
        {
            return true;
        }

        if(isset($check->status) && $check->status == 'success' && isset($check->date) && date('Y-m-d', strtotime($check->date)) == date('Y-m-d'))
        {
            return true;
        }

        return false;
    }

    public function updateStatusStepThree($service_id, $status)
    {
        Capsule::table($this->tableName)->where('service_id', $service_id)->update(['status' => $status]);
    }

    public function get() {
        return Capsule::table($this->tableName)->get();
    }

    public function remove($id) {
        Capsule::table($this->tableName)->where('id', $id)->delete();
    }

    public function clear() {
        Capsule::table($this->tableName)->truncate();
    }

    public function createActionsTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id');
                $table->string('action');
                $table->text('status');
                $table->datetime('date');
            });
        }
    }

    public function updateActionsTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id');
                $table->string('action');
                $table->text('status');
                $table->datetime('date');
            });
        }
    }

    public function dropActionsTable()
    {
        if (Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->dropIfExists($this->tableName);
        }
    }

}
