<?php

namespace MGModule\SSLCENTERWHMCS\mgLibs;

use MGModule\SSLCENTERWHMCS\Configuration;
use WHMCS\Database\Capsule;
/**
 * Use any way you want. Free for all
 *
 * @version 1.1
 * */
//error_reporting(E_ALL);
//ini_set('display_errors', 'on');
//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//define('DEBUG', 'TRUE');

define('DEBUG', 'FALSE');

class SSLCenterApi {

    protected $apiUrl = 'https://my.gogetssl.com/api';
    protected $apiV2Url = 'https://my.gogetssl.com/api/v2';
    protected $key;
    protected $lastStatus;
    protected $lastResponse;
    protected $lastRequest;
    protected $apiExceptions = true;
    protected $exceptionType;

    public function __construct($key = null, $apiUrl = null) {
        $this->key = isset($key) ? $key : null;
        
        $this->setSSLCenterApiException();
//        $this->setSSLCenterException();
    }
    
    public function setSSLCenterException() {
        $this->exceptionType = 'SSLCenterException';
    }
    
    public function setSSLCenterApiException() {
        $this->exceptionType = 'SSLCenterApiException';
    }
    
    public function setNoneException() {
        $this->exceptionType = 'none';
    }

    public function turnOnApiExceptions() {
        $this->apiExceptions = true;
    }
    
    public function turnOffApiExceptions() {
        $this->apiExceptions = false;
    }

    public function auth($user, $pass) {
        
        $checkKey = Capsule::table('tblconfiguration')->where('setting', 'sslcenter_authkey')->first();
        if(!isset($checkKey->value) || empty($checkKey->value))
        {
            $response = $this->call('/auth/', array(), array(
                'user' => $user,
                'pass' => $pass
            ));

            if (!empty($response['key'])) {
                
                if(!isset($checkKey->value))
                {
                    Capsule::table('tblconfiguration')->insert([
                        'setting' => 'sslcenter_authkey',
                        'value' => $response['key']
                    ]);
                }
                else if(empty($checkKey->value))
                {
                    Capsule::table('tblconfiguration')->where('setting', 'sslcenter_authkey')->update([
                        'value' => $response['key']
                    ]);
                }
                
                $this->key = $response ['key'];
                return $response;
            }
            Capsule::table('tblconfiguration')->where('setting', 'sslcenter_authkey')->delete();
            return $response;
        }
        else
        {
            $this->key = $checkKey->value;
            return ['key' => $checkKey->value];
        }
    }

    public function addSslSan($orderId, $count, $single, $wildcard) {

        $postData['order_id'] = $orderId;
        $postData['count'] = $count;
        $postData['single_san_count'] = $single;
        $postData['wildcard_san_count'] = $wildcard;

        if(empty($postData['count']))
        {
            unset($postData['count']);
        }
        if(empty($postData['single_san_count']))
        {
            unset($postData['single_san_count']);
        }
        if(empty($postData['wildcard_san_count']))
        {
            unset($postData['wildcard_san_count']);
        }

        return $this->call('/orders/add_ssl_san_order/', $getData, $postData);
    }

    public function cancelSSLOrder($orderId, $reason) {
        $postData ['order_id'] = $orderId;
        $postData ['reason'] = $reason;

        return $this->call('/orders/cancel_ssl_order/', $getData, $postData);
    }

    public function changeDcv($orderId, $data) {
        return $this->call('/orders/ssl/change_dcv/' . (int) $orderId, $getData, $data);
    }
    public function changeValidationMethod($orderId, $data) {
        return $this->call('/orders/ssl/change_validation_method/' . (int) $orderId, $getData, $data);
    }
    public function changeDomainValidationMethod($orderId, $data) {
        return $this->call('/orders/ssl/change_domains_validation_method/' . (int) $orderId, $getData, $data);
    }
    public function revalidate($orderId, $data) {
        return $this->call('/orders/ssl/revalidate/' . (int) $orderId, $getData, $data);
    }
    public function changeValidationEmail($orderId, $data) {
        return $this->call('/orders/ssl/change_validation_email/' . (int) $orderId, $getData, $data);
    }

    public function setKey($key) {
        if ($key) {
            $this->key = $key;
        }
    }

