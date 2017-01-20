<?php

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright © All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class Banzilla
{

    private static $instance = null;
    private static $apiKey = '';
    private static $secretKey = '';
    private static $apiAuthentication = '';
    private static $apiEndpoint = 'https://api.banzilla.com/v1/';
    private static $apiSandboxEndpoint = 'https://sandbox.banzilla.com/v1/';
    private static $sandboxMode =  true;

    public function __construct() {
        
    }

    public static function getInstance($apiKey = '', $secretKey = '', $sandbox = '') {
        
        if ($apiKey != '') {
            self::setId($apiKey);
        }
        if ($secretKey != '') {
            self::setSecretKey($secretKey);
        }
        if ($sandbox != '') {
            self::setSandboxMode($sandbox);
        }
         if ($apiKey != '' && $secretKey != '') {
            self::setAuth($apiKey, $secretKey);
        }
        $instance = BanzillaAPI::getInstance(null);
        return $instance;
    }

    public static function setSecretKey($secretKey = '') {
        if ($secretKey != '') {
            self::$secretKey = $secretKey;
        }
    }

    public static function getSecretKey() {
        $secretKey = self::$secretKey;
        if (!$secretKey) {
            $secretKey = getenv('BANZILLA_API_KEY');
        }
        return $apiKey;
    }

    public static function setId($apiKey = '') {
        if ($apiKey != '') {
            self::$apiKey = $apiKey;
        }
    }

    public static function getId() {
        $apiKey = self::$apiKey;
        if (!$apiKey) {
            $apiKey = getenv('BANZILLA_MERCHANT_ID');
        }
        return $apiKey;
    }

    public static function setAuth(){
        $apiKey = self::$apiKey;
        $secretKey = self::$secretKey;
         if ($apiKey != '' && $secretKey != '') {
            self::$apiAuthentication = base64_encode($apiKey.':'.$secretKey);
        }
        
    }

    public static function getAuth(){
        $apiAuthentication = self::$apiAuthentication;
        if (!$apiAuthentication) {
            $apiAuthentication = getenv('BANZILLA_AUTH');
        }
        return $apiAuthentication;
        
    }

    public static function getSandboxMode() {
        $sandbox = self::$sandboxMode;
        if (getenv('BANZILLA_PRODUCTION_MODE')) {
            $sandbox = (strtoupper(getenv('BANZILLA_PRODUCTION_MODE')) == 'FALSE');
        }
        return $sandbox;
    }

    public static function setSandboxMode($mode) {
        self::$sandboxMode = $mode ? true : false;
    }

    public static function getProductionMode() {
        $sandbox = self::$sandboxMode;
        if (getenv('BANZILLA_PRODUCTION_MODE')) {
            $sandbox = (strtoupper(getenv('BANZILLA_PRODUCTION_MODE')) == 'FALSE');
        }
        return !$sandbox;
    }

    public static function setProductionMode($mode) {
        self::$sandboxMode = $mode ? false : true;
    }

    public static function getEndpointUrl() {
        return (self::getSandboxMode() ? self::$apiSandboxEndpoint : self::$apiEndpoint);
    }

}

// ----------------------------------------------------------------------------
class BanzillaAPI extends BanzillaAPIResourceBase
{

    protected $derivedResources = array(
        'refund' => array(),
        'transactions' => array(),
        'Charge' => array(),
        'Cards' => array(),
        'subscription' => array(),
        'Plans' => array(),
        'Webhooks' => array(),
        'Token' => array());

    public static function getInstance($r, $p = null) {
        $resourceName = get_class();
        return parent::getInstance($resourceName);
    }

    protected function getResourceUrlName($p = true) {
        return '';
    }

    public function getFullURL() {
        return $this->getUrl();
    }

}


?>