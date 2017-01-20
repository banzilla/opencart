<?php 

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class BanzillaSubscription extends BanzillaAPIResourceBase {
	protected $status;
	protected $charge_date;
	protected $creation_date;
	protected $current_period_number;
	protected $period_end_date;
	protected $plan_id;
	protected $customer_id;

	protected $card;

	
}
// ----------------------------------------------------------------------------
class BanzillaSubscriptionList extends BanzillaAPIDerivedResource {
	public function create($params) {
		return $this->add($params);
	}
	public function cancel($params) {
        return $this->put($params);
    }
}
?>