    public function setUrl($url) {
        $this->apiUrl = $url;
    }

    public function decodeCSR($csr, $brand = 1, $wildcard = 0) {
        if ($csr) {
            $postData ['csr'] = $csr;
            $postData ['brand'] = $brand;
            $postData ['wildcard'] = $wildcard;
        }

        return $this->call('/tools/csr/decode/', $getData, $postData);
    }

    public function getWebServers($type) {
        return $this->call('/tools/webservers/' . (int) $type, $getData);
    }

    public function getDomainAlternative($csr = null) {
        $postData['csr'] = $csr;

        return $this->call('/tools/domain/alternative/', $getData, $postData);
    }

    public function getDomainEmails($domain) {
        if ($domain) {
            $postData ['domain'] = $domain;
        }

        return $this->call('/tools/domain/emails/', $getData, $postData);
    }

    public function getDomainEmailsForGeotrust($domain) {
        if ($domain) {
            $postData ['domain'] = $domain;
        }

        return $this->call('/tools/domain/emails/geotrust', $getData, $postData);
    }

    public function getAllProductPrices() {
        return $this->call('/products/all_prices/', $getData);
    }

    public function getAllProducts() {
        return $this->call('/products/', $getData);
    }

    public function getProduct($productId) {
        return $this->call('/products/ssl/' . $productId, $getData);
    }

    public function getProducts() {
        return $this->call('/products/ssl/', $getData);
    }

    public function getProductDetails($productId) {
        return $this->call('/products/details/' . $productId, $getData);
    }

    public function getProductPrice($productId) {
        return $this->call('/products/price/' . $productId, $getData);
    }

    public function getUserAgreement($productId) {
        return $this->call('/products/agreement/' . $productId, $getData);
    }

    public function getAccountBalance() {
        return $this->call('/account/balance/', $getData);
    }

    public function getAccountDetails() {
        return $this->call('/account/', $getData);
    }

    public function getTotalOrders() {
        return $this->call('/account/total_orders/', $getData);
    }

    public function getAllInvoices() {
        return $this->call('/account/invoices/', $getData);
    }

    public function getUnpaidInvoices() {
        return $this->call('/account/invoices/unpaid/', $getData);
    }

    public function getTotalTransactions() {
        return $this->call('/account/total_transactions/', $getData);
    }

    public function addSSLOrder1($data) {
        return $this->call('/orders/add_ssl_order1/', $getData, $data);
    }

    public function addSSLOrder($data) {
        return $this->call('/orders/add_ssl_order/', $getData, $data);
    }

    public function addSSLRenewOrder($data) {
        return $this->call('/orders/add_ssl_renew_order/', $getData, $data);
    }

    public function createAcmeSubscription($data)
    {
        if (isset($data['product_id']) && !isset($data['product']))
        {
            $data['product'] = ['id' => (int) $data['product_id']];
            unset($data['product_id']);
        }

        if (isset($data['domains']))
        {
            $data['domains'] = $this->normalizeDomains($data['domains']);
        }

        $createResponse = $this->normalizeAcmeCreateResponse(
            $this->callV2('/certificates/acme', 'POST', $data)
        );

        $orderId = isset($createResponse['order_id']) ? (int) $createResponse['order_id'] : 0;
        if ($orderId <= 0 && isset($createResponse['id']))
        {
            $orderId = (int) $createResponse['id'];
        }

        if ($orderId > 0)
        {
            $detailsResponse = $this->normalizeAcmeCreateResponse(
                $this->getCertificateDetails('acme', $orderId)
            );

            if (is_array($detailsResponse))
            {
                return array_replace($createResponse, $detailsResponse);
            }
        }

        return $createResponse;
    }

    public function getAcmeSubscriptionStatus($orderId)
    {
        return $this->callV2('/certificates/acme/' . (int) $orderId, 'GET');
    }

    public function getCertificateDetails($category, $orderId)
    {
        return $this->callV2('/certificates/' . rawurlencode((string) $category) . '/' . (int) $orderId, 'GET');
    }

