<?php

namespace MGModule\GGSSLWHMCS\eModels\whmcs;

class EmailTemplate extends \Illuminate\Database\Eloquent\Model {

    public $timestamps = false;
    protected $table   = 'tblemailtemplates';

    public function scopeWhereName($query, $name) {
        $query->where('name', '=', $name);
    }
}
