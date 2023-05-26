<?php
/**
 * @copyright Salix.me
 * @author Panos <panos@salix.gr>
 * Created: 22/Μάρ/2023
 * Time: 21:58
 */

namespace Slx\CleverPoint;

use Tygh\Registry;

class ApiCall {

    private $apiKey = '';
    private $apiUrl = '';
    public $lastResponseCode = 0;
    public $lastError = '';

    private function getOtherConnInfo() {
        $this->apiKey = Registry::get('addons.slx_clever_point.api_key');
        $this->apiUrl = sprintf("https://%s.cleverpoint.gr/api/v1/", Registry::get('addons.slx_clever_point.mode'));
    }

    public function get($uri, $qryParams = []) {
        return $this->makeCall($uri, 'GET', $qryParams);
    }

    public function post($uri, $qryParams, $payload) {
        return $this->makeCall($uri, 'POST', $qryParams, [],$payload);
    }

    public function put($uri, $qryParams, $payload) {
        return $this->makeCall($uri, 'PUT', $qryParams, [], $payload);
    }

    private function makeCall($uri, $method, $qryParams = [], $curlOpts = [], $payload = []) {
        $this->getOtherConnInfo();
        $curl = curl_init();
        $defaultOpts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];
        $curlOpts = fn_array_merge($curlOpts, $defaultOpts);
        $curlOpts[CURLOPT_CUSTOMREQUEST] = $method;
        $url = $this->apiUrl . $uri;
        if(!empty($qryParams)) {
            $url = $url . '?' . http_build_query($qryParams);
        }
        $curlOpts[CURLOPT_URL] = $url;
        $curlOpts[CURLOPT_HTTPHEADER] = [];
        if(!empty($payload)) {
            $curlOpts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            $curlOpts[CURLOPT_POSTFIELDS] = json_encode($payload);
        }
        if(empty($payload) && $method=='POST') {
            $curlOpts[CURLOPT_POSTFIELDS] = [];
        }
        $curlOpts[CURLOPT_HTTPHEADER][] = 'Authorization: ApiKey ' . $this->apiKey;
        curl_setopt_array($curl, $curlOpts);
        $response = curl_exec($curl);
        $this->lastError = curl_error($curl);
        $this->lastResponseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        $out = @json_decode($response, true);

        fn_log_event('requests', 'http', array(            
            'url' => $url,
            'data' => json_encode(['method' => $method, 'payload' => $payload],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES ),
            'response' => json_encode(
            	['resp'=> $response, 'resp_code' => $this->lastResponseCode,'error' => $this->lastError,],
            	JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES 
            )
            
        ));
        if(!is_array($out)) {
            $out = [];
        }
        return $out;
    }
}
