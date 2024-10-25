<?php

namespace Modules\TatumIo\Class;

use Modules\TatumIo\Interfaces\NetworkInterface;

class BitcoinNetwork implements NetworkInterface
{
    protected $sdk;

    public function __construct($apiKey, $environment)
    {

        $sdk = new \Tatum\Sdk($apiKey);
        $this->sdk = $sdk->{$environment}()->api()->bitcoin();

    }

    public function generateWallet()
    {
        return $this->sdk->btcGenerateWallet();

    }


    public function generateAddress($xpub, $index)
    {
        return $this->sdk->btcGenerateAddress($xpub, $index);

    }

    public function generateAddressPrivateKey()
    {
        return $this->sdk->btcGenerateAddressPrivateKey();
    }

}
