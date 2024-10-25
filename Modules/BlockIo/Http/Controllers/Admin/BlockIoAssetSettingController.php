<?php

namespace Modules\BlockIo\Http\Controllers\Admin;

use Modules\BlockIo\Http\Requests\{BlockIoSettingStoreRequest,
    BlockIoSettingUpdateRequest
};
use Modules\BlockIo\Classes\BlockIo;
use App\Models\CryptoAssetSetting;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DB, Common, Exception;

class BlockIoAssetSettingController extends Controller
{
    private $blockIo;
    private $helper;
    const NETWORK = ['BTC', 'LTC', 'DOGE', 'BTCTEST', 'LTCTEST', 'DOGETEST'];

    public function create()
    {
        $data['menu'] = 'crypto_providers';
        return view('blockio::admin.network.create', $data);
    }

    public function store(BlockIoSettingStoreRequest $request)
    {
        $this->helper = new Common;

        $validatedResponse = $this->validationRequestData($request);
        if ($validatedResponse['status'] == 400) {
            $this->helper->one_time_message('error', $validatedResponse['message']);
            return redirect()->route('admin.blockio_asset.create')->withInput();
        }

        try {
            DB::beginTransaction();

            $currency = new \App\Models\Currency();
            $currency->type = 'crypto_asset';
            $currency->name = $request->name;
            $currency->symbol = $request->symbol;
            $currency->code = $request->network;

            $currency->status  = $request->status == 'Active' ? 'Active' : 'Inactive';

            if ($request->hasFile('logo')) {
                $networkLogo = $request->file('logo');
                if (isset($networkLogo)) {
                    $response = uploadImage($networkLogo, 'public/uploads/currency_logos/', '64*64');
                    if ($response['status'] === true) {
                        $currency->logo = $response['file_name'];
                    }
                }
            }

            if ($currency->save()) {

                if ($currency->type == 'crypto_asset') {

                    $this->blockIo = new BlockIo;

                    $accountInfo = $this->blockIo->getAccountInfo($request->api_key, $request->pin);
                    $blockIoprovider = \App\Models\CryptoProvider::where('name', 'BlockIo')->orWhere('alias')->first();
                    $accountInfoArr['current_plan'] = ucfirst($accountInfo->current_plan);
                    $accountInfoArr['maximum_daily_api_requests'] = $accountInfo->maximum_daily_api_requests;
                    $accountInfoArr['maximum_wallet_addresses_per_network'] = $accountInfo->maximum_wallet_addresses_per_network;
                    $accountInfoArr['api_access_allowed_for_networks'] = json_encode($accountInfo->api_access_allowed_for_networks);
                    $blockIoprovider->subscription_details = json_encode($accountInfoArr);
                    $blockIoprovider->save();


                    $getBlockIoAssetSettings = $this->blockIo->getBlockIoAssetSetting($request->network, 'All', ['*']);
                    $blockIoAssetSettings = !empty($getBlockIoAssetSettings) ? $getBlockIoAssetSettings : new CryptoAssetSetting();
                    $blockIoAssetSettings->payment_method_id = BlockIo;
                    $blockIoAssetSettings->currency_id = $currency->id;
                    $blockIoAssetSettings->crypto_provider_id = $blockIoprovider->id;
                    $blockIoAssetSettings->network = $currency->code;

                    $blockIoNetworkArray = [];
                    $blockIoNetworkArray['api_key'] = $request->api_key;
                    $blockIoNetworkArray['pin'] = $request->pin;
                    $blockIoNetworkArray['address'] = $request->address;
                    $blockIoNetworkArray['account_balance'] = $this->blockIo->getBlockIoAccountBalance($request->api_key, $request->pin)->available_balance;
                    $blockIoNetworkArray['merchant_balance'] = $this->blockIo->getBlockIoMerchantBalance($request->api_key, $request->pin, $request->address)->available_balance;

                    $blockIoAssetSettings->network_credentials = json_encode($blockIoNetworkArray);
                    $blockIoAssetSettings->status = $currency->status;

                    // Create blockIo account notification for webhook response
                    if ($blockIoAssetSettings->save()) {
                        $createNotification = $this->blockIo->createNotification($request->network);
                    }
                }
            }

            // Create Users Wallet Addresses
            $createUsersNetworkAddressError = false;
            if ($request->type == 'crypto_asset' && isset($request->network_address) && $request->network_address == 'on') {

                $createUsersNetworkAddresses = $this->createUsersNetworkAddress($currency->code, $currency->id);
                foreach ($createUsersNetworkAddresses as $createUsersNetworkAddress) {
                    if ($createUsersNetworkAddress['status'] == 401) {
                        $createUsersNetworkAddressError = true;
                    }
                }
            }
            DB::commit();

            if ($createUsersNetworkAddressError == false) {
                $this->helper->one_time_message('success', __('Asset added successfully.'));
            } else {
                $this->helper->one_time_message('error', $createUsersNetworkAddress['message']);
            }
            return redirect()->route('admin.crypto_providers.list', 'BlockIo');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.crypto_providers.list', 'BlockIo');
        }
    }

