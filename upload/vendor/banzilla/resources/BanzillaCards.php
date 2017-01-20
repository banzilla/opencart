<?php 

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class BanzillaCards extends BanzillaAPIResourceBase
{

    protected $type;
    protected $brand;
    protected $allows_charges;
    protected $allows_payouts;
    protected $creation_date;
    protected $bank_name;
    protected $bank_code;
    protected $customer_id;
    protected $method;
    protected $card;

    public function delete() {
        $this->_delete();
    }

    public function get($param) {
        return $this->_getAttributes($param);
    }

}

// ----------------------------------------------------------------------------
class BanzillaCardsList extends BanzillaAPIDerivedResource
{
    public function createToken($params) {
		return $this->add($params);
	}
}

?>