<?php

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright © All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class BanzillaCharge extends BanzillaAPIResourceBase
{

    protected $authorization;
    protected $creation_date;
    protected $currency;
    protected $customer_id;
    protected $operation_type;
    protected $status;
    protected $transaction_type;
    
    protected $card;
    protected $derivedResources = array('Refund' => null, 'Capture' => null);

    public function refund($params) {
        $resource = $this->derivedResources['refunds'];
        if ($resource) {
            return parent::_create($resource->resourceName, $params, array('parent' => $this));
        }
    }

    public function capture($params) {
        $resource = $this->derivedResources['captures'];
        if ($resource) {
            return parent::_create($resource->resourceName, $params, array('parent' => $this));
        }
    }

    public function update($params) {
        return $this->_updateCharge($params);
    }

}

// ----------------------------------------------------------------------------
class BanzillaChargeList extends BanzillaApiDerivedResource
{

    public function createCard($params) {
    
        $this->validateParams($params, 'card');

        $cardNum = $params['Card']['CardNumber'];
        $type = $this->validateCards($cardNum);

        $params['Method'] = 'card';
        $params['Gateway'] = $type['gateway'];
        $params['Description'] = 'Cargo con tarjeta';

        if(!empty($type)){
            return $this->add($params);
        }

    }
    public function createOxxo($params) {

        $this->validateParams($params, 'oxxo');

        $params['Method'] = 'store';
        $params['Gateway'] = 'oxxo';
        $params['Description'] = 'Pago OXXO';

        return $this->add($params);
    }

    public function createSpei($params) {

        $this->validateParams($params, 'spei');

        $params['Method'] = 'transfer';
        $params['Gateway'] = 'spei';
        $params['Description'] = 'SPEI 125800';

        return $this->add($params);
    }

    public function createToken($params) {

        $params['Method'] = 'token';
        $params['Description'] = 'Cargo con token';

        return $this->add($params);
    }

}

?>
