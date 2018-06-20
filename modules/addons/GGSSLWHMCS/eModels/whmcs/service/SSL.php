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
        if(\MGModule\GGSSLWHMCS\eHelpers\Whmcs::isWHMCS73()) {
            $value = json_decode($value);
        } else {
            $value = unserialize($value);
        }
        
        return $value;
    }

    public function setConfigdataAttribute($value) {
        if(\MGModule\GGSSLWHMCS\eHelpers\Whmcs::isWHMCS73()) {
            $value = json_encode($value);
        } else {
            $value = serialize($value);
        }
        $this->attributes['configdata'] = $value;
    }

    public function setConfigdataKey($key, $value) {
        $c                = (array)$this->configdata;
        $c[$key]          = $value;
        $this->configdata = $c;
    }

    public function getConfigdataKey($key) {
        $c = (array)$this->configdata;
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

    public function setSansDomains($domains) {
        $fileds                 =  (array)$this->getConfigdataKey('fields');
        $fileds['sans_domains'] = $domains;
        $this->setConfigdataKey('fields', $fileds);

    }

    public function setApproverEmails($emails) {
        $fileds['approveremails'] = $emails;
        $this->setConfigdataKey('fields', $fileds);

    }

    public function setApproverEmail($email) {
        $fileds                  =  (array)$this->getConfigdataKey('fields');
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

    public function setRemoteId($id) {
        $this->remoteid = $id;
    }
    
    public function setUserId($id) {
        $this->userid = $id;
    }
    
    public function setServiceId($id) {
        $this->serviceid = $id;
    }
    
    public function setAddonId($id) {
        $this->addon_id = $id;
    }
    
    public function setModule($name) {
        $this->module = $name;
    }
    
    public function setCertType($type) {
        $this->certtype = $type;
    }
    
    public function setCompletionDate($date) {
        $this->completiondate = $date;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
}
