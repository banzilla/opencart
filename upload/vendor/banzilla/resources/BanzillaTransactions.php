<?php 

/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

class BanzillaTransactions extends BanzillaAPIResourceBase {
	protected function getResourceUrlName($p = true){
		return parent::getResourceUrlName(false);
	}
}

class BanzillaTransactionsList extends BanzillaApiDerivedResource
{

}
?>