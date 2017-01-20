<?php

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright © All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

abstract class BanzillaAPIResourceBase
{

    protected $id;
    protected $parent;
    protected $resourceName = '';
    protected $serializableData;
    protected $noSerializableData;
    protected $derivedResources;

    protected function __construct($resourceName, $params = array()) {
        $this->resourceName = $resourceName;
        $this->serializableData = array();
        $this->noSerializableData = array();

        if (!is_array($params)) {
            throw new BanzillaAPIError("Invalid parameter type detected when instantiating an Banzilla resource (passed '".gettype($params)."', array expected)");
        }

        foreach ($params as $key => $value) {
            if ($key == 'id') {
                $this->id = $value;
                continue;
            }
            if ($key == 'parent') {
                $this->parent = $value;
                continue;
            }
            $this->serializableData[$key] = $value;
        }

        if ($derived = $this->derivedResources) {
            foreach ($derived as $k => $v) {
                $name = strtolower($k).'s';
                $this->derivedResources[$name] = $this->processAttribute($k, $v);

                // unsets the original attribute
                unset($this->derivedResources[$k]);
            }
        }
    }

    protected static function getInstance($resourceName, $props = null) {
        BanzillaConsole::trace('BanzillaAPIResourceBase @getInstance > '.$resourceName);
        if (!class_exists($resourceName)) {
            throw new BanzillaApiError("Invalid Banzilla resource type (class resource '".$resourceName."' is invalid)");
        }
        if (is_string($props)) {
            $props = array('id' => $props);
        } else if (!is_array($props)) {
            $props = array();
        }
        $resource = new $resourceName($resourceName, $props);
        return $resource;
    }

    // ---------------------------------------------------------
    // ------------------  PRIVATE FUNCTIONS  ------------------

    private function isList($var) {
        if (!is_array($var))
            return false;

        foreach (array_keys($var) as $k) {
            if (!is_numeric($k))
                return false;
        }
        return true;
    }

    private function processAttribute($k, $v) {
        BanzillaConsole::trace('BanzillaAPIResourceBase @processAttribute > '.$k);
        $value = null;

        $resourceName = $this->getResourceName($k);
        if ($this->isResource($resourceName)) {
            // check is its a resource list
            if ($this->isList($v)) {
                $list = BanzillaAPIDerivedResource::getInstance($resourceName);
                $list->parent = $this;
                foreach ($v as $index => $objData) {
                    $list->add($objData);
                }
                $value = $list;
            } else {
                $resource = self::getInstance($resourceName);
                $resource->parent = $this;
                $resource->refreshData($v);
                $value = $resource;

                if ($resourceName != $this->resourceName) {
                    $this->registerInParent($resource);
                }
            }
        } else {
            if (is_array($v)) {
                // if it's an array, then is an object an instance a standar class

                $object = new stdClass();
                foreach ($v as $key => $value) {
                    $object->$key = $value;
                }
                $value = $object;
            } else {
                $value = $v;
            }
        }
        return $value;
    }

    private function getResourceName($name) {
        BanzillaConsole::trace('BanzillaAPIResourceBase @getResourceName');
        if (substr($name, 0, strlen('Banzilla')) == 'Banzilla') {
            return $name;
        }
        return 'Banzilla'.ucfirst($name);
    }

    private function isResource($resourceName) {
        BanzillaConsole::trace('BanzillaApiResourceBase @isResource > '.$resourceName);
// 		$resourceName = $this->getResourceName($name);

        return class_exists($resourceName);
    }

    private function registerInParent($resource) {
        //BanzillaConsole::trace('BanzillaAPIResourceBase @registerInParent');
        $parent = $this->parent;
        if ($parent instanceof BanzillaAPIDerivedResource) {
            $parent = $this->parent->parent;
        }

        if (!is_object($parent)) {
            return;
        }

        if ($container = $parent->getResource($resource->resourceName)) { // $resourceName
            if ($container instanceof BanzillaAPIDerivedResource && method_exists($container, 'addResource')) {
                //BanzillaConsole::trace('BanzillaAPIResourceBase @registerInParent > registering derived resource in parent');
                $container->addResource($resource);
            }
        }
    }

