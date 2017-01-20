<?php

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class BanzillaAPISocket

{

    private static $instance;
    private $apiKey;
    private $apiAuthentication;

    private function __construct() {
        $this->apiKey = '';
        $this->apiAuthentication= '';
    }

    private static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    
    /* ======================  PRIVATE FUNCTIONS  ========================= */

    private function _request($method, $url, $params) {

        
        if (!class_exists('Banzilla')) {
            throw new BanzillaAPIError("Banzilla Library install error, there are some missing classes");
        }
        BanzillaConsole::trace('BanzillaAPISocket @_request');


        $myAuth = Banzilla::getAuth();
        if (!$myAuth) {
            throw new BanzillaAPIAuthError("The Authentication is empty ro not provided");
        } /*else if (!preg_match('/^sk_[a-z0-9]{32}$/i', $myApiKey)) {
            throw new BanzillaAPIAuthError("Invalid Private Key '".$myApiKey."'");
        }*/

        $absUrl = Banzilla::getEndpointUrl();
        if (!$absUrl) {
            throw new BanzillaAPIConnectionError("No API endpoint set");
        }
        $absUrl .= $url;

        $headers = array('Authorization: Basic '.$myAuth);

        list($rbody, $rcode) = $this->_curlRequest($method, $absUrl, $headers, $params);
        
        return $this->interpretResponse($rbody, $rcode);
    }

    private function _curlRequest($method, $absUrl, $headers, $params) {
        BanzillaConsole::trace('BanzillaAPISocket @_curlRequest');

        $opts = array();
        if (!is_array($headers)) {
            $headers = array();
        }

        if ($method == 'get') {
            $opts[CURLOPT_HTTPGET] = 1;
            if (count($params) > 0) {
                $encoded = $this->encodeToStringQuery($params);
                if(count($params) == 1){
                     $absUrl = $absUrl.'/'.$encoded;
                }else{
                    $absUrl = $absUrl.'?'.$encoded;
                }
                
            }

        } else if ($method == 'post') {
            $data = $this->encodeToJson($params);
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;
            array_push($headers, 'Content-Type: application/json');
            array_push($headers, 'Content-Length: '.strlen($data));
        } else if ($method == 'put') {
            $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
            if (count($params) > 0) {
                $encoded = $this->encodeToStringQuery($params);
                $absUrl = $absUrl.'/'.$encoded;
            }
            $data = $this->encodeToJson($params);
            $opts[CURLOPT_POSTFIELDS] = $data;
            array_push($headers, 'Content-Type: application/json');
            array_push($headers, 'Content-Length: '.strlen($data));
        } else if ($method == 'delete') {
            $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            if (count($params) > 0) {
                $encoded = $this->encodeToStringQuery($params);
                $absUrl = $absUrl.'/'.$encoded;
            }
        } else {
            throw new BanzillaAPIError("Invalid request method '".$method."'");
        }


        $opts[CURLOPT_URL] = $absUrl;
        $opts[CURLOPT_RETURNTRANSFER] = TRUE;
        $opts[CURLOPT_CONNECTTIMEOUT] = 30;
        $opts[CURLOPT_TIMEOUT] = 80;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_SSL_VERIFYPEER] = FALSE;

        $curl = curl_init();
        curl_setopt_array($curl, $opts);

        BanzillaConsole::debug('Executing cURL: '.strtoupper($method).' > '.$absUrl);

        $rbody = curl_exec($curl);
        $errorCode = curl_errno($curl);

        if ($errorCode == 60 || $errorCode == 77) {
            curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__).'/cacert.pem');
            $rbody = curl_exec($curl);
        }

        if ($rbody === false) {
            BanzillaConsole::error('cURL request error: '.curl_errno($curl));
            $message = curl_error($curl);
            $errorCode = curl_errno($curl);
            curl_close($curl);

            $this->handleCurlError($errorCode, $message);
        }
        $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if (mb_detect_encoding($rbody, 'UTF-8', true) != 'UTF-8') {
            BanzillaConsole::warn('Response body is not an UTF-8 string');
        }

        BanzillaConsole::debug('cURL body: '.$rbody);
        BanzillaConsole::debug('cURL code: '.$rcode);

        return array($rbody, $rcode);
    }

    private function encodeToStringQuery($arr, $prefix = null) {
        if (!is_array($arr))
            return $arr;

        $r = array();
        foreach ($arr as $k => $v) {
            if (is_null($v))
                continue;

            if ($prefix && $k && !is_int($k))
                $k = $prefix."[".$k."]";
            else if ($prefix)
                $k = $prefix."[]";

            if (is_array($v)) {
                $r[] = $this->encodeToStringQuery($v, $k, true);
            } else {
                $r[] = urlencode($k)."=".urlencode($v);
            }
        }
        $string = implode("&", $r);
        BanzillaConsole::debug('Query string: '.$string);
        return $string;
    }

    private function encodeToJson($arr) {
        $encoded = json_encode($arr);
        if (mb_detect_encoding($encoded, 'UTF-8', true) != 'UTF-8') {
            $encoded = utf8_encode($encoded);
        }
        BanzillaConsole::debug('JSON UTF8 string: '.$encoded);
        return $encoded;
    }

    private function interpretResponse($responseBody, $responseCode) {
        BanzillaConsole::trace('BanzillaAPISocket @interpretResponse');
        try {
            // return json as an array NOT an object
            if (!empty($responseBody)) {
                $traslatedResponse = json_decode($responseBody, true);
                
            } else {

                $traslatedResponse = array();
            }
        } catch (Exception $e) {
            throw new BanzillaAPIRequestError("Invalid response: ".$responseBody, $responseCode);
        }

        if ($responseCode < 200 || $responseCode >= 300) {

            BanzillaConsole::error('Request finished with HTTP code '.$responseCode);
            $this->handleRequestError($responseBody, $responseCode, $traslatedResponse);// este es el que cacha el error 
            return array();
        }
        
        return $traslatedResponse;
    }

    private function handleRequestError($responseBody, $responseCode, $traslatedResponse) {

        /*if (!is_array($traslatedResponse) || !isset($traslatedResponse['error_code'])) {
            throw new BanzillaAPIRequestError("Invalid response body received from BANZILLA API Server"); checar esto es validacionde respose
        }*/
        
        $message = isset($traslatedResponse['Description']) ? $traslatedResponse['Description'] : 'No description';
        $error = isset($traslatedResponse['ErrorCode']) ? $traslatedResponse['ErrorCode'] : null;
        $category = isset($traslatedResponse['Category']) ? $traslatedResponse['Category'] : null;
        $request_id = isset($traslatedResponse['IdRequest']) ? $traslatedResponse['IdRequest'] : null;
        $details_error = isset($traslatedResponse['Details']) ? implode ( ' Error: ' , $traslatedResponse['Details']): '';

        switch ($responseCode) {

            // Unauthorized - Forbidden
            case 401:
            case 403:
                throw new BanzillaAPIAuthError($message.' Details: '.$details_error, $error, $category, $request_id, $responseCode);
                break;

            // Bad Request - Request Entity too large - Request Entity too large - Internal Server Error - Service Unavailable
            case 400:
            case 404:
            case 413:
            case 422:
            case 500:
            case 503:
                throw new BanzillaAPIRequestError($message.' Details: '.$details_error, $error, $category, $request_id, $responseCode);
                break;

            // Payment Required - Conflict - Preconditon Failed - Unprocessable Entity - Locked
            case 402:
            case 409:
            case 412:
            case 423:
                throw new BanzillaAPITransactionError($message.' Details: '.$details_error, $error, $category, $request_id, $responseCode);
                break;

            // Not Found
            default:
                throw new BanzillaAPIError($message.' Details: '.$details_error, $error, $category, $request_id, $responseCode);
        }
    }

    private function handleCurlError($errorCode, $message) {
        switch ($errorCode) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connect to Banzilla.  Please check your internet connection and try again";
                break;
            default:
                $msg = "Unexpected error connecting to Banzilla";
        }

        $msg .= " (Network error ".$errorCode.")";
        throw new BanzillaAPIConnectionError($msg);
    }

/* ================================= Publics =============================================  */

    public static function request($method, $url, $params = null) {
        BanzillaConsole::trace('BanzillaAPISocket @request '.$url);

        if (!$params) {
            $params = array();
        }

        $method = strtolower($method);
        if (!in_array($method, array('get', 'post', 'delete', 'put'))) {
            throw new BanzillaAPIError("Invalid request method '".$method."'");
        }

        $connector = self::getInstance();
        return $connector->_request($method, $url, $params);
    }

}

?>