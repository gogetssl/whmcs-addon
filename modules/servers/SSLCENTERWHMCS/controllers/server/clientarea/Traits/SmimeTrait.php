<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits;

use MGModule\SSLCENTERWHMCS as main;
use MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct;
use MGModule\SSLCENTERWHMCS\eServices\SmimeService;
use WHMCS\Database\Capsule;

/**
 * Client area of S/MIME certificates (GoGetSSL API v2).
 */
trait SmimeTrait
{
    private function indexSmimeHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];

        $sslRepo    = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);

        if (is_null($sslService) || (int) $sslService->userid !== (int) $input['params']['userid'])
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('anErrorOccurredPleaseContactSupport'));
        }

        // configuration link from the welcome email (&configssl=1) or the Configure Now button
        if ($sslService->status === 'Awaiting Configuration' && !SmimeService::isSmimeOrder($sslService)
            && ((isset($_GET['smimeconfig']) && (string) $_GET['smimeconfig'] === '1') || (isset($_GET['configssl']) && (string) $_GET['configssl'] === '1')))
        {
            return $this->smimeConfigurationHTML($input, $vars);
        }

        $vars = $this->getSmimeBaseVars($input, $vars);

        if (!SmimeService::isSmimeOrder($sslService))
        {
            $vars['configurationURL'] = 'clientarea.php?action=productdetails&id=' . $serviceId . '&smimeconfig=1';

            return [
                'tpl'  => 'home_smime_awaiting_configuration',
                'vars' => $vars,
            ];
        }

        $status = strtolower((string) $sslService->getConfigdataKey('ssl_status'));
        $vars['refreshError'] = '';
        if (!in_array($status, SmimeProduct::STATUSES_FINAL, true))
        {
            try
            {
                SmimeService::refresh($sslService);
                $sslService = $sslRepo->getByServiceId($serviceId);
            }
            catch (\Exception $e)
            {
                $vars['refreshError'] = $e->getMessage();
            }
        }

        $config = (array) $sslService->configdata;
        $emails = isset($config['smime_emails']) ? SmimeProduct::normalizeEmails(json_decode(json_encode($config['smime_emails']), true)) : [];

        $vars['smimeStatus']     = strtolower(isset($config['ssl_status']) ? (string) $config['ssl_status'] : 'processing');
        $vars['smimeStatusText'] = $this->translateSmimeStatus($vars['smimeStatus']);
        $vars['smimeOrderId']    = (int) $sslService->remoteid;
        $vars['smimeEmails']     = $emails;
        $vars['smimeAllApproved'] = !empty($emails) && count(array_filter($emails, function ($e) { return empty($e['approved']); })) === 0;
        $vars['smimeValidFrom']  = !empty($config['valid_from']) ? $this->formatSmimeDate($config['valid_from']) : '';
        $vars['smimeValidTill']  = !empty($config['valid_till']) ? $this->formatSmimeDate($config['valid_till']) : '';
        $vars['smimeVendorOrderId'] = !empty($config['partner_order_id']) ? $config['partner_order_id'] : '';
        $vars['smimeCsrProvided'] = !empty($config['smime_csr_provided']);
        $vars['smimeOrderedAt']  = !empty($config['ordered_at']) ? $this->formatSmimeDate($config['ordered_at']) : '';

        return [
            'tpl'  => 'home_smime',
            'vars' => $vars,
        ];
    }

    private function smimeConfigurationHTML($input, $vars = array())
    {
        $vars = $this->getSmimeBaseVars($input, $vars);

        $client = Capsule::table('tblclients')
            ->where('id', (int) $input['params']['userid'])
            ->first();

        $apiProductId = (int) $input['params']['configoption1'];
        $apiProduct   = (new main\eRepository\sslcenter\Products())->getProduct($apiProductId);
        $apiProduct   = ($apiProduct && (int) $apiProduct->id === $apiProductId) ? $apiProduct : null;

        $vars['smimeRequiresOrganization'] = SmimeProduct::requiresOrganization($apiProductId, $apiProduct);
        $vars['smimeMaxEmails']            = SmimeProduct::MAX_EMAILS;
        $vars['smimeClient'] = [
            'email'       => $client ? $client->email : '',
            'firstname'   => $client ? $client->firstname : '',
            'lastname'    => $client ? $client->lastname : '',
            'companyname' => $client ? $client->companyname : '',
            'phonenumber' => $client ? $this->formatSmimePhone($client) : '',
            'country'     => $client ? $client->country : '',
            'state'       => ($client && strcasecmp(trim((string) $client->state), 'None') !== 0) ? $client->state : '',
            'city'        => $client ? $client->city : '',
            'postcode'    => $client ? $client->postcode : '',
            'address1'    => $client ? $client->address1 : '',
            'address2'    => $client ? $client->address2 : '',
        ];
        $vars['smimeCountries'] = $this->getSmimeCountries();

        return [
            'tpl'  => 'home_smime_configuration',
            'vars' => $vars,
        ];
    }

    public function createSmimeJSON($input, $vars = array())
    {
        if (!SmimeProduct::isSmimeByServiceParams($input['params']))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('smimeNotSmimeProduct'));
        }

        if (isset($input['params']['status']) && $input['params']['status'] !== 'Active')
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('smimeServiceNotActive'));
        }

        $emails = [];
        if (isset($input['smime_email']))
        {
            $emails[] = $input['smime_email'];
        }
        if (!empty($input['smime_additional_emails']))
        {
            $additional = preg_split('/[\s,;]+/', (string) $input['smime_additional_emails'], -1, PREG_SPLIT_NO_EMPTY);
            $emails = array_merge($emails, $additional);
        }

        SmimeService::create($input['params'], [
            'emails'        => $emails,
            'csr'           => isset($input['smime_csr']) ? (string) $input['smime_csr'] : '',
            'organization'  => isset($input['org']) && is_array($input['org']) ? $input['org'] : [],
            'administrator' => isset($input['admin']) && is_array($input['admin']) ? $input['admin'] : [],
        ]);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('smimeOrderedSuccessfully'),
        ];
    }

    public function refreshSmimeDetailsJSON($input, $vars = array())
    {
        $sslService = (new main\eRepository\whmcs\service\SSL())->getByServiceId((int) $input['params']['serviceid']);

        if (is_null($sslService) || (int) $sslService->userid !== (int) $input['params']['userid'] || !SmimeService::isSmimeOrder($sslService))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('smimeNotOrderedYet'));
        }

        SmimeService::refresh($sslService);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('smimeDetailsRefreshed'),
        ];
    }

    private function getSmimeBaseVars($input, $vars)
    {
        $product = Capsule::table('tblproducts')
            ->select(['name'])
            ->where('id', (int) $input['params']['pid'])
            ->first();

        $vars['allOk']       = true;
        $vars['serviceid']   = (int) $input['params']['serviceid'];
        $vars['userid']      = (int) $input['params']['userid'];
        $vars['assetsURL']   = main\Server::I()->getAssetsURL();
        $vars['isSmime']     = true;
        $vars['productName'] = isset($product->name) ? $product->name : '';

        return $vars;
    }

    private function translateSmimeStatus($status)
    {
        $text = main\mgLibs\Lang::absoluteT('serverCA', 'home', 'smimeStatus_' . $status);
        if (!is_string($text) || $text === '' || strpos($text, 'smimeStatus_') !== false)
        {
            return ucfirst($status);
        }

        return $text;
    }

    private function formatSmimeDate($date)
    {
        $time = strtotime((string) $date);
        if (!$time)
        {
            return (string) $date;
        }

        if (function_exists('fromMySQLDate'))
        {
            return fromMySQLDate(date('Y-m-d', $time), false, true);
        }

        return date('Y-m-d', $time);
    }

    private function formatSmimePhone($client)
    {
        $phone = trim((string) $client->phonenumber);
        if ($phone === '' || strpos($phone, '+') === 0)
        {
            return $phone;
        }

        $countryCode = isset($client->phonecc) ? trim((string) $client->phonecc) : '';
        return $countryCode !== '' ? '+' . ltrim($countryCode, '+') . '.' . $phone : $phone;
    }

    private function getSmimeCountries()
    {
        try
        {
            $countries = (new \WHMCS\Utility\Country())->getCountryNameArray();
            if (is_array($countries) && !empty($countries))
            {
                return $countries;
            }
        }
        catch (\Throwable $e)
        {
        }

        return [];
    }
}