    private function getSerializeParameters() {
        //BanzillaConsole::trace('BanzillaAPIResourceBase @getSerializeParameters');
        return $this->serializableData;
    }

    private function getResource($resourceName) {
        foreach ($this->derivedResources as $resource) {
            if ($resource->resourceName == $resourceName) {
                return $resource;
            }
        }
        return false;
    }

    // ---------------------------------------------------------
    // -----------------  PROTECTED FUNCTIONS  -----------------

    protected function refreshData($data) {
        //BanzillaConsole::trace('BanzillaAPIResourceBase @refreshData');

        if (!$data) {
            return $this;
        }

        if (!is_array($data)) {
            //throw new BanzillaApiError("Invalid data received for processing, cannot update the Banzilla resource");
        }

        // unsets the unused attributes
        $removed = array_diff(array_keys($this->serializableData), array_keys($data));
        if (count($removed)) {
            //BanzillaConsole::debug('BanzillaAPIResourceBase @refreshData > removing unused data');
            foreach ($removed as $k) {
                if ($this->serializableData[$k]) {
                    unset($this->serializableData[$k]);
                }
                if ($this->noSerializableData[$k]) {
                    $this->noSerializableData[$k] = null;
                }
                if ($this->derivedResources[$k]) {
                    //$this->derivedResources[$k] = null;
                }
            }
        }

        foreach ($data as $k => $v) {
            $k = strtolower($k);

            $value = $this->processAttribute($k, $v);

            if ($k == 'id') {
                if (!isset($this->id)) {
                    $this->id = $v;
                }
                continue;

                // by default, only protected vars & serializable data will be refresh
                // in this version, noSerializableData does not store any value
            } else if (property_exists($this, $k)) {
                $this->$k = $value;
                //if ($this->noSerializableData[$k]) {
                //	$this->noSerializableData[$k] = $value;
                //}
            } else {
                $this->serializableData[$k] = $value;
            }
        }
        return $this;
    }

    protected function getResourceUrlName($pluralize = true) {
        $class = $this->resourceName;
        if (substr($class, 0, strlen('Banzilla')) == 'Banzilla') {
            $class = substr($class, strlen('Banzilla'));
        }
        if (substr($class, -1 * strlen('List')) == 'List') {
            $class = substr($class, 0, -1 * strlen('List'));
        }
        return strtolower(urlencode($class)).($pluralize ? '' : '');
    }

