<?php

namespace Modules\TatumIo\Http\Controllers\Admin;

use App\Http\Helpers\Common;

use App\Models\{CryptoAssetApiLog,
    CryptoAssetSetting
};
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

use Modules\TatumIo\Class\{CryptoNetwork,
    TatumIoTransaction
};
use Modules\TatumIo\Http\Requests\TatumAssetStoreRequest;

class TatumIoAssetSettingController extends Controller
{
    protected $network;
    protected $cryptoNetwork;
    protected $xpub;
    protected $mnemonic;

    const NETWORK = ['BTC', 'LTC', 'DOGE', 'ETH', 'TRX', 'BTCTEST', 'LTCTEST', 'DOGETEST', 'ETHTEST', 'TRXTEST'];

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $data['menu'] = 'crypto_providers';
        return view('tatumio::admin.network.create', $data);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(TatumAssetStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $this->validationRequestData($request);

            $currency = $this->storeOrUpdateCurrency($request);

            $this->createTatumAssetSettings($request, $currency);

            DB::commit();

            (new Common())->one_time_message('success', __('Asset added successfully.'));
            return redirect()->route('admin.crypto_providers.list', 'TatumIo');

        } catch (Exception $e) {

            DB::rollBack();
            (new Common())->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.tatumio_asset.create')->withInput();
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('tatumio::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($network)
    {
        $data['menu'] = 'crypto_providers';
        $network = decrypt($network);

        $data['cryptoAssetSetting'] = CryptoAssetSetting::with(['currency' => function($query) {
            $query->where('type', 'crypto_asset');
        }])
        ->where(['network' => $network, 'payment_method_id' => TatumIo])
        ->first();

        if (!empty($data['cryptoAssetSetting']) && !empty($data['cryptoAssetSetting']->currency)) {
            return view('tatumio::admin.network.edit', $data);
        } else {
            (new common)->one_time_message('error', __('Asset settings not found'));
            return redirect()->route('admin.crypto_providers.list');
        }
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $network
     * @return Renderable
     */
    public function update(Request $request, $coin)
    {

        try {
            $this->validationRequestData($request);

            DB::beginTransaction();
            $currency = $this->storeOrUpdateCurrency($request, 'update');
            $this->updateTatumAssetSettings($request, $currency);
            DB::commit();

            (new Common())->one_time_message('success', __('Crypto Asset Updated successfully.'));
            return redirect()->route('admin.crypto_providers.list', 'TatumIo');

        } catch (Exception $e) {
            (new Common())->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.tatumio_asset.edit', $coin)->withInput();
        }

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    private function validationRequestData(Request $request)
    {
        if (!in_array($request->network, TatumIoAssetSettingController::NETWORK)) {
            throw new Exception(__(':x network is not supported by TatumIo.', ['x' => $request->network]));
        }
    }

    public function storeOrUpdateCurrency($request, $action = 'create')
    {
        if ($action == 'update') {
            $currency = \App\Models\Currency::where(['code' => $request->network, 'type' => 'crypto_asset'])->first();

            if (is_null($currency)) {
                throw new Exception(__('Crypto Network not available'));
            }

        }

        if($action == 'create') {
            $currency =  new \App\Models\Currency();
        }

        $currency->type = 'crypto_asset';
        $currency->name = $request->name;
        $currency->symbol = $request->symbol;
        $currency->code = $request->network;

        if ($request->hasFile('logo')) {
            $networkLogo = $request->file('logo');
            if (isset($networkLogo)) {
                $response = uploadImage($networkLogo, 'public/uploads/currency_logos/', '64*64');
                if ($response['status'] === true) {
                    $currency->logo = $response['file_name'];
                }
            }
        }

        $currency->status  = ($request->status == 'Active') ? 'Active' : 'Inactive';
        $currency->save();

        return $currency;

    }


    public function createTatumAssetSettings($request, $currency)
    {
        $this->cryptoNetwork = new CryptoNetwork($request->api_key, $request->network);

        if (!is_null($this->cryptoNetwork->getTatumAssetSetting()) ) {
            throw new Exception(__(':x Network is already available on TatumIo provider', ['x' => $request->network]));
        }

        if ($this->cryptoNetwork->networkName() !== strtolower($request->name)) {
            throw new Exception(__(':x is not appropriate Coin name for  :y Network', ['x' => $request->name, 'y' => $request->network]));
        }

        $index = 0;
        $cryptoWallet  = $this->cryptoNetwork->generateWallet();

        $this->xpub = $cryptoWallet['xpub'];
        $this->mnemonic = $cryptoWallet['mnemonic'];

        $tatumAddress = $this->cryptoNetwork->generateAddress($this->xpub , $index);
        $tatumKey = $this->cryptoNetwork->generateAddressPrivateKey($index,  $this->mnemonic);
        $tatumBalance = ( $this->cryptoNetwork->networkName()  ==  'tron' ) ? 0 : $this->cryptoNetwork->getBalanceOfAddress($tatumAddress['address']);

        $tatumAssetSettings = new CryptoAssetSetting();
        $tatumAssetSettings->payment_method_id = TatumIo;
        $tatumAssetSettings->currency_id = $currency->id;
        $tatumAssetSettings->crypto_provider_id = 2;
        $tatumAssetSettings->network = $request->network;

        $tatumNetworkArray = [];
        $tatumNetworkArray['api_key'] = $request->api_key;
        $tatumNetworkArray['coin'] =  $request->name;
        $tatumNetworkArray['mnemonic'] = isset($this->mnemonic) ? $this->mnemonic : '';
        $tatumNetworkArray['address'] = isset($tatumAddress['address']) ? $tatumAddress['address'] : '';
        $tatumNetworkArray['xpub'] = isset( $this->xpub) ?  $this->xpub : '';
        $tatumNetworkArray['key'] = isset($tatumKey['key']) ? $tatumKey['key'] : '';
        $tatumNetworkArray['balance'] = $tatumBalance;
        $tatumAssetSettings->network_credentials = json_encode($tatumNetworkArray);
        $tatumAssetSettings->status = $currency->status;
        $tatumAssetSettings->save();

        if (isset($request->create_address) && $request->create_address == 'on') {
            $this->createUsersNetworkAddress($currency->code, $currency->id);
        }

    }

    public function updateTatumAssetSettings($request, $currency)
    {

        $this->cryptoNetwork = new CryptoNetwork($request->api_key, $request->network);

        $cryptoAssetSetting = $this->cryptoNetwork->getTatumAssetSetting('All');

        $credentials = json_decode($cryptoAssetSetting->network_credentials);

        $tatumBalance =  $this->cryptoNetwork->getBalanceOfAddress($credentials->address);

        $tatumArray = [];
        $tatumArray['api_key'] = $credentials->api_key;
        $tatumArray['coin'] = $credentials->coin;
        $tatumArray['mnemonic'] = $credentials->mnemonic;
        $tatumArray['address'] = $credentials->address;
        $tatumArray['xpub'] = $credentials->xpub;
        $tatumArray['key'] = $credentials->key;
        $tatumArray['balance'] = $tatumBalance;


        if (is_null($cryptoAssetSetting) ) {
            throw new Exception(__(':x Network is not available', ['x' => $request->network]));
        }

        if ($this->cryptoNetwork->networkName() !== strtolower($request->name)) {
            throw new Exception(__(':x is not appropriate Coin name for  :y Network', ['x' => $request->name, 'y' => $request->network]));
        }
        $cryptoAssetSetting->network_credentials = json_encode($tatumArray);

        $cryptoAssetSetting->status = $currency->status;
        $cryptoAssetSetting->save();


        if (isset($request->create_address) && $request->create_address == 'on') {
            $credentials = json_decode($cryptoAssetSetting->network_credentials);
            $this->xpub = $credentials->xpub;
            $this->mnemonic = $credentials->mnemonic;
            $this->createUsersNetworkAddress($currency->code, $currency->id);
        }

    }

    protected function createUsersNetworkAddress($network, $currencyId)
    {
        try {
            $users = \App\Models\User::with(['wallets' => function ($q) use ($currencyId)
            {
                $q->where(['currency_id' => $currencyId]);
            }])
            ->where(['status' => 'Active'])
            ->get(['id', 'email']);

            if (!empty($users)) {
                foreach ($users as $user) {
                    $getWalletObject = (new Common)->getUserWallet([], ['user_id' => $user->id, 'currency_id' => $currencyId], ['id']);
                    if (empty($getWalletObject) && count($user->wallets) == 0) {
                        $wallet              = new \App\Models\Wallet();
                        $wallet->user_id     = $user->id;
                        $wallet->currency_id = $currencyId;
                        $wallet->is_default  = 'No';
                        $wallet->save();
                        $this->createCryptoWalletLog($wallet->id, $user->id, $network);
                    }
                }
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }


    public function createCryptoWalletLog($walletId, $userId, $network)
    {
        try {
            $getTatumAssetApiLog = (new CryptoAssetApiLog())->getCryptoAssetapiLog(['payment_method_id' => TatumIo, 'object_id' => $walletId, 'object_type' => 'wallet_address', 'network' => $network], ['id']);
            if (empty($getTatumAssetApiLog)) {
                $tatumAddress = $this->cryptoNetwork->generateAddress($this->xpub, $userId);
                $tatumKey = $this->cryptoNetwork->generateAddressPrivateKey($userId, $this->mnemonic);
                $tatumBalance =  $this->cryptoNetwork->getBalanceOfAddress($tatumAddress['address']);
                $this->cryptoNetwork->createSubscription($tatumAddress['address']);

                $tatumNetworkArray = [];

                $tatumNetworkArray['address'] = $tatumAddress['address'];
                $tatumNetworkArray['key'] = isset($tatumKey['key']) ? $tatumKey['key'] : '';
                $tatumNetworkArray['balance'] =  $tatumBalance;
                $tatumNetworkArray['user_id'] =  $userId;
                $tatumNetworkArray['wallet_id'] =  $walletId;
                $tatumNetworkArray['network'] =  $network;

                //create new crypt api log if empty
                $tatumIoAssetApiLog = new CryptoAssetApiLog();
                $tatumIoAssetApiLog->payment_method_id = TatumIo;
                $tatumIoAssetApiLog->object_id = $walletId;
                $tatumIoAssetApiLog->object_type = 'wallet_address';
                $tatumIoAssetApiLog->network = $network;
                $tatumIoAssetApiLog->payload = json_encode($tatumNetworkArray);
                $tatumIoAssetApiLog->save();
            }


        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function assetStatusChange(Request $request)
    {
        $network = decrypt($request->network);

        $cryptoAssetSetting = CryptoAssetSetting::with(['currency' => function($query) {
            $query->where('type', 'crypto_asset');
        }])
        ->where(['network' => $network, 'payment_method_id' => TatumIo])
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

    // Validate crypto address
    public function validateAddress(Request $request)
    {
        $network = $request->network;
        $address = $request->address;
        $tatumIo = new TatumIoTransaction($network);
        $tatumIo->tatumIoAsset();
        return $tatumIo->checkAddress($address);
    }





}
