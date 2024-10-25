<?php

namespace Modules\TatumIo\Class\CryptoAsset;

use Exception;
use Illuminate\Support\Facades\Http;
use Modules\TatumIo\Interfaces\NetworkInterface;

class EthNetwork implements NetworkInterface
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
            ->ethereum()
            ->ethGenerateWallet(null, $this->ethereumChainType());
    }

    public function generateAddress($xpub, $index)
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->ethereum()
            ->ethGenerateAddress($xpub, $index, $this->ethereumChainType());
    }

    public function generateAddressPrivateKey($index, $mnemonic)
    {
        $argPrivKeyRequest = (new \Tatum\Model\PrivKeyRequest())
            ->setIndex($index)
            ->setMnemonic($mnemonic);

        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->ethereum()
            ->ethGenerateAddressPrivateKey($argPrivKeyRequest, $this->ethereumChainType());
    }

    public function getBalanceOfAddress($address)
    {
        try {
            $balance =  $this->sdk->{$this->getEnvironment()}()
                ->api()
                ->ethereum()
                ->ethGetBalance($address, $this->ethereumChainType());

            return $balance['balance'];
        } catch (\Tatum\Sdk\ApiException $apiExc) {
            throw new Exception(__($apiExc->getResponseObject()['data'][0]));
        }
    }

    public function getTransactionDetails($hash)
    {
        $url = "https://api.tatum.io/v3/ethereum/transaction/" . $hash;

        $response = Http::withHeaders([
            'x-api-key' =>  $this->apiKey,
        ])->get($url);

        if ($response->failed()) {
             return [
                'senderAddress' => '',
                'receiverAddress' => '',
                'networkFee' =>  ''
            ];
        } else {
            $response = json_decode($response);

            $fees =  ($response->gas * $response->gasPrice) / 1e18;

            return [
                'senderAddress' => $response->from,
                'receiverAddress' =>  $response->to,
                'networkFee' =>  $fees
            ];

        }
    }



    public function getBlockChainInfo()
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->ethereum()
            ->ethGetBlockChainInfo();
    }

    public function ethereumChainType()
    {
        return ($this->getEnvironment() == 'mainnet') ? 'ethereum-mainnet' : 'ethereum-sepolia';
    }

    public function getEstimateGasFees($from, $to, $amount, $prior)
    {
        $payload = [
            "from" => $from,
            "to" => $to,
            "amount" => $amount
        ];

        $priority = [
            'slow' => 'safe',
            'medium' => 'standard',
            'fast' => 'fast'
        ][$prior];

        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "x-api-key" =>  $this->apiKey
        ])
            ->post("https://api.tatum.io/v3/ethereum/gas", $payload);

        $data = json_decode($response, true);

        return ($data['gasLimit'] * $data['estimations'][$priority]) / 1e18;

    }

    public function createTransaction($sender, $key, $receiver, $amount, $priority)
    {
        $payload = [
            "to" => $receiver,
            "amount" => $amount,
            "currency" => "ETH",
            "fromPrivateKey" => $key
        ];

        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "x-api-key" => $this->apiKey
        ])
            ->post("https://api.tatum.io/v3/ethereum/transaction", $payload);

        return json_decode($response);
    }

    private function getEnvironment()
    {
        return (!str_contains($this->network, 'TEST')) ? 'mainnet' : 'testnet';
    }
}