    public function edit($network)
    {
        $data['menu'] = 'crypto_providers';
        $network = decrypt($network);

        $data['cryptoAssetSetting'] = CryptoAssetSetting::with(['currency' => function($query) {
            $query->where('type', 'crypto_asset');
        }])
        ->where(['network' => $network, 'payment_method_id' => BlockIo])
        ->first();

        if (!empty($data['cryptoAssetSetting']) && !empty($data['cryptoAssetSetting']->currency)) {
            return view('blockio::admin.network.edit', $data);
        } else {
            (new common)->one_time_message('error', __('Asset settings not found'));
            return redirect()->route('admin.crypto_providers.list');
        }
    }

    public function update(BlockIoSettingUpdateRequest $request, $network)
    {
        $validatedResponse = $this->validationRequestData($request);
        if ($validatedResponse['status'] == 400) {
            (new common)->one_time_message('error', $validatedResponse['message']);
            return redirect()->route('admin.blockio_asset.edit', $network);
        }

        $network = decrypt($network);
        $data['cryptoAssetSetting'] = CryptoAssetSetting::with(['currency' => function($query) {
            $query->where('type', 'crypto_asset');
        }])
        ->where(['network' => $network, 'payment_method_id' => BlockIo])
        ->first();

        if (!empty($data['cryptoAssetSetting']) && !empty($data['cryptoAssetSetting']->currency)) {

            try {
                DB::beginTransaction();

                $currency = \App\Models\Currency::where(['code' => $network, 'type' => 'crypto_asset'])->first();

                if (!empty($currency)) {

                    $currency->name = $request->name;
                    $currency->type = 'crypto_asset';
                    $currency->symbol = $request->symbol;
                    $currency->code = $request->network;
                    $currency->status  = $request->status == 'Active' ? 'Active' : 'Inactive';

                    // Logo upload
                    if ($request->hasFile('logo')) {
                        $networkLogo = $request->file('logo');
                        if (isset($networkLogo)) {
                            $response = uploadImage($networkLogo, 'public/uploads/currency_logos/', '64*64', $currency->logo);
                            if ($response['status'] === true) {
                                $currency->logo = $response['file_name'];
                            }
                        }
                    }

                    if ($currency->save()) {

                        $this->blockIo = new BlockIo;

                        $accountInfo = $this->blockIo->getAccountInfo($request->api_key, $request->pin);
                        $blockIoprovider = \App\Models\CryptoProvider::where('name', 'BlockIo')->orWhere('alias')->first();
                        $accountInfoArr['current_plan'] = ucfirst($accountInfo->current_plan);
                        $accountInfoArr['maximum_daily_api_requests'] = $accountInfo->maximum_daily_api_requests;
                        $accountInfoArr['maximum_wallet_addresses_per_network'] = $accountInfo->maximum_wallet_addresses_per_network;
                        $accountInfoArr['api_access_allowed_for_networks'] = json_encode($accountInfo->api_access_allowed_for_networks);
                        $blockIoprovider->subscription_details = json_encode($accountInfoArr);
                        $blockIoprovider->save();


                        $getBlockIoAssetSettings = $this->blockIo->getBlockIoAssetSetting($request->network, 'All', ['*']);
                        $blockIoAssetSettings = !empty($getBlockIoAssetSettings) ? $getBlockIoAssetSettings : new CryptoAssetSetting();
                        $blockIoAssetSettings->payment_method_id = BlockIo;
                        $blockIoAssetSettings->currency_id = $currency->id;
                        $blockIoAssetSettings->crypto_provider_id = $blockIoprovider->id;
                        $blockIoAssetSettings->network = $currency->code;

                        $blockIoNetworkArray = [];
                        $blockIoNetworkArray['api_key'] = $request->api_key;
                        $blockIoNetworkArray['pin'] = $request->pin;
                        $blockIoNetworkArray['address'] = $request->address;
                        $blockIoNetworkArray['account_balance'] = $this->blockIo->getBlockIoAccountBalance($request->api_key, $request->pin)->available_balance;
                        $blockIoNetworkArray['merchant_balance'] = $this->blockIo->getBlockIoMerchantBalance($request->api_key, $request->pin, $request->address)->available_balance;

                        $blockIoAssetSettings->network_credentials = json_encode($blockIoNetworkArray);
                        $blockIoAssetSettings->status = $currency->status;

                        // Create blockIo account notification for webhook response
                        if ($blockIoAssetSettings->save()) {
                            $createNotification = $this->blockIo->createNotification($request->network);
                        }
                    }
                } else {
                    (new common)->one_time_message('error', __('Asset settings not found'));
                    return redirect()->route('admin.crypto_providers.list', 'BlockIo');
                }

                $createUsersNetworkAddressError = false;
                if ($currency->type == 'crypto_asset' && isset($request->network_address) && $request->network_address == 'on') {
                    $createUsersNetworkAddresses = $this->createUsersNetworkAddress($currency->code, $currency->id);
                    foreach ($createUsersNetworkAddresses as $createUsersNetworkAddress) {
                        if ($createUsersNetworkAddress['status'] == 401) {
                            $createUsersNetworkAddressError = true;
                        }
                    }
                }

                if ($createUsersNetworkAddressError == false) {
                    (new Common)->one_time_message('success', __('Asset updated successfully.'));
                } else {
                    (new Common)->one_time_message('error', $createUsersNetworkAddress['message']);
                }

                DB::commit();

                return redirect()->route('admin.crypto_providers.list', 'BlockIo');

            } catch (Exception $e) {
                DB::rollBack();
                (new common)->one_time_message('error', $e->getMessage());
                return redirect()->route('admin.crypto_providers.list', 'BlockIo');
            }

        } else {
            (new common)->one_time_message('error', __('Asset settings not found'));
            return redirect()->route('admin.crypto_providers.list', 'BlockIo');
        }
    }

