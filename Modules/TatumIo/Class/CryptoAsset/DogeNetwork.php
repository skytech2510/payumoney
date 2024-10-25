<?php

namespace Modules\TatumIo\Class\CryptoAsset;

use Exception;
use Illuminate\Support\Facades\Http;
use Modules\TatumIo\Interfaces\NetworkInterface;

class DogeNetwork implements NetworkInterface
{
    protected $sdk;
    protected $network;
    protected $apiKey;

    public function __construct($apiKey, $network)
    {
        $this->sdk = new \Tatum\Sdk($apiKey);
        $this->network = $network;
        $this->apiKey = $apiKey;
    }

    public function generateWallet()
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->dogecoin()
            ->dogeGenerateWallet();
    }

    public function generateAddress($xpub, $index)
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->dogecoin()
            ->dogeGenerateAddress($xpub, $index);
    }

    public function generateAddressPrivateKey($index, $mnemonic)
    {
        $argPrivKeyRequest = (new \Tatum\Model\PrivKeyRequest())
            ->setIndex($index)
            ->setMnemonic($mnemonic);

        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->dogecoin()
            ->dogeGenerateAddressPrivateKey($argPrivKeyRequest);
    }

    public function getTransactionDetails($hash)
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->dogecoin()
            ->dogeGetRawTransaction($hash);
    }



    public function getBalanceOfAddress($address)
    {
        try {
            $url = "https://api.tatum.io/v3/dogecoin/address/balance/" . $address;
            $response = Http::withHeaders([
                "x-api-key" => $this->apiKey,
            ])->get($url);

            if ($response->failed()) {
                $response = json_decode($response);
                throw new Exception($response->data[0]);
            }
            $response = json_decode($response);
            return $response->incoming - $response->outgoing;

        } catch (Exception $e) {
            throw new Exception(__($e->getMessage()));
        }
    }

    public function getBlockChainInfo()
    {
        $response =  $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->dogecoin()
            ->dogeGetBlockChainInfo();
        return $response;
    }

    private function getEnvironment()
    {
        return (!str_contains($this->network, 'TEST')) ? 'mainnet' : 'testnet';
    }
}
