<?php

if (!function_exists('updateAssetCredentials')) {
    
    function updateAssetCredentials()
    {
        $cryptoAssetSettings = \App\Models\CryptoAssetSetting::where('payment_method_id', BlockIo)->get();
        if (!empty($cryptoAssetSettings)) {
            $blockIoNetworkArray = [];
            foreach ($cryptoAssetSettings as  $cryptoAssetSetting) {
                $networkCredential = json_decode($cryptoAssetSetting->network_credentials);
                $blockIoNetworkArray['api_key'] = $networkCredential->api_key;
                $blockIoNetworkArray['pin'] = $networkCredential->pin;
                $blockIoNetworkArray['address'] = $networkCredential->address;
                $blockIoNetworkArray['account_balance'] = (new \Modules\BlockIo\Classes\BlockIo())->getBlockIoAccountBalance($networkCredential->api_key, $networkCredential->pin)->available_balance;
                $blockIoNetworkArray['merchant_balance'] = (new \Modules\BlockIo\Classes\BlockIo())->getBlockIoMerchantBalance($networkCredential->api_key, $networkCredential->pin, $networkCredential->address)->available_balance;
                $cryptoAssetSetting->network_credentials = json_encode($blockIoNetworkArray);
                $cryptoAssetSetting->save();
            }
        }
    }
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

if (!function_exists('getBlockIoMinLimit')) {
    
    function getBlockIoMinLimit($type = null, $network = null) 
    {
        $minLimit = [
            'amount' => [
                'BTC' => 0.00002,
                'BTCTEST' => 0.00002,
                'DOGE' => 2,
                'DOGETEST' => 2,
                'LTC' => 0.0002,
                'LTCTEST' => 0.0002
            ],
            'networkFee' => [
                'BTC' => 0.0002,
                'BTCTEST' => 0.0002,
                'DOGE' => 1,
                'DOGETEST' => 1,
                'LTC' => 0.0001,
                'LTCTEST' => 0.0001
            ],
        ];
        if (is_null($network) && is_null($network)) {
            return $minLimit;
        }
        return $minLimit[$type][$network];
    }
} 