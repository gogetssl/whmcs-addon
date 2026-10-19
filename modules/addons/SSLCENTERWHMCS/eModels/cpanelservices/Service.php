<?php

namespace MGModule\SSLCENTERWHMCS\eModels\cpanelservices;

use \Illuminate\Database\Eloquent\model as EloquentModel;
use Illuminate\Database\Capsule\Manager as Capsule;
use MGModule\SSLCENTERWHMCS\eHelpers\Cpanel;

class Service extends EloquentModel {

    protected $table = 'tblhosting';
    public $timestamps = false;

    /**
     * WHMCS provisioning modules (tblproducts.servertype, lower case) whose services are cPanel accounts:
     * the standard cPanel module and ModulesGarden cPanel Extended.
     */
    const CPANEL_SERVER_TYPES = ['cpanel', 'cpanelextended', 'cpanel_extended'];

    /**
     * tblsslorders.configdata key holding the hosting service (tblhosting.id) chosen for installation.
     */
    const LINKED_SERVICE_KEY = 'cpanel_service_id';

    protected function cpanelServicesQuery()
    {
        $placeholders = implode(',', array_fill(0, count(self::CPANEL_SERVER_TYPES), '?'));

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
            ->whereRaw('LOWER(tblproducts.servertype) IN (' . $placeholders . ')', self::CPANEL_SERVER_TYPES);
    }

    public function getcPanelServices($userid)
    {
        return $this->cpanelServicesQuery()->where('tblhosting.userid', $userid)->get();
    }

    /**
     * The client's cPanel hosting service with the given ID, or false if it is not a cPanel service of this client.
     */
    public function getLinkedService($userid, $hostingId)
    {
        if ((int) $hostingId <= 0) {
            return false;
        }

        $service = $this->cpanelServicesQuery()
            ->where('tblhosting.userid', $userid)
            ->where('tblhosting.id', (int) $hostingId)
            ->first();

        return $service ? $service : false;
    }

    /**
     * Hosting service the SSL certificate of the given SSL service should be installed on:
     * the service linked in "Install certificate on", otherwise the client's cPanel account that hosts the domain.
     */
    public function getServiceForSsl($sslServiceId, $userid, $domain)
    {
        $sslOrder = Capsule::table('tblsslorders')->where('serviceid', (int) $sslServiceId)->first();
        if ($sslOrder && !empty($sslOrder->configdata)) {
            $configdata = json_decode($sslOrder->configdata, true);
            if (is_array($configdata) && !empty($configdata[self::LINKED_SERVICE_KEY])) {
                $linked = $this->getLinkedService($userid, $configdata[self::LINKED_SERVICE_KEY]);
                if ($linked !== false) {
                    return $linked;
                }
            }
        }

        return $this->getServiceByDomain($userid, $domain);
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
        // A wildcard certificate (*.example.com) belongs to the account hosting example.com
        $domain = strtolower((string) $domain);
        if (strpos($domain, '*.') === 0) {
            $domain = substr($domain, 2);
        }

        $services = self::getcPanelServices($userid);
        foreach ($services as $service)
        {
            try{
                $cpanel = new Cpanel();
                $cpanel->setService($service);
                foreach($cpanel->listDomains($service->user) as $cpaneldomain)
                {
                    if($domain == strtolower($cpaneldomain))
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
        return $this->cpanelServicesQuery()->where('tblhosting.domain', $domain)->first();
    }

}
