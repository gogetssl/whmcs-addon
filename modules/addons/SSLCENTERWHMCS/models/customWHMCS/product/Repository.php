<?php

namespace MGModule\SSLCENTERWHMCS\models\customWHMCS\product;

/**
 * Description of repository
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Repository extends \MGModule\SSLCENTERWHMCS\mgLibs\models\Repository {
    public function getModelClass() {
        return __NAMESPACE__.'\Product';
    }
}