    public function addAcmeDomains($orderId, $data)
    {
        if (isset($data['domains']))
        {
            $data = $this->normalizeDomains($data['domains']);
        }
        else if (!is_array($data))
        {
            $data = $this->normalizeDomains($data);
        }

        return $this->callV2('/certificates/acme/' . (int) $orderId . '/domains', 'POST', $data);
    }

    public function removeAcmeDomain($orderId, $acmeID, $data)
    {
        $domainId = null;
        $response = $this->getCertificateDetails('acme', $orderId);

        foreach($response['items'][0]['domains'] as $remoteDomainData)
        {
            if($remoteDomainData['name'] == $data['domain'])
            {
                $domainId = $remoteDomainData['id'];
                break;
            }
        }

        if (empty($domainId))
        {
            throw new SSLCenterApiException('Unable to resolve ACME domain ID.');
        }

        return $this->callV2('/certificates/acme/' . (int) $acmeID . '/domains/' . rawurlencode((string) $domainId), 'DELETE');
    }

    public function cancelCertificate($orderId, $reason = '')
    {
        $payload = ['reason' => !empty($reason) ? $reason : 'Cancelled by API client'];

        return $this->callV2('/certificates/' . (int) $orderId . '/cancel', 'POST', $payload);
    }

    public function disableSubscriptionAutoRenewal($orderId)
    {
        return $this->callV2('/certificates/acme/' . (int) $orderId . '/subscription', 'DELETE');
    }

    public function reIssueOrder($orderId, $data) {
        return $this->call('/orders/ssl/reissue/' . (int) $orderId, $getData, $data);
    }

    public function activateSSLOrder($orderId) {
        return $this->call('/orders/ssl/activate/' . (int) $orderId, $getData);
    }

    public function addSandboxAccount($data) {
        return $this->call('/accounts/sandbox/add/', $getData, $data);
    }

    public function getOrderStatus($orderId) {
        return $this->call('/orders/status/' . (int) $orderId, $getData);
    }
    public function getOrderStatuses($ordersId) {
        return $this->call('/orders/statuses/', $getData, $ordersId);
    }
    public function comodoClaimFreeEV($orderId, $data) {
        return $this->call('/orders/ssl/comodo_claim_free_ev/' . (int) $orderId, $getData, $data);
    }

    public function getOrderInvoice($orderId) {
        return $this->call('/orders/invoice/' . (int) $orderId, $getData);
    }

    public function getUnpaidOrders() {
        return $this->call('/orders/list/unpaid/', $getData);
    }

    public function resendEmail($orderId) {
        return $this->call('/orders/ssl/resend_validation_email/' . (int) $orderId, $getData);
    }

    public function resendValidationEmail($orderId) {
        return $this->call('/orders/ssl/resend_validation_email/' . (int) $orderId, $getData);
    }

    public function getCSR($data) {
        return $this->call('/tools/csr/get/', $getData, $data);
    }

    public function generateCSR($data) {
        return $this->call('/tools/csr/generate/', $getData, $data);
    }