    protected function _validateParams($params, $method) {
        BanzillaConsole::trace('BanzillaAPIResourceBase @validateParams');

        if (!is_array($params)) {
            throw new BanzillaAPIRequestError("Invalid parameters type detected (type '".gettype($params)."' received, Array expected)");
        }else{

            //Order validate info

            if(!isset($params['Order']) || $params['Order'] == ''){
                throw new BanzillaAPIRequestError("The order info are empty or null", -1001);
            }else{
                if(!isset($params['Order']['Reference']) || $params['Order']['Reference'] ==''){
                    throw new BanzillaAPIRequestError("The order reference field are empty or null", -1001);
                }
                if(!isset($params['Order']['Amount']) || $params['Order']['Amount'] ==''){
                    throw new BanzillaAPIRequestError("The order amount field are empty or null", -1001);
                }
                if(!isset($params['Order']['Currency']) || $params['Order']['Currency'] ==''){
                    throw new BanzillaAPIRequestError("The order currency field are empty or null", -1001);
                }
            }

            //customer validate info

            if(!isset($params['Customer']) || $params['Customer'] == ''){
                throw new BanzillaAPIRequestError("The Customer info are empty or null");
            }else{
                if(!isset($params['Customer']['FirstName']) || $params['Customer']['FirstName'] ==''){
                    throw new BanzillaAPIRequestError("The Customer FirstName field are empty or null", -1001);
                }
                if(!isset($params['Customer']['MiddleName']) || $params['Customer']['MiddleName'] ==''){
                    throw new BanzillaAPIRequestError("The Customer MiddleName field are empty or null", -1001);
                }
                if(!isset($params['Customer']['Email']) || $params['Customer']['Email'] ==''){
                    throw new BanzillaAPIRequestError("The Customer Email field are empty or null", -1001);
                }
                if(!isset($params['Customer']['Address']) || $params['Customer']['Address'] ==''){
                    throw new BanzillaAPIRequestError("The Customer Address info are empty or null", -1001);
                }else{
                    if(!isset($params['Customer']['Address']['Street']) || $params['Customer']['Address']['Street'] ==''){
                        throw new BanzillaAPIRequestError("The Customer Adress Street field are empty or null", -1001);
                    }
                    if(!isset($params['Customer']['Address']['Number']) || $params['Customer']['Address']['Number'] ==''){
                        throw new BanzillaAPIRequestError("The Customer Adress Number field are empty or null", -1001);
                    }
                    if(!isset($params['Customer']['Address']['City']) || $params['Customer']['Address']['City'] ==''){
                        throw new BanzillaAPIRequestError("The Customer Adress City field are empty or null", -1001);
                    }
                    if(!isset($params['Customer']['Address']['State']) || $params['Customer']['Address']['State'] ==''){
                        throw new BanzillaAPIRequestError("The Customer Adress State field are empty or null", -1001);
                    }else{
                        self::validateIsoCity($params['Customer']['Address']['State']);
                    }
                    if(!isset($params['Customer']['Address']['Country']) || $params['Customer']['Address']['Country'] ==''){
                        throw new BanzillaAPIRequestError("The Customer Adress Country field are empty or null", -1001);
                    }else{
                        self::validateIsoCountry($params['Customer']['Address']['Country']);
                    }
                    if(!isset($params['Customer']['Address']['ZipCode']) || $params['Customer']['Address']['ZipCode'] ==''){
                        throw new BanzillaAPIRequestError("The Customer Adress ZipCode field are empty or null", -1001);
                    }
                }
            }

            switch ($method) {
                case 'card':
                    if(!isset($params['Card']) || $params['Card'] == ''){
                        throw new BanzillaAPIRequestError("The Card info are empty or null");
                    }else{
                        if(!isset($params['Card']['HolderName']) || $params['Card']['HolderName'] ==''){
                            throw new BanzillaAPIRequestError("The Card HolderName field are empty or null", -1001);
                        }
                        if(!isset($params['Card']['CardNumber']) || $params['Card']['CardNumber'] ==''){
                            throw new BanzillaAPIRequestError("The Card CardNumber field are empty or null", -1001);
                        }
                        if(!isset($params['Card']['SecurityCode']) || $params['Card']['SecurityCode'] ==''){
                            throw new BanzillaAPIRequestError("The Card SecurityCode field are empty or null", -1001);
                        }
                        if(!isset($params['Card']['Address']) || $params['Card']['Address'] ==''){
                            throw new BanzillaAPIRequestError("The Card Address info are empty or null");
                        }else{
                            if(!isset($params['Card']['Address']['Street']) || $params['Card']['Address']['Street'] ==''){
                                throw new BanzillaAPIRequestError("The Card Adress Street field are empty or null", -1001);
                            }
                            if(!isset($params['Card']['Address']['Number']) || $params['Card']['Address']['Number'] ==''){
                                throw new BanzillaAPIRequestError("The Card Adress Number field are empty or null", -1001);
                            }
                            if(!isset($params['Card']['Address']['City']) || $params['Card']['Address']['City'] ==''){
                                throw new BanzillaAPIRequestError("The Card Adress City field are empty or null", -1001);
                            }
                            if(!isset($params['Card']['Address']['State']) || $params['Card']['Address']['State'] ==''){
                                throw new BanzillaAPIRequestError("The Card Adress State field are empty or null", -1001);
                            }else{
                                self::validateIsoCity($params['Card']['Address']['State']);
                            }
                            if(!isset($params['Card']['Address']['Country']) || $params['Card']['Address']['Country'] ==''){
                                throw new BanzillaAPIRequestError("The Card Adress Country field are empty or null", -1001);
                            }else{
                                self::validateIsoCountry($params['Card']['Address']['Country']);
                            }
                            if(!isset($params['Card']['Address']['ZipCode']) || $params['Card']['Address']['ZipCode'] ==''){
                                throw new BanzillaAPIRequestError("The Card Adress ZipCode field are empty or null", -1001);
                            }
                        }
                    }

                    break;
                
                case 'store':
                    if(!isset($params['DueDate']) || $params['DueDate'] == ''){
                        throw new BanzillaAPIRequestError("The DueDate field are empty or null", -1001);
                    }
                    break;

                case 'transfer':
                    if(!isset($params['DueDate']) || $params['DueDate'] == ''){
                        throw new BanzillaAPIRequestError("The DueDate field are empty or null", -1001);
                    }
                    break;

                default:
                    
                    break;
            }

        }
    }

