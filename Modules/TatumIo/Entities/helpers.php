<?php

use App\Models\CryptoAssetApiLog;
use Modules\TatumIo\Class\TatumIoTransaction;

if (!function_exists('updateTatumAssetCredentials')) {

    function updateTatumAssetCredentials()
    {
        $cryptoAssetSettings = \App\Models\CryptoAssetSetting::where('payment_method_id', TatumIo)->get();
        if (!empty($cryptoAssetSettings)) {
            $tatumIoNetworkArray = [];
            foreach ($cryptoAssetSettings as  $cryptoAssetSetting) {
                $network = $cryptoAssetSetting->network;
                $tatumIo = new TatumIoTransaction($network) ?? 0;
                $tatumIo->tatumIoAsset();
                $balance = $tatumIo->getMerchantBalance();
                $networkCredential = json_decode($cryptoAssetSetting->network_credentials);

                $tatumIoNetworkArray['api_key'] = $networkCredential->api_key;
                $tatumIoNetworkArray['coin'] = $networkCredential->coin;
                $tatumIoNetworkArray['mnemonic'] = $networkCredential->mnemonic;
                $tatumIoNetworkArray['xpub'] = $networkCredential->xpub;
                $tatumIoNetworkArray['key'] = $networkCredential->key;
                $tatumIoNetworkArray['address'] = $networkCredential->address;
                $tatumIoNetworkArray['balance'] = $balance;
                $cryptoAssetSetting->network_credentials = json_encode($tatumIoNetworkArray);
                $cryptoAssetSetting->save();
            }
        }
    }
}

function getReceiverAddressWalletUserId($receiverAddress)
{
    return  (new CryptoAssetApiLog())
    ->with(['wallet:id,user_id'])
    ->where(['payment_method_id' => TatumIo, 'object_type' => 'wallet_address'])
    ->whereJsonContains('payload->address', $receiverAddress )
    ->first('object_id');

}

if (!function_exists('getProviderActiveStatus')) {

    function getProviderActiveStatus($providers)
    {
        $activeCryptoProviders = [];

        if (isset($providers)) {
            foreach ($providers as $cryptoProvider) {
                if (isActive($cryptoProvider->alias)) {
                    $activeCryptoProviders[$cryptoProvider->alias] = true;
                }
            }
        }
        return $activeCryptoProviders;
    }
}

if (!function_exists('getTatumIoMinLimit')) {

    function getTatumIoMinLimit($type = null, $network = null)
    {
        $minLimit = [
            'amount' => [
                'BTC' => 0.00002,
                'BTCTEST' => 0.00002,
                'DOGE' => 2,
                'DOGETEST' => 2,
                'LTC' => 0.0002,
                'LTCTEST' => 0.0002,
                'ETH' => 0.000002,
                'ETHTEST' => 0.000002,
                'TRX' => 1,
                'TRXTEST' => 1,
            ],
            'networkFee' => [
                'BTC' => 0.0002,
                'BTCTEST' => 0.0002,
                'DOGE' => 1,
                'DOGETEST' => 1,
                'LTC' => 0.0001,
                'LTCTEST' => 0.0001,
                'ETH' => 0.0002,
                'ETHTEST' => 0.0002,
                'TRX' => 1,
                'TRXTEST' => 1,
            ],
        ];
        if (is_null($network) && is_null($network)) {
            return $minLimit;
        }
        return $minLimit[$type][$network];
    }
}

function tatumGetCryptoTransactionApiLog($txId)
{
    return (new CryptoAssetApiLog())
        ->with(['transaction:id'])
        ->where(['payment_method_id' => TatumIo, 'object_type' => 'crypto_sent'])
        ->whereJsonContains('payload->txId', $txId)
        ->first();
}

function tatumGetWalletApiLog($address)
{
    return (new CryptoAssetApiLog())
        ->with(['wallet:id,user_id,currency_id'])
        ->where(['payment_method_id' => TatumIo, 'object_type' => 'wallet_address'])
        ->whereJsonContains('payload->address', $address)
        ->first();
}
