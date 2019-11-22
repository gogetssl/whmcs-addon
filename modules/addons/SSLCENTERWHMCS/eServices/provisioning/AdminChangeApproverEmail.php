<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class AdminChangeApproverEmail extends Ajax {

    private $p;

    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        try {
            $this->adminChangeApproverEmail();
        } catch (Exception $ex) {
            $this->response(false, $ex->getMessage());
        }
        $this->response(true, 'Approver email was successfully changed.');
    }

    private function adminChangeApproverEmail() {
        $sslRepo   = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $ssService = $sslRepo->getByServiceId($this->p['serviceId']);

        if (is_null($ssService)) {
            throw new Exception('Create has not been initialized.');
        }

        if ($this->p['userID'] != $ssService->userid) {
            throw new Exception('An error occurred.');
        }

        $data = [
            'approver_email' => $this->p['newEmail']
        ];

        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->changeValidationEmail($ssService->remoteid, $data);
        
        $ssService->setConfigdataKey("approveremail", $data['approver_email']);
        $ssService->save();
    }
}