    protected function validateId($id) {
        //BanzillaConsole::trace('BanzillaAPIResourceBase @validateId');
        if (!is_string($id) || !preg_match('/^[a-z][a-z0-9]{0,20}$/i', $id)) {
            //throw new BanzillaApiRequestError("Invalid ID detected (value '".$id."' received, alphanumeric string not longer than 20 characters expected)");
        }
    }

    protected function _validateCards($numCard) {
        if(is_numeric($numCard)){
            $validNumber = self::isValidNumber($numCard);
            
            if($validNumber == true){
                $type = self::cardType($numCard);
                return $type;
            }
            throw new BanzillaAPIRequestError("Invalid card", -3001);

        }else{
            throw new BanzillaAPIRequestError('The field number of card must be a numerical value', -1001);
        }
    }

    protected function cardType($numCard){
        $num = (string)$numCard;
        
        if (substr($num, 0, 1)=='4'){
            $type['brand'] = 'visa';
            $type['gateway'] = 'prosa';
        }else if (substr($num, 0, 2)=='51' || substr($num, 0, 2)=='52' || substr($num, 0, 2)=='53' || substr($num, 0, 2)=='54' || substr($num, 0, 2)=='55'){
            $type['brand'] = 'mastercard';
            $type['gateway'] = 'prosa';
        }else if (substr($num, 0, 2)=='34' || substr($num, 0, 2)=='37'){
            $type['brand'] = 'amex';
            $type['gateway'] = 'amex';
        }else if (substr($num, 0, 3)=='300' || substr($num, 0, 3)=='301' || substr($num, 0, 3)=='302' || substr($num, 0, 3)=='303' || substr($num, 0, 3)=='304' || substr($num, 0, 3)=='305' || substr($num, 0, 2)=='36' || substr($num, 0, 2)=='38'){
            $type['brand'] = 'diners';
            $type['gateway'] = 'prosa';
        }else if (substr($num, 0, 1)=='1'){
            $type['brand'] = 'unknown';
            $type['gateway'] = 'prosa';
        }else{
            throw new BanzillaAPIRequestError('Invalid type card', -3001);
        }
        return $type;
    }

    protected function isValidNumber($number){

        $number_length=strlen($number);
        $parity=$number_length % 2;

        $total=0;
        for ($i=0; $i<$number_length; $i++) {
        $digit=$number[$i];
        
        if ($i % 2 == $parity) {
            $digit*=2;
            
            if ($digit > 9) {
                $digit-=9;
            }
        }
        
        $total+=$digit;
      }

      return ($total % 10 == 0) ? true : false;

    }

    protected function validateIsoCity($codeCity) {

        $code_length = strlen($codeCity);

        if($code_length == 2 ){
            
            if(!preg_match("/^([A-Z]+){0,20}$/", $codeCity)){
                throw new BanzillaAPIRequestError("The State code format is in a not valid characters");
            }
            
        }else{
            throw new BanzillaAPIRequestError("The State code must be two characters");
        }
        
    }

    protected function validateIsoCountry($codeCountry) {

        $code_length = strlen($codeCountry);

        if($code_length == 3 ){

            if(!preg_match("/^([A-Z]+){0,20}$/", $codeCountry)){
                throw new BanzillaAPIRequestError("The Country code format is in a not valid characters");
            }

        }else{
            throw new BanzillaAPIRequestError("The Country code must be two characters");
        }
        
    }

    protected function _create($resourceName, $params, $props = null) {

        $resource = self::getInstance($resourceName, $props);
        
        $response = BanzillaAPISocket::request('post', $resource->getUrl(), $params);
        return $resource->refreshData($response);
    }

