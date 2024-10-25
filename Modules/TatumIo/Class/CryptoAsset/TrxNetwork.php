<?php

namespace Modules\TatumIo\Class\CryptoAsset;

use Exception;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Modules\TatumIo\Interfaces\NetworkInterface;

class TrxNetwork implements NetworkInterface
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
            ->tron()
            ->generateTronwallet();
    }

    public function generateAddress($xpub, $index)
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->tron()
            ->tronGenerateAddress($xpub, $index);
    }

    public function generateAddressPrivateKey($index, $mnemonic)
    {
        $argPrivKeyRequest = (new \Tatum\Model\PrivKeyRequest())
            ->setIndex($index)
            ->setMnemonic($mnemonic);

        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->tron()
            ->tronGenerateAddressPrivateKey($argPrivKeyRequest);
    }

    public function getBalanceOfAddress($address)
    {

        try {
            $url = "https://api.tatum.io/v3/tron/account/" . $address;

            $response = Http::withHeaders([
                "x-api-key" => $this->apiKey,
            ])->get($url);

            if ($response->failed()) {
                $response = json_decode($response);
                if (isset($response->data[0])) {
                    throw new Exception($response->data[0]);
                }
                return 0;
            }
            $response = json_decode($response);

            return $response->balance * 0.000001;
        } catch (Exception $e) {

            if (Route::current()->uri() == 'crypto/send/tatumio/validate-address') {
                throw new Exception($e->getMessage());
            }

            return 0;

        }
    }

    public function getTransactionDetails($hash)
    {
        $response =  $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->tron()
            ->tronGetTransaction($hash);

        return [
            'senderAddress' => $response['raw_data']['contract'][0]['parameter']['value']['ownerAddressBase58'],
            'receiverAddress' => $response['raw_data']['contract'][0]['parameter']['value']['toAddressBase58'],
            'networkFee' =>  $response['fee']  * 0.000001
        ];


    }

    public function getBlockChainInfo()
    {
        return $this->sdk->{$this->getEnvironment()}()
            ->api()
            ->tron()
            ->tronGetBlockChainInfo();
    }

    public function getAccount($address)
    {
        try {
            return $this->sdk->{$this->getEnvironment()}()
                ->api()
                ->tron()
                ->tronGetAccount($address);
        } catch (\Tatum\Sdk\ApiException $apiExc) {
            throw new Exception(__($apiExc->getResponseObject()['message']));
        } catch (\Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }

    public function getEstimateGasFees($from, $to, $amount)
    {
        return 0;
    }

    public function createTransaction($sender, $key, $receiver, $amount, $priority)
    {
        $payload = [
            "fromPrivateKey" => $key,
            "to" => $receiver,
            "amount" => (string)$amount,
        ];

        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "x-api-key" => $this->apiKey,
        ])->post("https://api.tatum.io/v3/tron/transaction", $payload);

        return json_decode($response);
    }

    private function getEnvironment()
    {
        return (!str_contains($this->network, 'TEST')) ? 'mainnet' : 'testnet';
    }
}
