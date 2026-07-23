<?php

namespace MGModule\SSLCENTERWHMCS\models\cronQueue;

use Illuminate\Database\Capsule\Manager as Capsule;

class Repository
{
    public $tableName = 'SSLCENTER_cron_queue';

    public function addTask($clientId, $serviceId, $type, array $data = [])
    {
        Capsule::table($this->tableName)->insert([
            'client_id'  => (int) $clientId,
            'service_id' => (int) $serviceId,
            'type'       => $type,
            'status'     => 'pending',
            'attempts'   => 0,
            'domain'     => isset($data['domain']) ? $data['domain'] : '',
            'data'       => json_encode($data),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getPendingTasks($type = null, $limit = 20)
    {
        $query = Capsule::table($this->tableName)
            ->where('status', 'pending')
            ->where('attempts', '<', 5)
            ->orderBy('created_at', 'asc')
            ->limit($limit);

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    public function markProcessing($id)
    {
        Capsule::table($this->tableName)->where('id', $id)->update([
            'status'     => 'processing',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markDone($id)
    {
        Capsule::table($this->tableName)->where('id', $id)->update([
            'status'     => 'done',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markPending($id, $message = '')
    {
        Capsule::table($this->tableName)->where('id', $id)->update([
            'status'        => 'pending',
            'error_message' => $message ? substr($message, 0, 512) : null,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateTaskData($id, array $data)
    {
        Capsule::table($this->tableName)->where('id', $id)->update([
            'data'       => json_encode($data),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function resetStuckProcessing($minutesOld = 15)
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . (int) $minutesOld . ' minutes'));
        Capsule::table($this->tableName)
            ->where('status', 'processing')
            ->where('updated_at', '<', $cutoff)
            ->update([
                'status'        => 'pending',
                'error_message' => 'Auto-recovered from stuck processing state',
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
    }

    public function markError($id, $message = '')
    {
        $task = Capsule::table($this->tableName)->where('id', $id)->first();
        $attempts = $task ? (int) $task->attempts + 1 : 1;
        $newStatus = $attempts >= 5 ? 'error' : 'pending';

        Capsule::table($this->tableName)->where('id', $id)->update([
            'status'       => $newStatus,
            'attempts'     => $attempts,
            'error_message'=> substr($message, 0, 512),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function createTable()
    {
        if (!Capsule::schema()->hasTable($this->tableName)) {
            Capsule::schema()->create($this->tableName, function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('service_id');
                $table->string('type');
                $table->string('status')->default('pending');
                $table->string('domain')->nullable();
                $table->integer('attempts')->default(0);
                $table->text('data')->nullable();
                $table->string('error_message', 512)->nullable();
                $table->datetime('created_at');
                $table->datetime('updated_at');
            });
        }

        if (Capsule::schema()->hasTable($this->tableName) && !Capsule::schema()->hasColumn($this->tableName, 'error_message')) {
            Capsule::schema()->table($this->tableName, function ($table) {
                $table->string('error_message', 512)->nullable();
            });
        }
    }

    public function dropTable()
    {
        Capsule::schema()->dropIfExists($this->tableName);
    }
}
