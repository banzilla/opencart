<?php

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright © All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

/*  ======================   BANZILLA DERIVED RESOURCE   =====================  */
/*  ======================      PROTECTED FUNCTIONS      =====================  */

class BanzillaAPIDerivedResource extends BanzillaAPIResourceBase {
	private $cacheList = array();

	protected static function getInstance($resourceName, $p = null) {
		if (class_exists($resourceName.'List', false)) {
			$resource = $resourceName.'List';
			return new $resource($resourceName);
		}
		return new self($resourceName);
	}
	protected function addResource($resource, $id=null) {
		if (!$id && isset($resource->id)) {
			$id = $resource->id;
		} else if (is_string($id)) {
			$id = strtolower($id);
		} else {
			$id = count($this->cacheList) + 1;
		}
		if (!$this->isResourceListed($id)) {
			$resource->parent = $this;
			$this->cacheList[$id] = $resource;
		}
	}
	protected function getResource($id) {
		$id = strtolower($id);
		if ($this->isResourceListed($id)) {
			return $this->cacheList[$id];
		}
	}
	protected function removeResource($id) {
		$id = strtolower($id);
		if ($this->isResourceListed($id)) {
			unset($this->cacheList[$id]);
		}
	}
	protected function isResourceListed($id) {
		$id = strtolower($id);
		return (isset($this->cacheList[$id]) && !empty($this->cacheList[$id]));
	}
	
	
	/*  ======================   BANZILLA DERIVED RESOURCE   =====================  */
	/*  ======================        PUBLIC FUNCTIONS       =====================  */
	
	public function validateCards($numCard) {
		BanzillaConsole::trace('BanzillaAPIDerivedResource @validateCards');
		
		$validate = parent::_validateCards($numCard);
		
		return $validate;
	}

	public function validateParams($params, $method) {
		BanzillaConsole::trace('BanzillaAPIDerivedResource @validateParams');
		
		$validate = parent::_validateParams($params, $method);
		
		return $validate;
	}
	
	public function add($params) {
		BanzillaConsole::trace('BanzillaAPIDerivedResource @add');
		
		$resource = parent::_create($this->resourceName, $params, array('parent' => $this));
		$this->addResource($resource);
		return $resource;
	}
	public function delete($params) {
		BanzillaConsole::trace('BanzillaAPIDerivedResource @delete');
		
		$resource = parent::_delete($this->resourceName, $params, array('parent' => $this));
		$this->addResource($resource);
		return $resource;
	}
	public function put($params) {
		BanzillaConsole::trace('BanzillaAPIDerivedResource @delete');
		
		$resource = parent::_put($this->resourceName, $params, array('parent' => $this));
		$this->addResource($resource);
		return $resource;
	}
	public function get($id) {
		BanzillaConsole::trace('BanzillaAPIDerivedResource @get');

		if ($this->isResourceListed($id)) {	
			return $this->getResource($id);
		}
		$resource = parent::_retrieve($this->resourceName, $id, array('parent' => $this));
		$this->addResource($resource);
		return $resource;
	}
	public function getList($params=null) {
		if($params == null){
			$params=array();
		}
		BanzillaConsole::trace('BanzillaAPIDerivedResource @find');

		$list = parent::_find($this->resourceName, $params, array('parent' => $this));
		
		foreach ($list as $resource) {
			$this->addResource($resource);
		}
		return $list;
	}
}
?>