<?php 
/**
 * Banzilla API v1 for PHP (version 1.0.0)
 * 
 * Copyright © All rights reserved.
 * https://www.banzilla.com/
 * soporte@banzilla.com
 */

if (!function_exists('curl_init')) {
	throw new Exception('CURL PHP extension is required to ejecute Banzilla.');
}
if (!function_exists('json_decode')) {
	throw new Exception('JSON PHP extension is required to ejecute Banzilla.');
}
if (!function_exists('mb_detect_encoding')) {
	throw new Exception('Multibyte String PHP extension is required to ejecute Banzilla.');
}

require(dirname(__FILE__) . '/data/BanzillaAPIError.php');
require(dirname(__FILE__) . '/data/BanzillaAPIConsole.php');
require(dirname(__FILE__) . '/data/BanzillaAPIResourceBase.php');
require(dirname(__FILE__) . '/data/BanzillaAPISocket.php');
require(dirname(__FILE__) . '/data/BanzillaAPIDerivedResource.php');
require(dirname(__FILE__) . '/data/BanzillaAPI.php');

require(dirname(__FILE__) . '/resources/BanzillaCards.php');
require(dirname(__FILE__) . '/resources/BanzillaCharge.php');
require(dirname(__FILE__) . '/resources/BanzillaTransactions.php');
require(dirname(__FILE__) . '/resources/BanzillaPlans.php');
require(dirname(__FILE__) . '/resources/BanzillaRefund.php');
require(dirname(__FILE__) . '/resources/BanzillaSubscription.php');
require(dirname(__FILE__) . '/resources/BanzillaWebhooks.php');
?>