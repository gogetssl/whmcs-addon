<?php

namespace MGModule\GGSSLWHMCS\eRepository\whmcs\service;

use MGModule\GGSSLWHMCS\eModels\whmcs\service\SSL as Model;
use Exception;

class SSL {

    /**
     * @param int $id
     * @return \MGModule\GGSSLWHMCS\eModels\whmcs\service\SSL
     */
    public function getSingle($id) {
        $model = Model::find($id);
        if (is_null($model)) {
            throw new Exception('Invalid SSL Order');
        }
        return $model;
    }

    /**
     * @param int $id
     * @return \MGModule\GGSSLWHMCS\eModels\whmcs\service\SSL
     */
    public function getByServiceId($id) {
        return Model::whereServiceId($id)->first();
    }
    
    /**
     * @param string $status
     * @return \MGModule\GGSSLWHMCS\eModels\whmcs\service\SSL
     */
    public function getBy($where) {    
        return Model::getWhere($where)->get();
    }
}
