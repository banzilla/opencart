<?php

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class BanzillaWebhooks extends BanzillaAPIResourceBase
{

    protected $url;
    protected $event_types;
    protected $error;


}
class BanzillaWebhooksList extends BanzillaApiDerivedResource
{
	public function create($params) {
		return $this->add($params);
	}
	public function deleteWebhook($params) {
        return $this->delete($params);
    }
}

?>