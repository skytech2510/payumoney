<?php

namespace Modules\TatumIo\Class\CryptoAsset;

use Exception;
use Modules\TatumIo\Interfaces\NetworkInterface;

class LtcNetwork implements NetworkInterface
{
    protected $sdk;
    protected $network;

    public function __construct($apiKey, $network)
    {
        $this->sdk = new \Tatum\Sdk($apiKey);
        $this->network = $network;
    }

    public function generateWallet()
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->litecoin()
            ->ltcGenerateWallet();
    }

    public function generateAddress($xpub, $index)
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->litecoin()
            ->ltcGenerateAddress($xpub, $index);
    }

    public function generateAddressPrivateKey($index, $mnemonic)
    {
        $argPrivKeyRequest = (new \Tatum\Model\PrivKeyRequest())
            ->setIndex($index)
            ->setMnemonic($mnemonic);

        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->litecoin()
            ->ltcGenerateAddressPrivateKey($argPrivKeyRequest);
    }

    public function getBalanceOfAddress($address)
    {
        try {
            $balance = $this->sdk->{$this->getEnvironment()}()
                ->api()
                ->litecoin()
                ->ltcGetBalanceOfAddress($address);

            return $balance['incoming'] - $balance['outgoing'];
        } catch (\Tatum\Sdk\ApiException $apiExc) {
            throw new Exception(__($apiExc->getResponseObject()['data'][0]));
        }
    }

    public function getBlockChainInfo()
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->litecoin()
            ->ltcGetBlockChainInfo();
    }

    private function getEnvironment()
    {
        return (!str_contains($this->network, 'TEST')) ? 'mainnet' : 'testnet';
    }
}
