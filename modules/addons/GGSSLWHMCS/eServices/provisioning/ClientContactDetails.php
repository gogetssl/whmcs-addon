<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class ClientContactDetails {

    /**
     *
     * @var array 
     */
    private $p;

    /**
     *
     * @var array 
     */
    private $get;

    /**
     *
     * @var array 
     */
    private $post;

    /**
     *
     * @var array 
     */
    private $vars;

    /**
     *
     * @var \MGModule\GGSSLWHMCS\eModels\whmcs\service\SSL 
     */
    private $sslService;

    /**
     *
     * @var array 
     */
    private $orderStatus;

    function __construct(&$params, &$post, &$get) {
        $this->p              = &$params;
        $this->get            = &$get;
        $this->post           = &$post;
        $this->vars           = [];
        $this->vars['errors'] = [];
    }

    public function run() {
        return $this->miniControler();
    }

    private function miniControler() {
        try {
            $this->validate();
            $this->loadOrder();
        } catch (Exception $ex) {
            return '- ' . \MGModule\GGSSLWHMCS\eHelpers\Exception::e($ex);
        }

        return $this->build();
    }

    private function validate() {
        $ssl              = new \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSL();
        $this->sslService = $ssl->getByServiceId($this->p['serviceid']);

        if (is_null($this->sslService)) {
            throw new Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('create_not_initialized'));
        }
    }

    private function loadOrder() {
        $this->vars['order'] = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getOrderStatus($this->sslService->remoteid);
    }

    private function build() {
        $this->vars['error'] = implode('<br>', $this->vars['errors']);
        $content             = \MGModule\GGSSLWHMCS\eServices\TemplateService::buildTemplate('pages/contactDetails/contactDetails', $this->vars);
        return [
            'templatefile' => 'main',
            'vars'         => ['content' => $content],
        ];
    }

}
