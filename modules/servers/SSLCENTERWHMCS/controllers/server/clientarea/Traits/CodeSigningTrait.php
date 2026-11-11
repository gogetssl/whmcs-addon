<?php

namespace MGModule\SSLCENTERWHMCS\controllers\server\clientarea\Traits;

use MGModule\SSLCENTERWHMCS as main;
use MGModule\SSLCENTERWHMCS\eHelpers\CodeSigningProduct;
use MGModule\SSLCENTERWHMCS\eServices\CodeSigningService;
use WHMCS\Database\Capsule;

/**
 * Client area of hardware code signing certificates (GoGetSSL API v2).
 * Uses the helpers of SmimeTrait (base vars, dates, phone, countries).
 */
trait CodeSigningTrait
{
    private function indexCodeSigningHTML($input, $vars = array())
    {
        $serviceId = (int) $input['params']['serviceid'];

        $sslRepo    = new main\eRepository\whmcs\service\SSL();
        $sslService = $sslRepo->getByServiceId($serviceId);

        if (is_null($sslService) || (int) $sslService->userid !== (int) $input['params']['userid'])
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('anErrorOccurredPleaseContactSupport'));
        }

        // configuration link from the welcome email (&configssl=1) or the Configure Now button
        if ($sslService->status === 'Awaiting Configuration' && !CodeSigningService::isCodeSigningOrder($sslService)
            && ((isset($_GET['csconfig']) && (string) $_GET['csconfig'] === '1') || (isset($_GET['configssl']) && (string) $_GET['configssl'] === '1')))
        {
            return $this->codeSigningConfigurationHTML($input, $vars);
        }

        $vars = $this->getSmimeBaseVars($input, $vars);
        $vars['isSmime']       = false;
        $vars['isCodeSigning'] = true;

        if (!CodeSigningService::isCodeSigningOrder($sslService))
        {
            $vars['configurationURL'] = 'clientarea.php?action=productdetails&id=' . $serviceId . '&csconfig=1';

            return [
                'tpl'  => 'home_smime_awaiting_configuration',
                'vars' => $vars,
            ];
        }

        $status = strtolower((string) $sslService->getConfigdataKey('ssl_status'));
        $vars['refreshError'] = '';
        if (!in_array($status, CodeSigningProduct::STATUSES_FINAL, true))
        {
            try
            {
                CodeSigningService::refresh($sslService);
                $sslService = $sslRepo->getByServiceId($serviceId);
            }
            catch (\Exception $e)
            {
                $vars['refreshError'] = $e->getMessage();
            }
        }

        $config = (array) $sslService->configdata;

        $vars['csStatus']        = strtolower(isset($config['ssl_status']) ? (string) $config['ssl_status'] : 'processing');
        $vars['csStatusText']    = $this->translateCodeSigningStatus($vars['csStatus']);
        $vars['csOrderId']       = (int) $sslService->remoteid;
        $vars['csVendorOrderId'] = !empty($config['partner_order_id']) ? $config['partner_order_id'] : '';
        $vars['csOrganization']  = !empty($config['cs_organization']) ? $config['cs_organization'] : '';
        $vars['csMethod']        = !empty($config['cs_method']) ? (string) $config['cs_method'] : '';
        $vars['csDelivery']      = $this->describeCodeSigningDelivery($config);
        $vars['csOrderedAt']     = !empty($config['ordered_at']) ? $this->formatSmimeDate($config['ordered_at']) : '';
        $vars['csValidFrom']     = !empty($config['valid_from']) ? $this->formatSmimeDate($config['valid_from']) : '';
        $vars['csValidTill']     = !empty($config['valid_till']) ? $this->formatSmimeDate($config['valid_till']) : '';

        return [
            'tpl'  => 'home_codesigning',
            'vars' => $vars,
        ];
    }

    private function codeSigningConfigurationHTML($input, $vars = array())
    {
        $vars = $this->getSmimeBaseVars($input, $vars);
        $vars['isSmime']       = false;
        $vars['isCodeSigning'] = true;

        $client = Capsule::table('tblclients')
            ->where('id', (int) $input['params']['userid'])
            ->first();

        $apiProductId = (int) $input['params']['configoption1'];
        $apiProduct   = (new main\eRepository\sslcenter\Products())->getProduct($apiProductId);
        $apiProduct   = ($apiProduct && (int) $apiProduct->id === $apiProductId) ? $apiProduct : null;

        $available = CodeSigningService::getAvailableProvisioning($input['params'], $apiProduct);

        $methods = [];
        foreach ($available['methods'] as $method)
        {
            $methods[$method] = main\mgLibs\Lang::absoluteT('serverCA', 'home', 'csMethod_' . str_replace('-', '_', $method));
        }

        $shippingLabel = '';
        if ($available['fixed'] !== null && $available['fixed']['method'] === CodeSigningProduct::METHOD_SHIP_TOKEN)
        {
            $shippingLabel = $this->getCodeSigningShippingLabel($available['fixed']['shipping'], $available['shipping_options']);
        }
        else if (in_array(CodeSigningProduct::METHOD_SHIP_TOKEN, $available['methods'], true))
        {
            $shippingLabel = $this->getCodeSigningShippingLabel(CodeSigningProduct::DEFAULT_SHIPPING, $available['shipping_options']);
        }

        $vars['csMethods']       = $methods;
        $vars['csFixedMethod']   = $available['fixed'] !== null ? $available['fixed']['method'] : '';
        $vars['csShippingLabel'] = $shippingLabel;
        $vars['csTokenModels']   = CodeSigningProduct::TOKEN_MODELS;
        $vars['csClient'] = [
            'email'       => $client ? $client->email : '',
            'firstname'   => $client ? $client->firstname : '',
            'lastname'    => $client ? $client->lastname : '',
            'companyname' => $client ? $client->companyname : '',
            'phonenumber' => $client ? $this->formatSmimePhone($client) : '',
            'country'     => $client ? $client->country : '',
            'state'       => ($client && strcasecmp(trim((string) $client->state), 'None') !== 0) ? $client->state : '',
            'city'        => $client ? $client->city : '',
            'postcode'    => $client ? $client->postcode : '',
            'address1'   => $client ? $client->address1 : '',
            'address2'   => $client ? $client->address2 : '',
        ];
        $vars['csCountries'] = $this->getSmimeCountries();

        return [
            'tpl'  => 'home_codesigning_configuration',
            'vars' => $vars,
        ];
    }

    public function createCodeSigningJSON($input, $vars = array())
    {
        if (!CodeSigningProduct::isCodeSigningByServiceParams($input['params']))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('csNotCodeSigningProduct'));
        }

        if (isset($input['params']['status']) && $input['params']['status'] !== 'Active')
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('csServiceNotActive'));
        }

        CodeSigningService::create($input['params'], [
            'method'           => isset($input['cs_method']) ? (string) $input['cs_method'] : '',
            'model'            => isset($input['cs_model']) ? (string) $input['cs_model'] : '',
            'csr'              => isset($input['cs_csr']) ? (string) $input['cs_csr'] : '',
            'organization'     => isset($input['org']) && is_array($input['org']) ? $input['org'] : [],
            'administrator'    => isset($input['admin']) && is_array($input['admin']) ? $input['admin'] : [],
            'shipping_contact' => isset($input['ship']) && is_array($input['ship']) ? $input['ship'] : [],
        ]);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('csOrderedSuccessfully'),
        ];
    }

    public function refreshCodeSigningDetailsJSON($input, $vars = array())
    {
        $sslService = (new main\eRepository\whmcs\service\SSL())->getByServiceId((int) $input['params']['serviceid']);

        if (is_null($sslService) || (int) $sslService->userid !== (int) $input['params']['userid'] || !CodeSigningService::isCodeSigningOrder($sslService))
        {
            throw new \Exception(main\mgLibs\Lang::absoluteT('csNotOrderedYet'));
        }

        CodeSigningService::refresh($sslService);

        return [
            'success' => true,
            'message' => main\mgLibs\Lang::absoluteT('csDetailsRefreshed'),
        ];
    }

    private function getCodeSigningShippingLabel($shippingId, array $shippingOptions)
    {
        foreach ($shippingOptions as $option)
        {
            if (strtoupper((string) $option['id']) === strtoupper((string) $shippingId) && !empty($option['label']))
            {
                return (string) $option['label'];
            }
        }

        return ucfirst(strtolower((string) $shippingId)) . ' Shipping';
    }

    private function describeCodeSigningDelivery(array $config)
    {
        $method = isset($config['cs_method']) ? (string) $config['cs_method'] : '';
        $text   = main\mgLibs\Lang::absoluteT('serverCA', 'home', 'csMethod_' . str_replace('-', '_', $method));

        if ($method === CodeSigningProduct::METHOD_SHIP_TOKEN && !empty($config['cs_shipping']))
        {
            $text .= ' (' . ucfirst(strtolower((string) $config['cs_shipping'])) . ')';
        }
        else if ($method === CodeSigningProduct::METHOD_EXISTING_TOKEN && !empty($config['cs_token_model']))
        {
            $model = (string) $config['cs_token_model'];
            $text .= ' (' . (isset(CodeSigningProduct::TOKEN_MODELS[$model]) ? CodeSigningProduct::TOKEN_MODELS[$model] : $model) . ')';
        }

        return $text;
    }

    private function translateCodeSigningStatus($status)
    {
        // same status set as S/MIME (API v2 order statuses)
        return $this->translateSmimeStatus($status);
    }
}
