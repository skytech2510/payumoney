<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


 /**
     * Block.io Crypto Send
     */
    Route::group(['prefix' => 'crypto/send/tatumio', 'middleware' => ['auth:api-v2', 'check-user-suspended']], function () {
        Route::get('provider-status', 'CryptoSendController@providerStatus');

        Route::get('validate-address', 'CryptoSendController@validateCryptoAddress');

        Route::get('user-address/{walletCurrencyCode}', 'CryptoSendController@userCryptoAddress');

        Route::get('validate-user-balance', 'CryptoSendController@validateUserBalanceAgainstAmount');

        Route::post('confirm', 'CryptoSendController@cryptoSendConfirm');

        Route::get('provider-status', 'CryptoSendController@providerStatus');
    });
