<?php

namespace MGModule\SSLCENTERWHMCS\models\logs;

use MGModule\SSLCENTERWHMCS\mgLibs\models\Repository as MainRepository;
use Illuminate\Database\Capsule\Manager as Capsule;


class Repository extends MainRepository {

    public $tableName = 'SSLCENTER_logs';

    public function getModelClass() {
        return __NAMESPACE__ . '\Log';
    }

    public function get() {
        return Capsule::table($this->tableName)->get();
    }

    public function getList($limit, $offset, $orderBy = [], $search = '')
    {
        if(empty($search))
        {
            $query = Capsule::table($this->tableName)->limit($limit)->offset($offset)->orderBy($orderBy[0], $orderBy[1]);
            return [
                'results' => $query->get(),
                'count' =>$query->count()
            ];
        }

        $query = Capsule::table($this->tableName)
            ->select([$this->tableName.'.*'])
            ->join('tblclients', 'tblclients.id', '=', $this->tableName.'.client_id')
            ->join('tblhosting', 'tblhosting.id', '=', $this->tableName.'.service_id')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where(Capsule::raw("CONCAT(tblclients.firstname,' ',tblclients.lastname,' ',tblclients.companyname)"), 'like', '%'.$search.'%')
            ->orWhere(Capsule::raw("CONCAT(tblhosting.domain,' - ',tblproducts.name)"), 'like', '%'.$search.'%')
            ->orWhere($this->tableName.'.type', 'like', '%'.$search.'%')
            ->orWhere($this->tableName.'.msg', 'like', '%'.$search.'%')
            ->orWhere($this->tableName.'.date', 'like', '%'.$search.'%')
            ->limit($limit)->offset($offset)->orderBy($orderBy[0], $orderBy[1]);

        return [
            'results' => $query->get(),
            'count' =>$query->count()
        ];
    }

    public function remove($id) {
        Capsule::table($this->tableName)->where('id', $id)->delete();
    }

    public function clear() {
        Capsule::table($this->tableName)->truncate();
    }

    public function addLog($client_id, $service_id, $type, $msg)
    {
        Capsule::table($this->tableName)->insert([
            'client_id' => $client_id,
            'service_id' => $service_id,
            'type' => $type,
            'msg' => $msg,
            'date' => date('Y-m-d H:i:s')
        ]);
    }

    public function createLogsTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id');
                $table->string('type');
                $table->text('msg');
                $table->datetime('date');
            });
        }
    }

    public function updateLogsTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id');
                $table->string('type');
                $table->text('msg');
                $table->datetime('date');
            });
        }
    }

    public function dropLogsTable()
    {
        if (Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->dropIfExists($this->tableName);
        }
    }

}
