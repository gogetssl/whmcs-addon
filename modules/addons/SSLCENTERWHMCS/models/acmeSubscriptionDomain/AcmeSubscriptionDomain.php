<?php

namespace MGModule\SSLCENTERWHMCS\models\acmeSubscriptionDomain;

use MGModule\SSLCENTERWHMCS\mgLibs\models\Orm;

/**
 * @Table(name=SSLCENTER_acme_subscription_domains,prefixed=true)
 */
class AcmeSubscriptionDomain extends Orm
{
    public $id;
    public $service_id;
    public $domain;
    public $domain_type;
    public $status;
    public $added_at;
    public $removed_at;
    public $created_at;
    public $updated_at;
}
