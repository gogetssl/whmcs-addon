<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\admin;

use MGModule\SSLCENTERWHMCS\mgLibs\process\AbstractController;
use MGModule\SSLCENTERWHMCS\models\logs\Repository;
use MGModule\SSLCENTERWHMCS\mgLibs\Smarty;
use MGModule\SSLCENTERWHMCS\models\whmcs\clients\Client;
use MGModule\SSLCENTERWHMCS\models\whmcs\service\Service;
use MGModule\SSLCENTERWHMCS\mgLibs\Lang;
use WHMCS\Database\Capsule as DB;

class Logs extends AbstractController
{
    public function indexHTML($input = [], $vars = [])
    {
        return ['tpl' => 'logs', 'vars' => $vars];
    }

    public function getLogsJSON($input = [], $vars = [])
    {
        try
        {
            $data['data'] = [];
            $logsRepo = new Repository();

            $col = ['id', 'client_id', 'service_id', '', '', 'date'];
            $list = $logsRepo->getList($input['limit'], $input['offset'], [$col[$input['order']['column']],$input['order']['dir']], $input['search']);

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

        $data['id'] = $item->id;
        $data['client_id'] = $client['id'];
        $data['service_id'] = $service['id'];
        $data['client_name'] = $client['name'];
        $data['service_name'] = $service['name'];

        $data['type']    = strtoupper($item->type);
        $data['msg']    = $item->msg;
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

    public function removeLogJSON($input = [], $vars = [])
    {
        try {
            if (!isset($input['log_id']) OR ! trim($input['log_id']))
                throw new \Exception(Lang::T('messages', 'logIDNotProvided'));

            $logID = $input['log_id'];
            $logsRepo = new Repository();
            $logsRepo->remove($logID);

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

        return [
            'success' => Lang::T('messages', 'removeSuccess')
        ];
    }

    public function clearLogsJSON($input = [], $vars = [])
    {
        try {

            $logsRepo = new Repository();
            $logsRepo->clear();

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

        return [
            'success' => Lang::T('messages', 'clearSuccess')
        ];
    }
}