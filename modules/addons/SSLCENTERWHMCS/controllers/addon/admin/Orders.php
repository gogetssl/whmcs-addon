<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\admin;

use MGModule\SSLCENTERWHMCS\eModels\whmcs\service\SSL;
use MGModule\SSLCENTERWHMCS\mgLibs\Lang;
use MGModule\SSLCENTERWHMCS\mgLibs\process\AbstractController;
use MGModule\SSLCENTERWHMCS\models\orders\Repository;
use MGModule\SSLCENTERWHMCS\mgLibs\Smarty;
use MGModule\SSLCENTERWHMCS\models\whmcs\clients\Client;
use MGModule\SSLCENTERWHMCS\models\whmcs\service\Service;
use WHMCS\Database\Capsule as DB;

class Orders extends AbstractController
{
    public function indexHTML($input = [], $vars = [])
    {
        return ['tpl' => 'orders', 'vars' => $vars];
    }

    public function setVerifiedJSON($input = [], $vars = [])
    {
        try {
            if (!isset($input['id']) OR ! trim($input['id']))
                throw new \Exception(Lang::T('messages', 'orderIDNotProvided'));

            $orderID = $input['id'];
            $ordersRepo = new Repository();
            $ordersRepo->updateStatusById($orderID, 'Pending Installation');

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

        return [
            'success' => Lang::T('messages', 'Success')
        ];
    }

    public function setInstalledJSON($input = [], $vars = [])
    {
        try {
            if (!isset($input['id']) OR ! trim($input['id']))
                throw new \Exception(Lang::T('messages', 'orderIDNotProvided'));

            $orderID = $input['id'];
            $ordersRepo = new Repository();
            $ordersRepo->updateStatusById($orderID, 'Success');

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

        return [
            'success' => Lang::T('messages', 'Success')
        ];
    }

    public function getOrdersJSON($input = [], $vars = [])
    {
        try
        {
            $data['data'] = [];
            $orderRepo = new Repository();

            $col = ['id', 'client_id', 'service_id', '', '', '', 'date'];

            $list = $orderRepo->getList($input['limit'], $input['offset'], [$col[$input['order']['column']],$input['order']['dir']], $input['search']);

            $data['recordsFiltered'] = $data['recordsTotal'] = $list['count'];

            foreach ($list['results'] as $rule)
            {
                $data['data'][] = $this->formatRow('row', $rule);
            }
        }
        catch (\Exception $ex)
        {
            return ['error' => $ex->getMessage()];
        }

        return $data;
    }

    private function formatRow($template, $item)
    {
        $client = $this->getClient($item->client_id);
        $service = $this->getService($item->service_id);
        $order = $this->getSSLOrder($item->ssl_order_id);

        $data['id'] = $item->id;
        $data['client_id'] = $client['id'];
        $data['service_id'] = $service['id'];
        $data['client_name'] = $client['name'];
        $data['service_name'] = $service['name'];
        $data['order_id'] = $order['id'];
        $data['remote_id'] = $order['remoteid'];
        $data['verification_method'] = strtoupper($item->verification_method);
        $data['status'] = $item->status;
        $data['date'] = $item->date;

        $rows = $this->dataTablesParseRow($template, $data);

        return $rows;
    }

    function dataTablesParseRow($template,$data){
        $row = Smarty::I()->view($template,$data);

        $output = [];
        if(preg_match_all('/\<td\>(?P<col>.*?)\<\/td\>/s', $row, $result))
        {
            foreach($result['col'] as $col)
            {
                $output[] = $col;
            }
        }

        return $output;
    }

    private function getService($id)
    {
        $check = DB::table('tblhosting')->where('id', $id)->first();
        if(isset($check->id)) {
            $serviceDetails = new Service($id);
            return [
                'id' => $serviceDetails->id,
                'name' => trim($serviceDetails->domain . ' - ' . $serviceDetails->product()->name),
            ];
        }
        else
        {
            return [
                'id' => 0,
                'name' => 'The service does not exist',
            ];
        }
    }

    private function getSSLOrder($id)
    {
        $check = DB::table('tblsslorders')->where('id', $id)->first();
        if(isset($check->id)) {
            $sslOrder = SSL::find($id);
            return [
                'id' => $sslOrder->id,
                'remoteid' => $sslOrder->remoteid,
            ];
        }
        else
        {
            return [
                'id' => 0,
                'remoteid' => 0,
            ];
        }
    }

    private function getClient($id)
    {
        $check = DB::table('tblclients')->where('id', $id)->first();
        if(isset($check->id)) {
            $clientDetails = new Client($id);
            return [
                'id' => $clientDetails->id,
                'name' => trim($clientDetails->firstname . ' ' . $clientDetails->lastname . ' ' . $clientDetails->companyname),
            ];
        }
        else
        {
            return [
                'id' => 0,
                'name' => 'The client does not exist',
            ];
        }
    }
}