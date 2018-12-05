<?php

namespace MGModule\SSLCENTERWHMCS\eModels\whmcs;

class EmailTemplate extends \Illuminate\Database\Eloquent\Model {

    public $timestamps = false;
    protected $table   = 'tblemailtemplates';

    public function scopeWhereName($query, $name) {
        $query->where('name', '=', $name);
    }
    
    public function scopeWhereType($query, $type) {
        $query->where('type', '=', $type);
    }
    
    public function scopeWhereId($query, $id) {
        $query->where('id', '=', $id);
    }
}