    private function validationRequestData(Request $request)
    {
        // Supported networks by BlockIo
        if (!in_array($request->network, BlockIoAssetSettingController::NETWORK)) {
            return [
                'status' => 400,
                'message' => __(':x network is not supported by BlockIo.', ['x' => $request->network])
            ];
        }

        // Merchant address validation (apikey, pin, address, network)
        $checkMerchantNetworkAddress = $this->checkMerchantNetworkAddress($request)->getData();
        if ($checkMerchantNetworkAddress->status == 400) {
            return [
                'status' => 400,
                'message' =>  $checkMerchantNetworkAddress->message
            ];
        }

        // Request code DOGETEST but apiKey,address,Pin response network is another network like LTCTEST
        if ($checkMerchantNetworkAddress->status == 200 && $request->network != $checkMerchantNetworkAddress->network) {
            return [
                'status' => 400,
                'message' => __('Merchant address does not belong to this network.')
            ];
        }
        return ['status' => 200];
    }

    public function checkDuplicateNetwork()
    {
        $network = CryptoAssetSetting::where('network', request()->network)->exists();

        if ($network) {
            return response()->json([
                'status'  => 400,
                'message' => __('Network already exist for crypto assets')
            ]);
        }
        return response()->json([
            'status' => 200
        ]);
    }

    public function checkMerchantNetworkAddress(Request $request)
    {
        $this->blockIo = new BlockIo;

        try {
            $checkMerchantNetworkAddress = $this->blockIo->checkMerchantNetworkAddressValidity($request->api_key, $request->pin, $request->address);

            if (!$checkMerchantNetworkAddress['status']) {
                return response()->json([
                    'status'  => 400,
                    'message' => __('Invalid merchant address'),
                ]);
            }
            return response()->json([
                'status'  => 200,
                'network' => $checkMerchantNetworkAddress['network'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 400,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function assetStatusChange(Request $request)
    {
        $network = decrypt($request->network);

        $cryptoAssetSetting = CryptoAssetSetting::with(['currency' => function($query) {
            $query->where('type', 'crypto_asset');
        }])
        ->where(['network' => $network, 'payment_method_id' => BlockIo])
        ->first();

        try {
            DB::beginTransaction();
            $cryptoAssetSetting->update(['status' => $request->network_status]);
            $cryptoAssetSetting->currency->update(['status' => $request->network_status]);
            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => __(':x has been :y successfully.', ['x' => $network, 'y' => $request->network_status == 'Active' ? __('Activated') : __('Deactivated')]),
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function validateAddress(Request $request)
    {
        $this->blockIo = new BlockIo();
        $validateAddress = $this->blockIo->checkAddressValidity($request->network, $request->address);
        return $validateAddress;
    }

    public function currentParentList($network)
    {
        $this->blockIo = new BlockIo();

        try {
            $data['getCurrentPrices'] = $this->blockIo->getCurrentPricesList($network)->data->prices;
            return view('blockio::admin.network.current_price_list', $data);
        } catch (Exception $e) {
            (new Common)->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.crypto_providers.list');
        }
    }

    protected function createUsersNetworkAddress($network, $currencyId)
    {
        $users = \App\Models\User::with(['wallets' => function ($q) use ($currencyId)
        {
            $q->where(['currency_id' => $currencyId]);
        }])
        ->where(['status' => 'Active'])
        ->get(['id', 'email']);

        $getCryptoApiLogOfWallets = [];
        if (!empty($users)) {
            foreach ($users as $user) {
                $getWalletObject = (new Common)->getUserWallet([], ['user_id' => $user->id, 'currency_id' => $currencyId], ['id']);
                if (empty($getWalletObject) && count($user->wallets) == 0) {
                    // Create new wallet of current currency
                    $wallet              = new \App\Models\Wallet();
                    $wallet->user_id     = $user->id;
                    $wallet->currency_id = $currencyId;
                    $wallet->is_default  = 'No';
                    $wallet->save();

                    // Get Crypto Api Logs of current wallet id
                    $getCryptoApiLogOfWallets[] = (new BlockIo)->getBlockIoAssetsApiLogOfWallet($wallet->id, $network, $user);
                }
            }
            return $getCryptoApiLogOfWallets;
        }
    }
}