    protected function normalizeDomains($domains)
    {
        if (is_array($domains))
        {
            return array_values(array_filter(array_map('trim', $domains)));
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', (string) $domains))));
    }

    protected function findAcmeDomainId($status, $domainName)
    {
        if (!is_array($status))
        {
            return null;
        }

        $target = strtolower(trim((string) $domainName));
        $items = isset($status['items']) && is_array($status['items']) ? $status['items'] : array();

        foreach ($items as $item)
        {
            if (empty($item['domains']) || !is_array($item['domains']))
            {
                continue;
            }

            foreach ($item['domains'] as $domain)
            {
                if (!is_array($domain) || !isset($domain['name']))
                {
                    continue;
                }

                if (strtolower(trim((string) $domain['name'])) === $target && !empty($domain['id']))
                {
                    return $domain['id'];
                }
            }
        }

        return null;
    }

    protected function normalizeAcmeCreateResponse($response)
    {
        if (!is_array($response))
        {
            return $response;
        }

        if (isset($response['order']['id']) && !isset($response['order_id']))
        {
            $response['order_id'] = (int) $response['order']['id'];
            $response['id'] = (int) $response['order']['id'];
        }

        if (isset($response['order']['status']) && !isset($response['status']))
        {
            $response['status'] = $response['order']['status'];
        }

        if (!empty($response['items']) && is_array($response['items']) && isset($response['items'][0]) && is_array($response['items'][0]))
        {
            $item = $response['items'][0];

            if (!empty($item['account']) && is_array($item['account']))
            {
                $response['acme_account_id'] = isset($item['account']['id']) ? (string) $item['account']['id'] : '';
                $response['eab_kid'] = isset($item['account']['eab_mac_id']) ? (string) $item['account']['eab_mac_id'] : '';
                $response['eab_hmac_key'] = isset($item['account']['eab_mac_key']) ? (string) $item['account']['eab_mac_key'] : '';
                $response['server_url'] = isset($item['account']['server_url']) ? (string) $item['account']['server_url'] : '';
            }

            if (!empty($item['subscription']) && is_array($item['subscription']))
            {
                $response['begin_date'] = isset($item['subscription']['begin']) ? $item['subscription']['begin'] : null;
                $response['end_date'] = isset($item['subscription']['end']) ? $item['subscription']['end'] : null;
                $response['renewal_date'] = isset($item['subscription']['next_renewal']) ? $item['subscription']['next_renewal'] : null;
            }
        }

        return $response;
    }

    protected function getV2AuthorizationHeader()
    {
        return $this->buildV2AuthorizationHeader();
    }

    public function testConnectionV1($user, $pass)
    {
        return $this->call('/auth/', array(), array(
            'user' => $user,
            'pass' => $pass
        ));
    }

    public function testConnectionV2($partnerCode = null, $password = null)
    {
        return $this->callV2('/products', 'GET', null, $partnerCode, $password);
    }

    protected function buildV2AuthorizationHeader($partnerCode = null, $password = null)
    {
        if (!empty($partnerCode) && !empty($password))
        {
            return 'Authorization: GGS ' . $partnerCode . ':' . $password;
        }

        $apiConfigRepo = new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository();
        $apiData = $apiConfigRepo->get();

        $partnerCode = !empty($apiData->api_partner_code) ? trim((string) $apiData->api_partner_code) : null;
        $password = $apiData->api_password;

        if (!empty($partnerCode))
        {
            if (empty($password))
            {
                throw new SSLCenterException('api_configuration_empty');
            }

            return 'Authorization: GGS ' . $partnerCode . ':' . $password;
        }

        if (empty($this->key))
        {
            throw new SSLCenterException('Authorization key is required');
        }

        return 'Authorization: GGS ' . $this->key;
    }

    protected function callV2($uri, $method = 'GET', $payload = null, $partnerCode = null, $password = null)
    {
        $method = strtoupper($method);
        $url = rtrim($this->apiV2Url, '/') . '/' . ltrim($uri, '/');

        $this->lastRequest = [
            'uri' => $uri,
            'method' => $method,
            'post' => $payload,
            'api_version' => 'v2',
        ];

        $headers = [
            $this->buildV2AuthorizationHeader($partnerCode, $password),
            'Accept: application/json',
        ];

        $configuration = new Configuration();
        $headers[] = 'User-Agent: whmcs/' . $configuration->version;

        $queryData = '';
        if (!is_null($payload))
        {
            $queryData = json_encode($payload);
            if ($queryData === false)
            {
                throw new SSLCenterApiException('Invalid JSON payload for API V2 request');
            }

            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($queryData);
        }

        $c = curl_init($url);
        if ($method === 'POST')
        {
            curl_setopt($c, CURLOPT_POST, true);
        }
        else if ($method !== 'GET')
        {
            curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($queryData !== '')
        {
            curl_setopt($c, CURLOPT_POSTFIELDS, $queryData);
        }

        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLINFO_HEADER_OUT, true);
        curl_setopt($c, CURLOPT_HEADER, true);

        $data = curl_exec($c);
        $info = curl_getinfo($c);
        $result = substr($data, $info['header_size']);
        $status = (int) curl_getinfo($c, CURLINFO_HTTP_CODE);

        logModuleCall('SSLCENTERWHMCS', 'Time: '.$info['total_time'].' '.$uri.' [v2]', $info['request_header'].$queryData, $data);

        if ($result === false)
        {
            $error = curl_error($c);
            curl_close($c);
            throw new SSLCenterException($error);
        }

        curl_close($c);
        $this->lastStatus = $status;

        if ($status === 204 || trim((string) $result) === '')
        {
            $this->lastResponse = ['error' => false, 'status' => $status];
            return $this->lastResponse;
        }

        $this->lastResponse = json_decode($result, true);
        if (!is_array($this->lastResponse))
        {
            throw new SSLCenterApiException('Invalid Response from API V2');
        }

        if ($status >= 400)
        {
            $message = !empty($this->lastResponse['message']) ? $this->lastResponse['message'] : 'API V2 request failed';
            throw new SSLCenterApiException($message);
        }

        return $this->lastResponse;
    }

    protected function call($uri, $getData = array(), $postData = array(), $forcePost = false, $isFile = false, $httpMethod = null) {

        $post = !empty($postData) || $forcePost ? true : false;
        $method = strtoupper($httpMethod ? $httpMethod : ($post ? 'POST' : 'GET'));
        $this->lastRequest = [
            'uri' => $uri,
            'method' => $method,
            'post' => $postData,
        ];

        if($uri !== '/auth/') {
            $getData['auth_key'] = $this->key;
        }

        if($uri !== '/auth/' AND !$this->key) {
            throw new SSLCenterException('Authorization key is required');
        }
        $url = $this->apiUrl . $uri;
        if (!empty($getData)) {
            foreach ($getData as $key => $value) {
                $url .= (strpos($url, '?') !== false ? '&' : '?') . urlencode($key) . '=' . rawurlencode($value);
            }
        }

        $c = curl_init($url);
        if ($method === 'POST') {
            curl_setopt($c, CURLOPT_POST, true);
        } else if ($method !== 'GET') {
            curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
        }

        $queryData = '';
        if (!empty($postData)) {
            $queryData = $isFile ? $postData : htmlspecialchars_decode(http_build_query($postData));
            curl_setopt($c, CURLOPT_POSTFIELDS, $queryData);
        }

        $configuration = new Configuration();

        $headers = [];
        $headers[] = 'User-Agent: whmcs/'.$configuration->version;

        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($c, CURLINFO_HEADER_OUT,true);
        curl_setopt($c, CURLOPT_HEADER,true);

        $data = curl_exec($c);
        $info = curl_getinfo($c);
        $result = substr($data, $info['header_size']);

        if (DEBUG == 'TRUE') {
            echo "\n\n";
            echo "===============\n";
            echo __FILE__ . "\n";
            echo "===============\n\n";
            echo "url = " . $url . "\n\n";
            echo "queryData = " . urldecode($queryData) . "\n\n";
            echo "getData = \n";
            print_r($getData) . "\n\n";
            echo "postData = \n";
            print_r($postData) . "\n\n";
            echo "result SSLCenterApi = \n";
            print_r(json_decode($result, true));
            echo "\n\n";
        }

        logModuleCall('SSLCENTERWHMCS','Time: '.$info['total_time'].' '.$uri, $info['request_header'].$queryData, $data);

        if ($result === false) {
            throw new SSLCenterException(curl_error($c));
        }

        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

        if($status == '403')
        {
            Capsule::table('tblconfiguration')->where('setting', 'sslcenter_authkey')->delete();
        }

        curl_close($c);
        $this->lastStatus = $status;
        $this->lastResponse = json_decode($result, true);

        if(is_null($this->lastResponse)) {
            throw new SSLCenterApiException('Invalid Response from API');
        }

        if($this->lastResponse['error'] === true AND $this->apiExceptions AND $this->exceptionType === 'SSLCenterException') {
            throw new SSLCenterException($this->lastResponse['description']);
        }

        if($this->lastResponse['error'] === true AND $this->apiExceptions AND $this->exceptionType === 'SSLCenterApiException') {
            throw new SSLCenterApiException($this->lastResponse['description']);
        }

        return $this->lastResponse;
    }

    public function getLastStatus() {
        return $this->lastStatus;
    }

    public function getLastResponse() {
        return $this->lastResponse;
    }
    
  public function getLastRequest() {
        return $this->lastRequest;
    }
}

class SSLCenterException extends \Exception {

}

class SSLCenterApiException extends \Exception {

}
