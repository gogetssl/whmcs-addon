<?php

namespace MGModule\SSLCENTERWHMCS\eModels\whmcs\service;

use Illuminate\Database\Capsule\Manager as Capsule;

class SSL extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;
    protected $table   = 'tblsslorders';
    
    public function scopeWhereServiceId($query, $id)
    {
        $query->where('serviceid', '=', $id);
    }
    public function scopeWhereRemoteId($query, $id)
    {
        $query->where('remoteid', '=', $id);
    }
    public function getConfigdataAttribute($value)
    {
        if (\MGModule\SSLCENTERWHMCS\eHelpers\Whmcs::isWHMCS73())
        {
            $value = json_decode($value);
        }
        else
        {
            $value = unserialize($value);
        }

        return $value;
    }

    public function setConfigdataAttribute($value)
    {
        if (\MGModule\SSLCENTERWHMCS\eHelpers\Whmcs::isWHMCS73())
        {
            $value = json_encode($value);
        }
        else
        {
            $value = serialize($value);
        }
        $this->attributes['configdata'] = $value;
    }

    public function setConfigdataKey($key, $value)
    {
        $c                = (array) $this->configdata;
        $c[$key]          = $value;
        $this->configdata = $c;
    }

    public function getRemoteId()
    {
        return $this->remoteid;
    }

    public function getConfigdataKey($key)
    {
        $c = (array) $this->configdata;
        return $c[$key];
    }

    public function getCsr()
    {
        return $this->getConfigdataKey('csr');
    }

    public function setCsr($value)
    {
        $this->setConfigdataKey('csr', $value);
    }

    public function getCrt()
    {
        return $this->getConfigdataKey('crt');
    }

    public function setCrt($value)
    {
        $this->setConfigdataKey('crt', $value);
    }

    public function getCa()
    {
        return $this->getConfigdataKey('ca');
    }

    public function getPrivateKey()
    {
        return $this->getConfigdataKey('private_key');
    }

    public function setCa($value)
    {
        $this->setConfigdataKey('ca', $value);
    }

    public function getOrderStatus()
    {
        return $this->getConfigdataKey('orderStatus');
    }

    public function setOrderStatus($value)
    {
        $this->setConfigdataKey('orderStatus', $value);
    }
    
    public function getOrderStatusDescription()
    {
        return $this->getConfigdataKey('order_status_description');
    }

    public function setOrderStatusDescription($value)
    {
        $this->setConfigdataKey('order_status_description', $value);
    }
    
    public function getApproverMethod()
    {
        return $this->getConfigdataKey('approver_method');
    }

    public function setApproverMethod($value)
    {
        $this->setConfigdataKey('approver_method', $value);
    }
    
    public function getDcvMethod()
    {
        return $this->getConfigdataKey('dcv_method');
    }

    public function setDcvMethod($value)
    {
        $this->setConfigdataKey('dcv_method', $value);
    }
    
    public function getProductId()
    {
        return $this->getConfigdataKey('product_id');
    }

    public function setProductId($id)
    {
        $this->setConfigdataKey('product_id', $id);
    }
    
    public function getProductBrand()
    {
        return $this->getConfigdataKey('product_brand');
    }

    public function setProductBrand($brand)
    {
        $this->setConfigdataKey('product_brand', $brand);
    }

    public function setAsFetched()
    {
        $this->setConfigdataKey('fetched', true);
    }

    public function setAsNotFetched()
    {
        $this->setConfigdataKey('fetched', false);
    }

    public function isFetched()
    {
        return $this->getConfigdataKey('fetched') === true;
    }

    public function setSansDomains($domains)
    {
        $fileds                 = (array) $this->getConfigdataKey('fields');
        $fileds['sans_domains'] = $domains;
        $this->setConfigdataKey('fields', $fileds);
    }

    public function setApproverEmails($emails)
    {
        $fileds['approveremails'] = $emails;
        $this->setConfigdataKey('fields', $fileds);
    }

    public function setApproverEmail($email)
    {
        $fileds                  = (array) $this->getConfigdataKey('fields');
        $fileds['approveremail'] = $email;
        $this->setConfigdataKey('fields', $fileds);
    }
    
    public function setSubscriptionStarts($date)
    {
        $this->setConfigdataKey('begin_date', $date);
    }
    
    public function getSubscriptionStarts($date)
    {
        return $this->getConfigdataKey('begin_date');
    }
    
    public function setSubscriptionEnds($date)
    {
        $this->setConfigdataKey('end_date', $date);
    }
    
    public function getSubscriptionEnd($date)
    {
        return $this->getConfigdataKey('end_date');
    }
    
    public function setValidFrom($date)
    {
        $this->setConfigdataKey('valid_from', $date);
    }
    
    public function getValidFrom()
    {
        return $this->getConfigdataKey('valid_from');
    }
    
    public function setValidTill($date)
    {
        $this->setConfigdataKey('valid_till', $date);
    }
    
    public function getDomain()
    {
        return $this->getConfigdataKey('domain');
    }
    
    public function setDomain($domain)
    {
        $this->setConfigdataKey('domain', $domain);
    }
    
    public function getValidTill()
    {
        return $this->getConfigdataKey('valid_till');
    }
    
    public function setPartnerOrderId($id)
    {
        $this->setConfigdataKey('partner_order_id', $id);
    }
    
    public function getPartnerOrderId()
    {
        return $this->getConfigdataKey('partner_order_id');
    }
    
    public function getSanDetails()
    {
        return $this->getConfigdataKey('san_details');
    }
    
    public function getSanDomains()
    {
        return $this->getConfigdataKey('fields');
    }
    
    public function setSanDetails($details)
    {
        $this->setConfigdataKey('san_details', $details);
    }
    public function setSSLStatus($status)
    {
        $this->setConfigdataKey('ssl_status', $status);
    }
    public function setSSLTotalDomains($domains)
    {
        $this->setConfigdataKey('total_domains', $domains);
    }    
    public function getSSLStatus()
    {
        return $this->getConfigdataKey('ssl_status');
    }
    public static function getWhere($where, $gogetssl = false)
    {
        $query = Capsule::table('tblsslorders');

        if (!empty($where))
        {
            foreach ($where as $column => $value)
            {
                $query = $query->where("$column", '=', "$value");
            }
        }
        
        if($gogetssl === true)
        {
            $query = Capsule::table('tblsslorders');
            
            if(isset($where['serviceid']) && !empty($where['serviceid']))
            {
                $query = $query->where('serviceid', $where['serviceid']);
            }
            
            $query = $query->where(function($q) {
                $q->where('status', "Completed");
                $q->orWhere('status', "Configuration Submitted");
            });
            
            $query = $query->where(function($q) {
                $q->where('module', "SSLCENTERWHMCS");
                $q->orWhere('module', "gogetssl");
            });
        }
        
        return $query;
    }

    public function setRemoteId($id)
    {
        $this->remoteid = $id;
    }

    public function setUserId($id)
    {
        $this->userid = $id;
    }

    public function setServiceId($id)
    {
        $this->serviceid = $id;
    }

    public function setAddonId($id)
    {
        $this->addon_id = $id;
    }

    public function setModule($name)
    {
        $this->module = $name;
    }

    public function setCertType($type)
    {
        $this->certtype = $type;
    }

    public function setCompletionDate($date)
    {
        $this->completiondate = $date;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
