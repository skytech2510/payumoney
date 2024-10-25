<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('receive/tatumio-balance-change-notification', 'TatumIoNotificationController@balanceNotification')->name('tatumio.balance.notification');

Route::get('receive/tatumio-balance-change-notification', 'TatumIoNotificationController@balanceNotification');

Route::group(config('addons.route_group.authenticated.admin'), function()
{
    Route::get('tatumio/create', 'Admin\TatumIoAssetSettingController@create')->name('admin.tatumio_asset.create')->middleware('permission:add_crypto_asset');
    Route::post('tatumio/store', 'Admin\TatumIoAssetSettingController@store')->name('admin.tatumio_asset.store')->middleware('permission:add_crypto_asset');
    Route::get('tatumio/edit/{network}', 'Admin\TatumIoAssetSettingController@edit')->name('admin.tatumio_asset.edit')->middleware('permission:edit_crypto_asset');
    Route::post('tatumio/update/{network}', 'Admin\TatumIoAssetSettingController@update')->name('admin.tatumio_asset.update')->middleware('permission:edit_crypto_asset');

    Route::post('tatumio/asset-status-change', 'Admin\TatumIoAssetSettingController@assetStatusChange')->name('admin.tatumio_asset.status_change');
    Route::get('tatumio/validate-address', 'Admin\TatumIoAssetSettingController@validateAddress')->name('admin.tatumio_asset.validate_address');

    // Admin Crypto Send
    Route::get('tatumio/crypto-send/initiate/{code}', 'Admin\CryptoSendReceiveController@cryptoSentInitiate')->name('admin.tatum.crypto_send.create');
    Route::post('tatumio/crypto-send/confirm', 'Admin\CryptoSendReceiveController@eachUserCryptoSentConfirm')->name('admin.tatum.crypto_send.confirm');
    Route::post('tatumio/crypto-send/success', 'Admin\CryptoSendReceiveController@eachUserCryptoSentSuccess')->name('admin.tatum.crypto_send.success');
    Route::get('tatumio/crypto-send/get-merchant-user-network-address-with-merchant-balance', 'Admin\CryptoSendReceiveController@getMerchantUserNetworkAddressWithMerchantBalance')->name('admin.tatum.crypto.address_balance');

    Route::get('tatumio/crypto-send/validate-merchant-address-balance', 'Admin\CryptoSendReceiveController@validateMerchantAddressBalanceAgainstAmount')->name('admin.tatum.crypto.validate_balance');


    // Admin Crypto Receive
    Route::get('tatumio/crypto-receive/initiate/{code}', 'Admin\CryptoSendReceiveController@cryptoReceiveInitiate')->name('admin.tatum.crypto_receive.create');
    Route::post('tatumio/crypto-receive/confirm', 'Admin\CryptoSendReceiveController@eachUserCryptoReceiveConfirm')->name('admin.tatum.crypto_receive.confirm');
    Route::post('tatumio/crypto-receive/success', 'Admin\CryptoSendReceiveController@eachUserCryptoReceiveSuccess')->name('admin.tatum.crypto_receive.success');
    Route::get('tatumio/crypto-receive/get-user-network-address-balance-with-merchant-address', 'Admin\CryptoSendReceiveController@getUserNetworkAddressWithUserBalance')->name('admin.tatum.crypto_receive.network_balance');
    Route::get('tatumio/crypto-receive/validate-user-address-balance', 'Admin\CryptoSendReceiveController@validateUserAddressBalanceAgainstAmount')->name('admin.tatum.crypto_receive.validate_balance');

    // Admin crypto send-receive print pdf
});


Route::group(config('addons.route_group.authenticated.user'), function()
{
    // Crypto Send
    Route::prefix('crypto/send/tatumio')->as('tatumio.user.crypto_send.')->namespace('Users')->middleware('permission:manage_crypto_send_receive')->group(function() {
        Route::get('validate-address', 'CryptoSendController@validateCryptoAddress')->name('validate_address');
        Route::get('validate-user-balance', 'CryptoSendController@validateUserBalanceAgainstAmount')->name('validate_balance');
        Route::post('success', 'CryptoSendController@sendCryptoSuccess')->name('success');
        Route::post('confirm', 'CryptoSendController@sendCryptoConfirm')->name('confirm');
        Route::get('{walletCurrencyCode}/{walletId}', 'CryptoSendController@sendCryptoCreate')->name('create');
    });

    // Crypto Receive
    Route::get('crypto/receive/tatumio/{walletCurrencyCode}/{walletId}', 'Users\CryptoReceiveController@receiveCrypto')->name('tatumio.user.crypto_receive.create')->middleware(['permission:manage_crypto_send_receive']);

    // Crypto send receive print pdf
    Route::get('transactions/crypto-sent-received-print/{id}', 'Users\CryptoReceiveController@cryptoSentReceivedTransactionPrintPdf')->name('user.crypto_send_receive.print');
});


