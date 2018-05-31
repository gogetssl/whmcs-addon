<?php

namespace MGModule\GGSSLWHMCS\eModels\whmcs\service;

use Illuminate\Database\Capsule\Manager as Capsule;

class SSL extends \Illuminate\Database\Eloquent\Model {

    public $timestamps = false;
    protected $table   = 'tblsslorders';
    
    public function scopeWhereServiceId($query, $id) {     
        $query->where('serviceid', '=', $id);

    }

    public function getConfigdataAttribute($value) {
        return unserialize($value);

    }

    public function setConfigdataAttribute($value) {
        $this->attributes['configdata'] = serialize($value);

    }

    public function setConfigdataKey($key, $value) {
        $c                = $this->configdata;
        $c[$key]          = $value;
        $this->configdata = $c;

    }

    public function getConfigdataKey($key) {
        $c = $this->configdata;
        return $c[$key];

    }

    public function getCsr() {
        return $this->getConfigdataKey('csr');

    }

    public function setCsr($value) {
        $this->setConfigdataKey('csr', $value);

    }

    public function getCrt() {
        return $this->getConfigdataKey('crt');

    }

    public function setCrt($value) {
        $this->setConfigdataKey('crt', $value);

    }

    public function getCa() {
        return $this->getConfigdataKey('ca');

    }
    public function getPrivateKey() {
        return $this->getConfigdataKey('private_key');
    }

    public function setCa($value) {
        $this->setConfigdataKey('ca', $value);

    }

    public function getOrderStatus() {
        return $this->getConfigdataKey('orderStatus');

    }

    public function setOrderStatus($value) {
        $this->setConfigdataKey('orderStatus', $value);

    }

    public function setAsFetched() {
        $this->setConfigdataKey('fetched', true);

    }

    public function setAsNotFetched() {
        $this->setConfigdataKey('fetched', false);

    }

    public function isFetched() {
        return $this->getConfigdataKey('fetched') === true;

    }

    public function setRemoteId($id) {
        $this->remoteid = $id;

    }

    public function setSansDomains($domains) {
        $fileds                 = $this->getConfigdataKey('fields');
        $fileds['sans_domains'] = $domains;
        $this->setConfigdataKey('fields', $fileds);

    }

    public function setApproverEmails($emails) {
        $fileds                   = $this->getConfigdataKey('fields');
        $fileds['approveremails'] = $emails;
        $this->setConfigdataKey('fields', $fileds);

    }

    public function setApproverEmail($email) {
        $fileds                  = $this->getConfigdataKey('fields');
        $fileds['approveremail'] = $email;
        $this->setConfigdataKey('fields', $fileds);

    }    
    
    public function getWhere($where) {   
        $query = Capsule::table('tblsslorders');
        
        if (!empty($where))
        {
            foreach ($where as $column => $value)
            {
                $query = $query->where("$column",'=',"$value");
            }
        }
                
        return $query;
    }
}
