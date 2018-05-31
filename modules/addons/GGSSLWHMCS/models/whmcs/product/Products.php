<?php

namespace MGModule\GGSSLWHMCS\models\whmcs\product;

/**
 * Description of repository
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Products extends \MGModule\GGSSLWHMCS\mgLibs\models\Repository {
    public function getModelClass() {
        return __NAMESPACE__.'\Product';
    }
}
