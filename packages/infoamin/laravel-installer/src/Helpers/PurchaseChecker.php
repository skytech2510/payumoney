<?php
namespace Infoamin\Installer\Helpers;

use Infoamin\Installer\Interfaces\PurchaseInterface;
use Infoamin\Installer\Interfaces\CurlRequestInterface;
class PurchaseChecker implements PurchaseInterface {

	protected $curlRequest;

    public function __construct(CurlRequestInterface $curlRequest) {
        $this->curlRequest = $curlRequest;
    }

	public function getPurchaseStatus($domainName, $domainIp, $envatopurchasecode, $envatoUsername)
    {
		return ['status' => true, 'data' => 'never', 'old' => true];
    	$data = array(
            'domain_name'        => $domainName,
            'domain_ip'          => $domainIp,
            'envatopurchasecode' => $envatopurchasecode,
            'envatoUsername' => $envatoUsername,
            'item_id' => config('installer.item_id') ?? ''
        );

        return $this->curlRequest->send($data);

    }
}
