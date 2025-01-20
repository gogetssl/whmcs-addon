<?php

namespace MGModule\SSLCENTERWHMCS\models\orders;

use MGModule\SSLCENTERWHMCS\mgLibs\models\Repository as MainRepository;
use Illuminate\Database\Capsule\Manager as Capsule;


class Repository extends MainRepository {

    public $tableName = 'SSLCENTER_orders';

    public function getModelClass() {
        return __NAMESPACE__ . '\Order';
    }

    public function get() {
        return Capsule::table($this->tableName)->get();
    }

    public function remove($id) {
        Capsule::table($this->tableName)->where('id', $id)->delete();
    }

    public function getOrdersInstallation()
    {
        return Capsule::table($this->tableName)
            ->select([$this->tableName.'.*', 'tblsslorders.configdata', 'tblhosting.domain'])
            ->join('tblhosting', 'tblhosting.id', '=', $this->tableName.'.service_id')
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', $this->tableName.'.service_id')
            ->where($this->tableName.'.status', 'Pending Installation')
            ->where('tblsslorders.configdata', 'like', '%-----BEGIN CERTIFICATE-----%')
            ->get();
    }

    public function checkOrdersInstallation($serviceId)
    {
        $checkOrder = Capsule::table($this->tableName)
            ->where('service_id', $serviceId)
            ->first();

        if(!isset($checkOrder->id))
        {
            return true;
        }

        if(isset($checkOrder->status) && $checkOrder->status == 'Pending Installation')
        {
            return true;
        }

        return false;
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
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->where(Capsule::raw("CONCAT(tblclients.firstname,' ',tblclients.lastname,' ',tblclients.companyname)"), 'like', '%'.$search.'%')
            ->orWhere(Capsule::raw("CONCAT(tblhosting.domain,' - ',tblproducts.name)"), 'like', '%'.$search.'%')
            ->orWhere(Capsule::raw("CONCAT(tblsslorders.remoteid)"), 'like', '%'.$search.'%')
            ->orWhere($this->tableName.'.verification_method', 'like', '%'.$search.'%')
            ->orWhere($this->tableName.'.status', 'like', '%'.$search.'%')
            ->orWhere($this->tableName.'.date', 'like', '%'.$search.'%')
            ->limit($limit)->offset($offset)->orderBy($orderBy[0], $orderBy[1]);

        return [
            'results' => $query->get(),
            'count' =>$query->count()
        ];
    }

    public function addOrder($clientId, $serviceId, $sslOrderId, $verificationMethod, $status, $data)
    {
        Capsule::table($this->tableName)->insert([
            'client_id' => $clientId,
            'service_id' => $serviceId,
            'ssl_order_id' => $sslOrderId,
            'verification_method' => $verificationMethod,
            'status' => $status,
            'data' => json_encode($data),
            'date' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateStatus($serviceid, $status)
    {
        Capsule::table($this->tableName)->where('service_id', $serviceid)->update([
            'status' => $status
        ]);
    }

    public function updateStatusById($id, $status)
    {
        Capsule::table($this->tableName)->where('id', $id)->update([
            'status' => $status
        ]);
    }

    public function createOrdersTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id');
                $table->integer('ssl_order_id');
                $table->string('verification_method');
                $table->string('status');
                $table->text('data');
                $table->datetime('date');
            });
        }
    }

    public function updateOrdersTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->create($this->tableName, function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id');
                $table->integer('ssl_order_id');
                $table->string('verification_method');
                $table->string('status');
                $table->text('data');
                $table->datetime('date');
            });
        }
    }

    public function dropOrdersTable()
    {
        if (Capsule::schema()->hasTable($this->tableName))
        {
            Capsule::schema()->dropIfExists($this->tableName);
        }
    }

}
