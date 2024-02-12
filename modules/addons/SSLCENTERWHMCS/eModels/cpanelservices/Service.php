<?php

namespace MGModule\SSLCENTERWHMCS\eModels\cpanelservices;

use \Illuminate\Database\Eloquent\model as EloquentModel;
use MGModule\SSLCENTERWHMCS\eHelpers\Cpanel;

class Service extends EloquentModel {

    protected $table = 'tblhosting';
    public $timestamps = false;

    public function getcPanelServices($userid)
    {
        return static::select([
            'tblhosting.id',
            'tblhosting.domain',
            'tblhosting.username as user',
            'tblproducts.name',
            'tblservers.hostname',
            'tblservers.ipaddress',
            'tblservers.secure',
            'tblservers.port',
            'tblservers.accesshash as hash',
            'tblservers.username',
            'tblservers.password',
            'tblhosting.packageid'
        ])
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->join('tblservers', 'tblhosting.server', '=', 'tblservers.id')
            ->where('tblproducts.servertype', 'cpanel')->where('tblhosting.userid', $userid)->get();
    }

    public function getDomainByUser($userid)
    {
        $domains = [];
        $services = self::getcPanelServices($userid);

        foreach ($services as $service)
        {
            try{
                $cpanel = new Cpanel();
                $cpanel->setService($service);
                $domains = array_merge($domains, $cpanel->listDomains($service->user));
            } catch (\Exception $e) {
                \logActivity($e->getMessage(), 0);
            }
        }

        return $domains;
    }

    public function getServiceByDomain($userid, $domain)
    {
        $services = self::getcPanelServices($userid);
        foreach ($services as $service)
        {
            try{
                $cpanel = new Cpanel();
                $cpanel->setService($service);
                foreach($cpanel->listDomains($service->user) as $cpaneldomain)
                {
                    if($domain == $cpaneldomain)
                    {
                        return $service;
                    }
                }
            } catch (\Exception $e) {
                \logActivity($e->getMessage(), 0);
            }
        }

        return false;
    }

    public function getcPanelService($domain)
    {
        return static::select([
            'tblhosting.id',
            'tblhosting.domain',
            'tblhosting.username as user',
            'tblproducts.name',
            'tblservers.hostname',
            'tblservers.ipaddress',
            'tblservers.secure',
            'tblservers.port',
            'tblservers.accesshash as hash',
            'tblservers.username',
            'tblservers.password'
        ])
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->join('tblservers', 'tblhosting.server', '=', 'tblservers.id')
            ->where('tblproducts.servertype', 'cpanel')->where('tblhosting.domain', $domain)->first();
    }

}
