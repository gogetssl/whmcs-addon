<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

class Ajax {

    private $response = [];

    protected function response($succes, $message, $data = []) {
        $this->response['success'] = $succes ? 1 : 0;
        $this->response['msg'] = $message;
        if(!empty($data)) {
            $this->response['data'] = $data;
        }
        echo json_encode($this->response);
        $this->finishScript();
    }

    private function finishScript() {
        die();
    }
}
