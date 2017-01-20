<?php 

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class BanzillaPlans extends BanzillaAPIResourceBase {
	protected $creation_date;
	protected $currency;
	protected $amount;
	protected $repeat_every;
	protected $repeat_unit;
	protected $retry_times;
	protected $status;
	protected $status_after_retry;

	protected $derivedResources = array('Subscription' => array());

	public function save() {
		return $this->_update();
	}
}
// ----------------------------------------------------------------------------
class BanzillaPlansList extends BanzillaAPIDerivedResource {

	public function create($params) {
		return $this->add($params);
	}
	public function deletePlans($params) {
        return $this->delete($params);
    }
	public function cancel($params) {
        return $this->put($params);
    }
}
?>