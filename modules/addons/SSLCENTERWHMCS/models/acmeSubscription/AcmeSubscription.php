<?php

namespace MGModule\SSLCENTERWHMCS\models\acmeSubscription;

use MGModule\SSLCENTERWHMCS\mgLibs\models\Orm;

/**
 * @Table(name=SSLCENTER_acme_subscriptions,prefixed=true)
 */
class AcmeSubscription extends Orm
{
    public $id;
    public $service_id;
    public $client_id;
    public $api_order_id;
    public $acme_id;
    public $status;
    public $acme_account_id;
    public $eab_kid;
    public $eab_hmac_key;
    public $server_url;
    public $period_start;
    public $period_end;
    public $renewal_date;
    public $auto_renew;
    public $cancelled_at;
    public $created_at;
    public $updated_at;
}
