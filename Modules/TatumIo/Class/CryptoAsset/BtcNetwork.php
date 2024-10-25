<?php

namespace Modules\TatumIo\Class\CryptoAsset;

use Exception;
use Modules\TatumIo\Interfaces\NetworkInterface;

class BtcNetwork implements NetworkInterface
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
            ->bitcoin()
            ->btcGenerateWallet();
    }

    public function generateAddress($xpub, $index)
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->bitcoin()
            ->btcGenerateAddress($xpub, $index);
    }

    public function generateAddressPrivateKey($index, $mnemonic)
    {
        $argPrivKeyRequest = (new \Tatum\Model\PrivKeyRequest())
            ->setIndex($index)
            ->setMnemonic($mnemonic);

        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->bitcoin()
            ->btcGenerateAddressPrivateKey($argPrivKeyRequest);
    }

    public function getBalanceOfAddress($address)
    {
        try {
            $balance = $this->sdk->{$this->getEnvironment()}()
                ->api()
                ->bitcoin()
                ->btcGetBalanceOfAddress($address);
            return $balance['incoming'] - $balance['outgoing'];
        } catch (\Tatum\Sdk\ApiException $apiExc) {
            throw new Exception(__($apiExc->getResponseObject()['data'][0]));
        }
    }

    public function getTransactionDetails($hash)
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->bitcoin()
            ->btcGetRawTransaction($hash);
    }

    public function getBlockChainInfo()
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->bitcoin()
            ->btcGetBlockChainInfo();
    }

    private function getEnvironment()
    {
        return (!str_contains($this->network, 'TEST')) ? 'mainnet' : 'testnet';
    }
}
