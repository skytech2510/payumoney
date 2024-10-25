<?php
namespace Infoamin\Installer\Interfaces;
use Infoamin\Installer\Interfaces\CurlRequestInterface;

interface PurchaseInterface {
	function getPurchaseStatus($domainName, $domainIp, $envatopurchasecode, $envatoUsername);
}