<?php 

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright © All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class BanzillaAPIError extends Exception {
	protected $description;
	protected $error_code;
	protected $category;
	protected $http_code;
	protected $request_id;
	
	public function __construct($message=null, $error_code=null, $category=null, $request_id=null, $http_code=null) {
	    parent::__construct($message, $error_code);
	    $this->description = $message;
	    $this->error_code  = isset($error_code) ? $error_code : 0;
	    $this->category    = isset($category) ? $category : '';
	    $this->http_code   = isset($http_code) ? $http_code : 0;
	    $this->request_id  = isset($request_id) ? $request_id : '';
	}
	public function getDescription() {
		return $this->description;
	}
	public function getErrorCode() {
		return $this->error_code;
	}
	public function getCategory() {
		return $this->category;
	}
	public function getHttpCode() {
		return $this->http_code;
	}
	public function getRequestId() {
		return $this->request_id;
	}
}

// Authentication related Errors
class BanzillaAPIAuthError extends BanzillaAPIError {

}

// Request related Error
class BanzillaAPIRequestError extends BanzillaAPIError {
}
// Transaction related Errors
class BanzillaAPITransactionError extends BanzillaAPIError {
}

// Connection related Errors
class BanzillaAPIConnectionError extends BanzillaAPIError {
}
