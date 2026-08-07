<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

class Cpanel
{
    private $service;
    protected $ch = false;
    protected $sessionUrl;
    private $loggingEnabled = true;

    private function logCall($action, $request, $response, $processed = '', $replacements = [])
    {
        if (!$this->loggingEnabled) {
            return;
        }
        logModuleCall('SSLCENTERWHMCS_cpanel', $action, $request, $response, $processed, $replacements);
    }


    protected function initiateConnection($username)
    {
        $sessionDetails = $this->getSessionDetails($username);
        $decodedResponse = json_decode($sessionDetails, true);
        if(!$decodedResponse)
        {
            $this->ch = false;
            return false;
        }

        $this->setSessionDetails($decodedResponse);
    }

    protected function setSessionDetails($decodedResponse)
    {
        $sessionUrl = $decodedResponse['data']['url'];
        $cookieJar = 'cookie.txt';
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, []);
        curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookieJar);
        curl_setopt($this->ch, CURLOPT_URL, $sessionUrl);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        $resultorg = curl_exec($this->ch);

        $info = curl_getinfo($this->ch);
        $result = substr($resultorg, $info['header_size']);
        if($resultorg == false)
        {
            throw new \Exception(curl_error($this->ch));
        }
        $this->sessionUrl = preg_replace( '{/login(?:/)??.*}', '', $sessionUrl);
    }

    /**
     * Builds the Authorization header for WHM/cPanel API calls.
     *
     * Distinguishes between:
     *  - Legacy WHM access hash (long, may contain newlines) → "Authorization: WHM user:hash"
     *  - cPanel API token (short, alphanumeric, no newlines)  → "Authorization: cpanel user:token"
     */
    private function buildAuthHeader(): array
    {
        $hash = $this->service['hash'] ?? '';
        if (empty($hash)) {
            return [];
        }
        $clean   = str_replace(["\r", "\n"], '', $hash);
        $isToken = strpos($hash, "\n") === false && strlen($clean) < 200;
        $scheme  = 'WHM';
        return ['Authorization: ' . $scheme . ' ' . $this->service['username'] . ':' . $clean];
    }

    protected function getSessionDetails($username)
    {
        $query = ($this->service['ssl']? 'https://'.$this->service['hostname'].':2087' : 'http://'.$this->service['hostname'].':2086').
            "/json-api/create_user_session?api.version=1&user=".$username."&service=cpaneld";
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 15);
        $authHeader = $this->buildAuthHeader();

        if (!empty($authHeader)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $authHeader);
        } elseif ($this->service['password'] != '') {
            curl_setopt($this->ch, CURLOPT_USERPWD, $this->service['username'].':'.$this->service['password']);
        }
        curl_setopt($this->ch, CURLOPT_URL, $query);
        $resultorg = curl_exec($this->ch);

        $info = curl_getinfo($this->ch);
        $result = substr($resultorg, $info['header_size']);
        if($resultorg == false)
        {
            throw new \Exception(curl_error($this->ch));
        }
        return $result;
    }

    protected function exec($name, $api, $params = array())
    {
        $query = $this->sessionUrl.$api.$name;
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_URL, $query);
        if (!empty($params)) {
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
        }
        $resultorg = curl_exec($this->ch);
        $curl_info = curl_getinfo($this->ch);
        if($resultorg == false)
        {
            $error = curl_error($this->ch);
            $this->logCall($api . $name,
                $this->sanitizeParams($params),
                'cURL error: ' . $error,
                '',
                [$this->service['password'] ?? '', $this->service['hash'] ?? '']
            );
            throw new \Exception($error);
        }
        $result = substr($resultorg, $curl_info['header_size']);
        $resp   = json_decode($result);

        $this->logCall(
            $api . $name,
            $this->sanitizeParams($params),
            $result,
            isset($resp->errors) ? $resp->errors : (isset($resp->data) ? $resp->data : $resp),
            [$this->service['password'] ?? '', $this->service['hash'] ?? '']
        );

        return $resp;
    }

    private function sanitizeParams(array $params)
    {
        $safe = [];
        foreach ($params as $k => $v) {
            if (in_array($k, ['cert', 'key', 'cabundle'], true)) {
                $safe[$k] = '[' . strlen((string) $v) . ' bytes]';
            } else {
                $safe[$k] = $v;
            }
        }
        return $safe;
    }

    public function listDomains($username)
    {
        $this->initiateConnection($username);
        $response = $this->exec("list_domains", "/execute/DomainInfo/");

        $domains = [];
        if(isset($response->data->main_domain) && !empty($response->data->main_domain))
        {
            $domains[] = $response->data->main_domain;
        }

        if(isset($response->data->addon_domains) && !empty($response->data->addon_domains))
        {
            foreach($response->data->addon_domains as $domain)
            {
                $domains[] = $domain;
            }
        }

        if(isset($response->data->parked_domains) && !empty($response->data->parked_domains))
        {
            foreach($response->data->parked_domains as $domain)
            {
                $domains[] = $domain;
            }
        }

        if(isset($response->data->sub_domains) && !empty($response->data->sub_domains))
        {
            foreach($response->data->sub_domains as $domain)
            {
                $domains[] = $domain;
            }
        }

        return $domains;
    }

    public function listDomainsGrouped($username)
    {
        $this->initiateConnection($username);
        $response = $this->exec("list_domains", "/execute/DomainInfo/");

        $grouped = [
            'main'      => null,
            'addon'     => [],
            'parked'    => [],
            'subdomain' => [],
        ];

        if (isset($response->data->main_domain) && !empty($response->data->main_domain)) {
            $grouped['main'] = $response->data->main_domain;
        }

        if (isset($response->data->addon_domains) && !empty($response->data->addon_domains)) {
            foreach ($response->data->addon_domains as $domain) {
                $grouped['addon'][] = $domain;
            }
        }

        if (isset($response->data->parked_domains) && !empty($response->data->parked_domains)) {
            foreach ($response->data->parked_domains as $domain) {
                $grouped['parked'][] = $domain;
            }
        }

        if (isset($response->data->sub_domains) && !empty($response->data->sub_domains)) {
            $allKnownParents = [];
            if ($grouped['main']) {
                $allKnownParents[] = $grouped['main'];
            }
            foreach ($grouped['addon'] as $d) {
                $allKnownParents[] = $d;
            }

            foreach ($response->data->sub_domains as $domain) {
                $parent = null;
                $parts = explode('.', $domain);
                if (count($parts) > 2) {
                    $possibleParent = implode('.', array_slice($parts, 1));
                    if (in_array($possibleParent, $allKnownParents)) {
                        $parent = $possibleParent;
                    }
                }
                $grouped['subdomain'][] = ['domain' => $domain, 'parent' => $parent];
            }
        }

        return $grouped;
    }

    public function setService($service)
    {
        $this->service['hostname'] = $service->hostname;
        $this->service['ipaddress'] = $service->ipaddress;
        if($service->secure == 'on')
        {
            $this->service['ssl'] = true;
        }
        $this->service['port'] = $service->port;
        $this->service['hash'] = $service->hash;
        $this->service['username'] = $service->username;
        $this->service['password'] = decrypt($service->password);

        $apiConf = (new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository())->get();
        $this->loggingEnabled = empty($apiConf->disable_hosting_module_log);
    }

    public function getRootDirectory($username, $domain)
    {
        $this->initiateConnection($username);
        $response = $this->exec("single_domain_data", "/execute/DomainInfo/", [
            'domain' => $domain,
            'return_https_redirect_status' => '1'
        ]);
        if(isset($response->data->documentroot) && !empty($response->data->documentroot))
        {
            return $response->data->documentroot;
        }
        throw new \Exception($response->errors[0]);
    }

    public function installSSL($username, $domain, $cert, $key, $cabundle)
    {
        $this->initiateConnection($username);

        $data = [
            'domain'    => $domain,
            'cert'      => $cert,
            'key'       => $key,
            'cabundle'  => $cabundle,
        ];

        $response = $this->exec("install_ssl", "/execute/SSL/", $data);

        $sslLog = ['domain' => $domain, 'cert' => '[' . strlen($cert) . ' bytes]', 'key' => '[' . strlen($key) . ' bytes]', 'cabundle' => '[' . strlen($cabundle) . ' bytes]'];
        if (isset($response->errors) && !empty($response->errors)) {
            if (strpos($response->errors[0], 'install_ssl was handled successfully') !== false) {
                $this->logCall('installSSL', $sslLog,
                    'Success (reported via errors field): ' . $response->errors[0],
                    'installed',
                    [$this->service['password'] ?? '', $this->service['hash'] ?? '']
                );
                return true;
            }
            $this->logCall('installSSL', $sslLog,
                'Error: ' . $response->errors[0],
                'failed',
                [$this->service['password'] ?? '', $this->service['hash'] ?? '']
            );
            throw new \Exception($response->errors[0]);
        }

        $this->logCall('installSSL', $sslLog,
            json_encode($response),
            'success - no errors',
            [$this->service['password'] ?? '', $this->service['hash'] ?? '']
        );
        return true;
    }

    public function getFile($username, $file, $dir)
    {
        $this->initiateConnection($username);
        $response = $this->exec("get_file_content", "/execute/Fileman/", [
            'dir'           => $dir,
            'file'          => $file
        ]);

        return $response;
    }

    public function saveFile($username, $file, $dir, $content)
    {
        $this->initiateConnection($username);

        $requestLog = ['dir' => $dir, 'file' => $file, 'content' => $content];

        $response = $this->exec("save_file_content", "/execute/Fileman/", [
            'dir'           => $dir,
            'file'          => $file,
            'from_charset'  => 'UTF-8',
            'to_charset'    => 'ASCII',
            'content'       => $content,
            'fallback'      => '0',
        ]);

        if (isset($response->errors) && !empty($response->errors)) {
            $error = $response->errors[0];

            // On some cPanel versions save_file_content fails when the file
            // already exists. Delete it first and retry once.
            $isExistsError = stripos($error, 'exist') !== false
                          || stripos($error, 'already') !== false
                          || stripos($error, 'create') !== false;

            if ($isExistsError) {
                $this->exec("delete_files", "/execute/Fileman/", [
                    'files' => json_encode([$dir . $file]),
                ]);

                $response = $this->exec("save_file_content", "/execute/Fileman/", [
                    'dir'           => $dir,
                    'file'          => $file,
                    'from_charset'  => 'UTF-8',
                    'to_charset'    => 'ASCII',
                    'content'       => $content,
                    'fallback'      => '0',
                ]);
            }

            if (isset($response->errors) && !empty($response->errors)) {
                $this->logCall('saveFile', $requestLog,
                    'Error: ' . $response->errors[0],
                    'failed',
                    [$this->service['password'] ?? '', $this->service['hash'] ?? '']
                );
                throw new \Exception($response->errors[0]);
            }
        }

        $this->logCall('saveFile', $requestLog,
            json_encode($response),
            'success',
            [$this->service['password'] ?? '', $this->service['hash'] ?? '']
        );
        return true;
    }

    public function addRecord($user, $record)
    {
        $input['domain'] = $record->domain;
        $input['name'] = $record->name;
        $input['type'] = $record->type;

        if($input['type'] == 'TXT') {
            $input['txtdata'] = $record->txtdata;
            $input['ttl'] = $record->ttl;
        }

        if($input['type'] == 'CNAME') {
            $input['cname'] = $record->cname;
        }

        $input['cpanel_jsonapi_user'] = $user;
        $input['cpanel_jsonapi_apiversion'] = '2';
        $input['cpanel_jsonapi_module'] = 'ZoneEdit';
        $input['cpanel_jsonapi_func'] = 'add_zone_record';

        $this->get($input);
        return true;
    }

    public function addDirectory($user, $directories)
    {
        foreach ($directories as $newDir) {
            try {

                $input['cpanel_jsonapi_user'] = $user;
                $input['cpanel_jsonapi_apiversion'] = '2';
                $input['cpanel_jsonapi_module'] = 'Fileman';
                $input['cpanel_jsonapi_func'] = 'mkdir';
                $input['path'] = $newDir['dir'];
                $input['name'] = $newDir['name'];
                $this->get($input);

            } catch (\Exception $ex) {

                continue;

            }
        }
        return true;
    }

    private function get($params=array()) {

        $url = ($this->service['ssl']? 'https://'.$this->service['hostname'].':2087' : 'http://'.$this->service['hostname'].':2086').'/json-api/cpanel';
        $ch = curl_init();
        if(is_array($params)) {
            $url .= '?';
            foreach($params as $key=>$value) {
                $value = urlencode($value);
                $url .= "{$key}={$value}&";
            }
        }
        $url = trim($url, '&');
        $chOptions = array (
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => is_numeric($this->service['timeout']) ? intval($this->service['timeout']) : 30
        );

        $authHeader = $this->buildAuthHeader();
        if (!empty($authHeader)) {
            $chOptions[CURLOPT_HTTPHEADER] = $authHeader;
        } elseif ($this->service['password'] != '') {
            $chOptions[CURLOPT_USERPWD] = $this->service['username'].':'.$this->service['password'];
        } else {
            throw new \Exception('Password or Access key is required');
        }

        curl_setopt_array($ch, $chOptions);
        $out = curl_exec($ch);

        if (curl_errno($ch)) {
            $curlErr = curl_errno($ch) . ' - ' . curl_error($ch);
            $this->logCall($params['cpanel_jsonapi_func'] ?? 'cpanel_api',
                array_diff_key($params, array_flip(['cpanel_jsonapi_user'])),
                'cURL Error: ' . $curlErr,
                '',
                [$this->service['password'] ?? '', $this->service['hash'] ?? '']
            );
            throw new \Exception("cURL Error: " . $curlErr);
        }

        $out_info = curl_getinfo($ch);
        if($out_info['http_code'] != 200) {
            if($out_info['http_code'] == 301 || $out_info['http_code'] == 302){
                $this->logCall($params['cpanel_jsonapi_func'] ?? 'cpanel_api',
                    array_diff_key($params, array_flip(['cpanel_jsonapi_user'])),
                    'HTTP ' . $out_info['http_code'] . ' redirect — SSL required',
                    '',
                    [$this->service['password'] ?? '', $this->service['hash'] ?? '']
                );
                throw new \Exception('Module require SSL');
            }
        }

        curl_close($ch);

        if(strpos($out, 'SSL encryption is required for access to this server') !== FALSE) {
            throw new \Exception('SSL encryption is required for access to this server');
        }

        $a = json_decode($out);
        if($a===FALSE) {
            throw new \Exception('Unable to parse response');
        }

        if ($a->cpanelresult->error && $a->cpanelresult->data->result == 0)
        {
            throw new \Exception($a->cpanelresult->error);
        }

        if(isset($a->status) && $a->status == 0){
            throw new \Exception($a->statusmsg?:'Unknown Error');
        }

        if(isset($a->data->result) && $a->data->result == 0) {
            throw new \Exception($a->data->reason?:'Unknown Error');
        }

        if(!empty($a->error)) {
            throw new \Exception($a->error);
        }

        if(isset($a->metadata->result) && $a->metadata->result == 0) {
            $this->logCall($params['cpanel_jsonapi_func'] ?? 'cpanel_api',
                array_diff_key($params, array_flip(['cpanel_jsonapi_user'])),
                $out,
                'metadata result=0: ' . $a->metadata->reason,
                [$this->service['password'] ?? '', $this->service['hash'] ?? '']
            );
            throw new \Exception($a->metadata->reason);
        }

        $this->logCall($params['cpanel_jsonapi_func'] ?? 'cpanel_api',
            array_diff_key($params, array_flip(['cpanel_jsonapi_user'])),
            $out,
            isset($a->data) ? $a->data : $a,
            [$this->service['password'] ?? '', $this->service['hash'] ?? '']
        );

        return isset($a->data)?$a->data:$a;
    }


}