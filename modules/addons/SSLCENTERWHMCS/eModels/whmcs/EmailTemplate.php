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
    
    /**
     * Master templates only. WHMCS stores each translation as an extra row with the same
     * name and a language set; sending (SendEmail / sendMessage) needs the master row.
     */
    public function scopeMaster($query) {
        $query->where(function ($q) {
            $q->where('language', '=', '')->orWhereNull('language');
        });
    }

    public function scopeWhereId($query, $id) {
        $query->where('id', '=', $id);
    }
}
