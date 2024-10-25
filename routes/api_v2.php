<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('login', 'LoginController@login');
Route::post('registration', 'RegistrationController@registration');
Route::post('duplicate-email-check', 'RegistrationController@checkDuplicateEmail');
Route::post('duplicate-phone-number-check', 'RegistrationController@checkDuplicatePhoneNumber');
Route::get('default-country-short-name', 'CountryController@getDefaultCountryShortName');
Route::get('countries', 'CountryController@list');
Route::get('user-types', 'PreferenceController@userRoles');

Route::post('forget-password', 'ForgotPasswordController@forgetPassword');
Route::post('forget-password/verify', 'ForgotPasswordController@verifyResetCode');
Route::post('forget-password/store', 'ForgotPasswordController@confirmNewPassword');

/**
 * Preference routes
 */
Route::group(['prefix' => 'preference'], function () {
    Route::get('/', 'PreferenceController@preferenceSettings');
    Route::get('custom', 'PreferenceController@customSetting');
    Route::get('check-login-via', 'PreferenceController@checkLoginVia');
    Route::get('check-processed-by', 'PreferenceController@checkProcessedByApi');
});

Route::group(['middleware' => ['auth:api-v2', 'check-user-inactive']], function () {
    Route::get('check-user-status', 'ProfileController@checkUserStatus');
    /**
     * Profile routes
     */
    Route::group(['middleware' => ['permission:manage_setting']], function ()
    {
        Route::group(['prefix' => 'profile'], function () {
            Route::get('/summary', 'ProfileController@summary');
            Route::get('/details', 'ProfileController@details');
            Route::put('/update', 'ProfileController@update');
            Route::post('/upload-image', 'ProfileController@uploadImage');
            Route::post('/duplicate-phone-number-check', 'ProfileController@checkDuplicatePhoneNumber');
        });
        Route::post('/change-password', 'ProfileController@changePassword');
        Route::get('/default-wallet-balance', 'ProfileController@getDefaultWalletBalance');
        Route::get('/available-balances', 'ProfileController@getUserAvailableWalletsBalance');
    });

    /**
     * Transaction routes
     */
    Route::group(['prefix' => 'transaction', 'middleware' => ['permission:manage_transaction']], function () {
        Route::get('activityall', 'TransactionController@list');
        Route::post('details', 'TransactionController@details');
    });

    /**
     * Send money routes
     */
    Route::group(['name' => 'send-money.', 'prefix' => 'send-money', 'middleware' => ['permission:manage_transfer', 'check-user-suspended']], function () {
        Route::post('/email-check', 'SendMoneyController@emailValidate')->name('validate-email');
        Route::post('/phone-check', 'SendMoneyController@phoneValidate')->name('validate-phone');
        Route::get('/get-currencies', 'SendMoneyController@getCurrencies')->name('get-currencies');
        Route::post('/check-amount-limit', 'SendMoneyController@amountLimitCheck')->name('check-amount-limit');
        Route::post('/confirm', 'SendMoneyController@sendMoneyConfirm')->name('confirm');
    });

    /**
     * Accept Money routes
     */
     Route::group(['prefix' => 'accept-money', 'middleware' => ['permission:manage_request_payment', 'check-user-suspended']], function () {
        Route::get('details', 'AcceptCancelRequestMoneyController@details');
        Route::post('amount-limit-check', 'AcceptCancelRequestMoneyController@checkAmountLimit');
    });

    /**
     * Exchange money routes
     */
    Route::group(['prefix' => 'exchange-money', 'middleware' => ['permission:manage_exchange', 'check-user-suspended']], function () {
        Route::get('get-currencies', 'ExchangeMoneyController@getCurrencies');
        Route::post('amount-limit-check', 'ExchangeMoneyController@exchangeLimitCheck');
        Route::post('get-wallets-balance', 'ExchangeMoneyController@getExchangeWalletsBalance');
        Route::post('get-destination-wallets', 'ExchangeMoneyController@getExchangableDestinations');
        Route::post('get-exchange-rate', 'ExchangeMoneyController@getCurrenciesExchangeRate');
        Route::post('confirm-details', 'ExchangeMoneyController@reviewExchangeDetails');
        Route::post('complete', 'ExchangeMoneyController@exchangeMoneyComplete');
    });


    /**
     * Deposit money rotue
     */
    Route::group(['prefix' => 'deposit-money', 'middleware' => ['permission:manage_deposit', 'check-user-suspended']], function () {
        Route::get('get-currencies', 'DepositMoneyController@getCurrencies');
        Route::post('amount-limit-check', 'DepositMoneyController@validateDepositData');
        Route::post('payment-methods', 'DepositMoneyController@getPaymentMethod');
        Route::post('get-bank-list', 'DepositMoneyController@getBankList');
        Route::post('get-bank-detail', 'DepositMoneyController@getBankDetails');
        Route::post('stripe-make-payment', 'DepositMoneyController@stripePaymentInitiate');
        Route::post('payment-confirm', 'DepositMoneyController@paymentConfirm');
        Route::post('get-paypal-info', 'DepositMoneyController@getPaypalInfo');
    });


    // Request Money routes
    Route::group(['prefix' => 'request-money', 'middleware' => ['permission:manage_request_payment', 'check-user-suspended']], function () {
        Route::post('email-check', 'RequestMoneyController@checkEmail');
        Route::post('phone-check', 'RequestMoneyController@checkPhone');
        Route::get('currencies', 'RequestMoneyController@getCurrency');
        Route::post('confirm', 'RequestMoneyController@store');
        Route::post('accept', 'AcceptCancelRequestMoneyController@store');
        Route::post('cancel-by-creator', 'AcceptCancelRequestMoneyController@cancelByCreator');
        Route::post('cancel-by-receiver', 'AcceptCancelRequestMoneyController@cancelByReceiver');

    });

    /**
     * Withdrawal setting routes
     */
    Route::group(['prefix' => 'withdrawal-setting', 'middleware' => ['permission:manage_withdrawal', 'check-user-suspended']], function () {
        Route::get('/payment-methods', 'WithdrawalSettingController@paymentMethods');
        Route::get('/crypto-currencies', 'WithdrawalSettingController@cryptoCurrencies');
    });
    Route::resource('/withdrawal-settings', WithdrawalSettingController::class)->middleware('permission:manage_withdrawal', 'check-user-suspended');

    /**
     * Withdrawal routes
     */
    Route::group(['prefix' => 'withdrawal', 'middleware' => ['permission:manage_withdrawal', 'check-user-suspended']], function () {
        Route::post('get-currencies', 'WithdrawalController@getCurrencies');
        Route::post('amount-limit-check', 'WithdrawalController@amountLimitCheck');
        Route::post('confirm', 'WithdrawalController@Confirm');
    });

    /**
     * QrCode
     */
    Route::group(['prefix' => 'qr-code', 'middleware' => ['check-user-suspended']], function () {
        Route::get('get-qr-code', 'QrCodeController@getQrCode');
        Route::post('add-update-qr-code', 'QrCodeController@addOrUpdateQrCode');
        Route::post('send-request-qr-operation', 'QrCodeController@sendRequestQrOperation');
        Route::post('merchant-qr-operation', 'QrCodeController@merchantQrOperation');
        Route::post('standard-merchant-payment-review', 'QrCodeController@standardMerchantPaymentReview');
        Route::post('express-merchant-payment-review', 'QrCodeController@expressMerchantPaymentReview');
        Route::post('standard-merchant-payment-submit', 'QrCodeController@merchantPaymentSubmit');
    });

});
