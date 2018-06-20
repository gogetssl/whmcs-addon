<?php

namespace MGModule\GGSSLWHMCS\models\customWHMCS\product;

/**
 * Description of repository
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Repository extends \MGModule\GGSSLWHMCS\mgLibs\models\Repository {
    public function getModelClass() {
        return __NAMESPACE__.'\Product';
    }
}
