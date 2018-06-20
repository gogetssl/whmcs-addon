<?php

namespace MGModule\GGSSLWHMCS\models\testGroup\simpleItem;
use MGModule\GGSSLWHMCS as main;

/**
 * Description of repository
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Repository extends \MGModule\GGSSLWHMCS\mgLibs\models\Repository{
    public function getModelClass() {
        return __NAMESPACE__.'\SimpleItem';
    }
}
