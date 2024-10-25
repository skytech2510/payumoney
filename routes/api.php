<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
 */
Route::group(['namespace' => 'Api'], function ()
{
    //Route for Preference Settings
    Route::get('get-preference-settings', 'LoginController@getPreferenceSettings');

    //Route for Paymoney Settings
    Route::get('get-paymoney-settings', 'LoginController@getPaymoneySettingsFromApi');

    //Check User Inactive Status
    Route::get('check-user-status', 'ProfileController@checkUserStatusApi');

    Route::get('check-login-via', 'LoginController@checkLoginVia');
    Route::post('login', 'LoginController@login');

    //Routes for Registration
    Route::get('check-merchant-user-role-existence', 'RegistrationController@getMerchantUserRoleExistence');
    Route::post('registration', 'RegistrationController@registration');
    Route::post('registration/duplicate-email-check', 'RegistrationController@duplicateEmailCheckApi');
    Route::post('registration/duplicate-phone-number-check', 'RegistrationController@duplicatePhoneNumberCheckApi');
    Route::get('registration/get-default-country-short-name', 'RegistrationController@getDefaultCountryShortName');
});

/*
|--------------------------------------------------------------------------
| API Routes - With Authorization Middleware
|--------------------------------------------------------------------------
 */
Route::group(['namespace' => 'Api', 'middleware' => ['check-authorization-token']], function ()//bug-fixed-pm-v2.7
{
    //Routes for User Profile starts here
    Route::get('get-default-wallet-balance', 'ProfileController@getDefaultWalletBalance');

    Route::get('get-user-profile', 'ProfileController@getUserProfile');
    Route::post('update-user-profile', 'ProfileController@updateUserProfile');
    Route::get('get-user-specific-details', 'ProfileController@getUserSpecificProfile');
    Route::post('profile/duplicate-email-check', 'ProfileController@userProfileDuplicateEmailCheckApi');
    Route::get('check-processed-by', 'ProfileController@checkProcessedByApi');
    Route::post('profile-image-upload', 'ProfileController@profileImage');
    Route::post('update-password', 'ProfileController@updatePassword');

    //Route for User Profile Ends here

    //Route for User's Available Balance
    Route::get('available-balance', 'ProfileController@getUserAvailableWalletsBalances');

    //Routes for Transactions starts here
    Route::get('activityall', 'TransactionController@getTransactionApi');
    Route::get('transaction-details', 'TransactionController@getTransactionDetailsApi');
    Route::get('transaction-details/request-payment/check-creator-status', 'RequestMoneyController@checkReqCreatorStatusApi');
    //Route for Transactions Ends here

    //Routes for deposit Starts here
    Route::get('get-deposit-currency-list', 'DepositMoneyController@getDepositCurrencyList');
    Route::get('get-deposit-bank-list', 'DepositMoneyController@getDepositBankList');
    Route::post('fees-limit-currency-payment-methods-is-active-payment-methods-list', 'DepositMoneyController@getDepositMatchedFeesLimitsCurrencyPaymentMethodsSettingsPaymentMethods');
    Route::get('get-deposit-details-with-amount-limit-check', 'DepositMoneyController@getDepositDetailsWithAmountLimitCheck');
    Route::post('deposit/get-bank-detail', 'DepositMoneyController@getBankDetails');
    Route::post('deposit/bank-payment-store', 'DepositMoneyController@bankPaymentStore');
    Route::post('deposit/coinpayments-payment-store', 'DepositMoneyController@coinpaymentsPaymentStore');
    Route::post('deposit/stripe-make-payment', 'DepositMoneyController@stripeMakePayment');
    Route::post('deposit/stripe-confirm-payment', 'DepositMoneyController@stripeConfirm');
    Route::post('deposit/get-paypal-info', 'DepositMoneyController@getPeypalInfo');
    Route::post('deposit/paypal-payment-store', 'DepositMoneyController@paypalPaymentStore');
    //Routes for deposit ends here

    //Routes for withdraw Setting Starts here
    Route::get('payout-setting', 'PayoutSettingController@index');
    Route::POST('add-withdraw-setting', 'PayoutSettingController@store');
    Route::POST('edit-withdraw-setting', 'PayoutSettingController@update');
    Route::POST('delete-payout-setting', 'PayoutSettingController@delete');
    Route::get('get-withdraw-payment-methods', 'PayoutSettingController@paymentMethods');
    Route::get('withdrawal/get-all-countries', 'PayoutSettingController@getAllCountries');
    Route::get('withdrawal/get-withdrawal-crypto-currencies', 'PayoutSettingController@getWithdrawalCryptoCurrencies');
    //Routes for withdraw Setting ends here

    //Routes for withdraw Starts here
    Route::get('check-payout-settings', 'PayoutMoneyController@checkPayoutSettingsApi');
    Route::get('get-withdraw-payment-method', 'PayoutMoneyController@getWithdrawalPaymentMethod');
    Route::get('get-withdraw-currencies-based-on-payment-method', 'PayoutMoneyController@getWithdrawalCurrencyBasedOnPaymentMethod');
    Route::get('get-withdraw-details-with-amount-limit-check', 'PayoutMoneyController@getWithdrawDetailsWithAmountLimitCheck');
    Route::post('withdraw-money-pay', 'PayoutMoneyController@withdrawMoneyConfirm');
    //Routes for withdraw ends here

    //Route for Send Money Starts here
    Route::post('send-money-email-check', 'SendMoneyController@postSendMoneyEmailCheckApi');
    Route::post('send-money-phone-check', 'SendMoneyController@postSendMoneyPhoneCheckApi');
    Route::get('get-send-money-currencies', 'SendMoneyController@getSendMoneyCurrenciesApi');
    Route::post('check-send-money-amount-limit', 'SendMoneyController@postSendMoneyFeesAmountLimitCheckApi');
    Route::post('send-money-pay', 'SendMoneyController@postSendMoneyPayApi');
    //Routes for Send Money Ends here

    //Routes for Request Money Starts here
    Route::post('request-money-email-check', 'RequestMoneyController@postRequestMoneyEmailCheckApi');
    Route::post('request-money-phone-check', 'RequestMoneyController@postRequestMoneyPhoneCheckApi');//
    Route::get('get-request-currency', 'RequestMoneyController@getRequestMoneyCurrenciesApi');//
    Route::post('request-money-pay', 'RequestMoneyController@postRequestMoneyPayApi');
    //Routes for Request Money Ends here

    //Routes for accept/cancel request payment starts here
    Route::get('accept-request-email-phone', 'AcceptCancelRequestMoneyController@getAcceptRequestEmailOrPhone');
    Route::post('request-accept-amount-limit-check', 'AcceptCancelRequestMoneyController@getAcceptRequestAmountLimit');
    Route::get('get-accept-fees-details', 'AcceptCancelRequestMoneyController@getAcceptFeesDetails');
    Route::post('accept-request-payment-pay', 'AcceptCancelRequestMoneyController@requestAcceptedConfirm');
    Route::post('cancel-request', 'AcceptCancelRequestMoneyController@requestCancel');
    //Routes for accept/cancel request payment ends here

    //Routes for exchange money starts here
    Route::get('get-User-Wallets-WithActive-HasTransaction', 'ExchangeMoneyController@getUserWalletsWithActiveAndHasTransactionCurrency');
    Route::post('exchange-review', 'ExchangeMoneyController@exchangeReview');
    Route::post('getBalanceOfFromAndToWallet', 'ExchangeMoneyController@getBalanceOfFromAndToWallet');
    Route::post('getWalletsExceptSelectedFromWallet', 'ExchangeMoneyController@getWalletsExceptSelectedFromWallet');
    Route::post('get-currencies-exchange-rate', 'ExchangeMoneyController@getCurrenciesExchangeRate');
    Route::post('review-exchange-details', 'ExchangeMoneyController@reviewExchangeDetails');
    Route::post('exchange-money-complete', 'ExchangeMoneyController@exchangeMoneyComplete');
    //Routes for exchange money ends here

    //Route for Logout
    Route::post('logout', 'LoginController@logout');

    //qr-code
    Route::post('perform-qr-code-operation', 'QrCodeController@performQrCodeOperationApi'); //new
    Route::get('get-user-qr-code', 'QrCodeController@getUserQrCode'); //new
    Route::post('add-or-update-user-qr-code', 'QrCodeController@addOrUpdateUserQrCode'); //new
 
    //Send Money & Request Money - QrCode Operations
    Route::post('send-money-request-money-perform-qr-code-operation', 'QrCodeController@performSendMoneyRequestMoneyQrCodeOperationApi'); //new
 
    //Standard Merchant - QrCode Operations
    Route::post('perform-merchant-payment-qr-code-review', 'QrCodeController@performMerchantPaymentQrCodeReviewApi'); //new
    Route::post('perform-merchant-payment-qr-code-submit', 'QrCodeController@performMerchantPaymentQrCodeSubmit'); //new
 
    //Express Merchant - QrCode Operations
    Route::post('perform-express-merchant-payment-qr-code-merchant-currency-user-wallets-review', 'QrCodeController@performExpressMerchantPaymentMerchantCurrencyUserWalletsReviewApi'); //new
    Route::post('perform-express-merchant-payment-qr-code-merchant-amount-review', 'QrCodeController@performExpressMerchantPaymentAmountReviewApi'); //new
    Route::post('perform-express-merchant-payment-qr-code-submit', 'QrCodeController@performExpressMerchantPaymentQrCodeSubmit');
});

// Route::middleware(['auth:api', 'permission:manage_merchant']);//permission test