    protected function _retrieve($resourceName, $id, $props = null) {
        if ($props && is_array($props)) {
            $props['id'] = $id;
        } else {
            $props = array('id' => $id);
        }
        $resource = self::getInstance($resourceName, $props);
        $resource->validateId($id);

        $response = BanzillaAPISocket::request('get', $resource->getUrl(), $id);
        return $resource->refreshData($response);
    }

    protected function _find($resourceName, $params, $props = null) {

        $resource = self::getInstance($resourceName, $props);
        $resource->validateParams($params);

        $list = array();
        $response = BanzillaAPISocket::request('get', $resource->getUrl(), $params);
        
        if (!empty($response['List'])) {
            foreach ($response['List'] as $v) {
                $item = self::getInstance($resourceName);
                $item->refreshData($v);
                array_push($list, $item);
            }
        }
        return $list;
    }

    protected function _update() {
        $params = $this->getSerializeParameters();

        if (count($params)) {
           // $response = BanzillaAPISocket::request('put', $this->getUrl(), $params);
            return $this->refreshData($response);
        }
    }

    protected function _updateCharge($params) {
        if (count($params)) {
            //$response = BanzillaAPISocket::request('put', $this->getResourceUrl(), $params);
            return $this->refreshData($response);
        }
    }

    protected function _delete($resourceName, $params, $props = null) {

        $resource = self::getInstance($resourceName, $props);

        $response = BanzillaAPISocket::request('delete', $resource->getUrl(), $params);

        return $resource->refreshData($response);
    }

    protected function _put($resourceName, $params, $props = null) {

        $resource = self::getInstance($resourceName, $props);

        $response = BanzillaAPISocket::request('put', $resource->getUrl(), $params);

        return $resource->refreshData($response);
    }

    protected function _getAttributes($param) {
        $url = $this->getUrl();
        $response = BanzillaAPISocket::request('get', $url, null);
        return json_decode(json_encode($response));
    }


    /* ==================== PUBLIC FUNCTIONS  ========================== */

    public function getUrl() { // $includeId = true
        //BanzillaConsole::trace('BanzillaAPIResourceBase @getUrl > class/parent: '.get_class($this).'/'.($this->parent ? 'true' : 'false'));
        $parentUrl = '';

        if ($this->parent) {
            $parentUrl = $this->parent->getUrl();
            if ($this->parent instanceof BanzillaAPIDerivedResource) {
                return $parentUrl;
            }
        }
        $resourceUrlName = $this->getResourceUrlName();
        return ($parentUrl != '' ? $parentUrl : '').($resourceUrlName != '' ? $resourceUrlName : '');
    }

    // ---------------------------------------------------------
    // --------------------  MAGIC METHODS  --------------------

    public function __set($key, $value) {
        BnazillaConsole::trace('BanzillaAPIResourceBase @__set > '.$key.' = '.$value);
        if ($value === '' || !$value) {
            error_log("[Banzilla Notice] The property '".$key."' will be set to en empty string which will be intepreted ad a NULL in request");
        }
        if (isset($this->$key) && is_array($value)) {
            
            // $this->$key->replaceWith($value);
            throw new BanzillaAPIError("The property '".$key."' cannot be assigned directly with an array");
            //} else if (property_exists($this, $key)) {
            //	$this->$key = $value;
        } else if (isset($this->serializableData[$key])) {
            $this->serializableData[$key] = $value;
        } elseif (isset($this->derivedResources[$key])) {
            $this->derivedResources[$key] = $value;
        }
    }

    public function __get($key) {
        //echo '<br><br>/*inicio de item*/<br><br><pre>'; print_r($key); echo '<br><br>'; print_r($this);echo '</pre>'; echo '<br><br>>/*final de item*/<br><br>';
        if (property_exists($this, $key)) {
            return $this->$key;
        } else if (array_key_exists($key, $this->serializableData)) {
            return $this->serializableData[$key];
        } else if (array_key_exists($key, $this->derivedResources)) {
            return $this->derivedResources[$key];
        } else if (array_key_exists($key, $this->noSerializableData)) {
            return $this->noSerializableData[$key];
        } else {
            $resourceName = get_class($this);
            error_log("[Banzilla Notice] Undefined property of $resourceName instance: $key");
            return null;
        }
    }

}

?>