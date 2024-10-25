<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    return redirect('/');
});

Route::get('/', 'HomeController@index')->name('home');
Route::get('/privacy-policy', 'HomeController@privacyPolicy')->name('privacy_policy');

// changing-language
Route::get('change-lang', 'HomeController@setLocalization');

//coinPayment IPN
Route::post('coinpayment/check', 'Users\DepositController@coinpaymentCheckStatus');

// user email check on registration
Route::post('user-registration-check-email', 'Auth\RegisterController@checkUserRegistrationEmail');

// Unauthenticated User
Route::group(['middleware' => ['no_auth:users', 'locale']], function () {
    Route::get('/login', 'Auth\LoginController@index')->name("login");
    Route::post('/authenticate', 'Auth\LoginController@authenticate');
    Route::get('register', 'Auth\RegisterController@create');
    Route::post('register/duplicate-phone-number-check', 'Auth\RegisterController@registerDuplicatePhoneNumberCheck');
    Route::post('register/store-personal-info', 'Auth\RegisterController@storePersonalInfo')->name('register.personal.info');
    Route::post('register/store', 'Auth\RegisterController@store')->name('register.store');
    Route::get('/user/verify/{token}', 'Auth\RegisterController@verifyUser');
    Route::view('forget-password', 'frontend.auth.forgetPassword')->name('user.forget_password');
    Route::post('forget-password', 'Auth\ForgotPasswordController@forgetPassword');
    Route::get('password/resets/{token}', 'Auth\ForgotPasswordController@verifyToken');
    Route::post('confirm-password', 'Auth\ForgotPasswordController@confirmNewPassword');
});

//2fa
Route::group(['middleware' => ['guest:users', 'locale', 'check-user-inactive'], 'namespace' => 'Users'], function () {
    Route::get('2fa', 'CustomerController@view2fa');
    Route::post('2fa/verify', 'CustomerController@verify2fa')->name('2fa-verify.store');
    Route::get('google2fa', 'CustomerController@viewGoogle2fa')->name('google2fa');
    Route::post('google2fa/verify', 'CustomerController@verifyGoogle2fa')->name('2fa-verify.google_authenticator');
    Route::post('google2fa/verifyGoogle2faOtp', 'CustomerController@verifyGoogle2faOtp')->name('2fa-verify.google_otp');
});

// Authenticated User
Route::group(['middleware' => ['guest:users', 'locale', 'twoFa', 'check-user-inactive'], 'namespace' => 'Users'], function () {
    Route::get('dashboard', 'CustomerController@dashboard')->name('user.dashboard');
    Route::get('wallet-list', 'CustomerController@getWallets')->name('user.wallets.index');

    Route::get('/logout', 'CustomerController@logout')->name('user.logout');
    Route::get('check-user-status', 'CustomerController@checkUserStatus');
    Route::get('check-request-creator-suspended-status', 'CustomerController@checkRequestCreatorSuspendedStatus')->name('user.request_money.creator_suspend');
    Route::get('check-request-creator-inactive-status', 'CustomerController@checkRequestCreatorInactiveStatus')->name('user.request_money.creator_inactive');;
    Route::get('check-processed-by', 'CustomerController@checkProcessedBy')->name('');

    //Settings
    Route::group(['middleware' => ['permission:manage_setting']], function () {
        Route::get('profile', 'CustomerController@profile')->name('user.profiles.index');
        Route::get('profile/2fa', 'CustomerController@profileTwoFa')->name('user.setting.twoFa');;
        Route::post('profile/2fa/update', 'CustomerController@UpdateProfileTwoFa');
        Route::post('profile/2fa/ajaxTwoFa', 'CustomerController@ajaxTwoFa')->name('user.setting.2fa-verify.create');

        //add or update user's qr-code
        Route::post('profile/qr-code/add-or-update', 'CustomerController@addOrUpdateUserProfileQrCode')->name('user.profile.qrcode.update');
        Route::get('profile/qr-code-print/{id}/{printQrCode}', 'CustomerController@printUserQrCode')->name('user.profile.qrcode.print');

        //KYC
        Route::get('profile/personal-id', 'CustomerController@personalId')->name('user.setting.identitiy_verify');
        Route::post('profile/personal-id-update', 'CustomerController@updatePersonalId')->name('user.setting.identitiy_verify.update');
        Route::get('profile/personal-address', 'CustomerController@personalAddress')->name('user.setting.address_verify');
        Route::post('profile/personal-address-update', 'CustomerController@updatePersonalAddress')->name('user.setting.address_verify.update');
        Route::get('/kyc-proof-download/{fileName}/{fileType}', 'CustomerController@download')->name('user.setting.kyc_proof.download');

        //google2fa
        Route::post('profile/2fa/google2fa', 'CustomerController@google2fa')->name('user.setting.2fa-verify.google');
        Route::post('profile/2fa/google2fa/complete-google2fa-verification', 'CustomerController@completeGoogle2faVerification')->name('user.setting.2fa-verify.google_complete');
        Route::post('profile/2fa/google2fa/otp-verify', 'CustomerController@google2faOtpVerification')->name('user.setting.2fa-verify.google_otp');

        //2fa
        Route::post('profile/2fa/disabledTwoFa', 'CustomerController@disabledTwoFa')->name('user.setting.2fa-verify.disabled');
        Route::post('profile/2fa/ajaxTwoFaSettingsVerify', 'CustomerController@ajaxTwoFaSettingsVerify')->name('user.setting.2fa-verify.settings');
        Route::post('profile/2fa/check-phone', 'CustomerController@checkPhoneFor2fa')->name('user.setting.2fa-verify.phone');
        //

        Route::post('prifile/update_password', 'CustomerController@updateProfilePassword')->name('user.profile.password.update');
        Route::view('profile-image-upload', 'user.profile.index');
        Route::post('profile-image-upload', 'CustomerController@profileImage')->name('user.profile.image_upload');
        Route::post('profile/getVerificationCode', 'CustomerController@generatePhoneVerificationCode');
        Route::post('profile/complete-phone-verification', 'CustomerController@completePhoneVerification');
        Route::post('profile/add-phone-number', 'CustomerController@addPhoneNumberViaAjax');
        Route::post('profile/update-phone-number', 'CustomerController@updatePhoneNumberViaAjax');
        Route::post('profile/editGetVerificationCode', 'CustomerController@editGeneratePhoneVerificationCode');
        Route::post('profile/edit-complete-phone-verification', 'CustomerController@editCompletePhoneVerification');
        Route::post('profile/delete-phone-number', 'CustomerController@deletePhoneNumberViaAjax');
        Route::post('prifile/update', 'CustomerController@updateProfileInfo')->name('user.profile.update');
        Route::post('profile/duplicate-phone-number-check', 'CustomerController@userDuplicatePhoneNumberCheck')->name('user.profile.duplicate_check.phone');
        Route::post('profile/change-default-currency', 'CustomerController@updateDefaultCurrency')->name('user.profile.default_currency');
    });

    // Deposit - With Suspend Middleware
    Route::group(['middleware' => ['permission:manage_deposit', 'check-user-suspended']], function () {
        Route::get('deposit', 'DepositController@create')->name('user.deposit.create');
        Route::post('deposit/confirm', 'DepositController@depositConfirm')->name('user.deposit.confirm');
        Route::post('deposit/gateway', 'DepositController@depositGateway')->name('user.deposit.gateway');
        Route::post('deposit/getDepositFeesLimit', 'DepositController@getDepositFeesLimit')->name('user.deposit.fees_limit');
        Route::post('deposit/payment-methods-list', 'DepositController@getPaymentMethods')->name('user.deposit.payment_method');
        Route::post('deposit/store', 'DepositController@store')->name('user.deposit.store');
    });



    // Withdrawal - Without Suspend Middleware
    Route::group(['middleware' => ['permission:manage_withdrawal']], function () {
        Route::get('payouts', 'WithdrawalController@payouts')->name('user.withdrawal.index');
        Route::get('payout/setting', 'WithdrawalController@payoutSetting')->name('user.withdrawal.setting');
        Route::get('withdrawal-money/print/{id}', 'WithdrawalController@withdrawalPrintPdf')->name('user.withdrawal.print');
    });

    // Withdrawal - With Suspend Middleware
    Route::group(['middleware' => ['permission:manage_withdrawal', 'check-user-suspended']], function () {
        Route::post('payout/setting/store', 'WithdrawalController@payoutSettingStore')->name('user.withdrawal.setting.store');
        Route::post('payout/setting/update', 'WithdrawalController@payoutSettingUpdate')->name('user.withdrawal.setting.update');
        Route::post('payout/setting/delete', 'WithdrawalController@payoutSettingDestroy')->name('user.withdrawal.setting.delete');

        Route::get('payout', 'WithdrawalController@withdrawalCreate')->name('user.withdrawal.create');
        Route::post('payout/store', 'WithdrawalController@withdrawalConfirm')->name('user.withdrawal.confirm');
        Route::get('withdrawal/confirm-transaction', 'WithdrawalController@withdrawalSuccess')->name('user.withdrawal.success');

        Route::get('withdrawal/method/{id}', 'WithdrawalController@selectWithdrawalMethod');
        Route::post('withdrawal/store', 'WithdrawalController@withdrawalStore');
        Route::post('withdrawal/amount-limit', 'WithdrawalController@withdrawalAmountLimitCheck')->name('user.withdrawal.amount_limit_check');
        Route::post('withdrawal/fees-limit-payment-method-isActive-currencies', 'WithdrawalController@getWithdrawalFeesLimitsActiveCurrencies')->name('user.withdrawal.active_currencies');
    });

    //Transfer - Without Suspend Middleware
    Route::group(['middleware' => ['permission:manage_transfer']], function () {
        Route::get('moneytransfer/print/{id}', 'MoneyTransferController@transferPrintPdf')->name('user.send_money.print');
    });

    //Transfer - With Suspend Middleware
    Route::group(['middleware' => ['permission:manage_transfer', 'check-user-suspended']], function () {
        Route::get('moneytransfer', 'MoneyTransferController@create')->name('user.send_money.create');
        Route::post('transfer', 'MoneyTransferController@store')->name('user.send_money.store');
        Route::post('transfer-user-email-phone-receiver-status-validate', 'MoneyTransferController@transferUserEmailPhoneReceiverStatusValidate')->name('user.send_money.receiver_status_check');
        Route::post('amount-limit', 'MoneyTransferController@amountLimitCheck')->name('user.send_money.check_amount_limit');
        Route::post('send-money-confirm', 'MoneyTransferController@sendMoneyConfirm')->name('user.send_money.confirm');
    });

    // transactions
    Route::group(['middleware' => ['permission:manage_transaction']], function () {
        Route::get('transactions', 'UserTransactionController@index')->name('user.transactions.index');
        Route::get('transactions/{id}', 'UserTransactionController@showDetails');
        Route::post('get_transaction', 'UserTransactionController@getTransaction');
        Route::get('transactions/print/{id}', 'UserTransactionController@getTransactionPrintPdf')->name('user.transactions.print');
        Route::get('transactions/exchangeTransactionPrintPdf/{id}', 'UserTransactionController@exchangeTransactionPrintPdf')->name('user.exchange_transaction.print');
        Route::get('transactions/merchant-payment-print/{id}', 'UserTransactionController@merchantPaymentTransactionPrintPdf')->name('user.merchant_payment.print');
    });

    // Currency Exchange - Without Suspend Middleware
    Route::group(['middleware' => ['permission:manage_exchange']], function () {
        Route::get('exchange-money/print/{id}', 'ExchangeController@exchangeOfPrintPdf')->name('user.exchange_money.print');
    });

    // Currency Exchange - With Suspend Middleware
    Route::group(['middleware' => ['permission:manage_exchange', 'check-user-suspended']], function () {
        Route::get('exchange', 'ExchangeController@exchange')->name('user.exchange_money.create');
        Route::post('exchange-of-money', 'ExchangeController@exchangeOfCurrency')->name('user.exchange_money.store');
        Route::post('exchange-of-money-success', 'ExchangeController@exchangeOfCurrencyConfirm')->name('user.exchange_money.confirm');
        Route::post('exchange/get-converted-currencies', 'ExchangeController@getActiveHasTransactionExceptUsersExistingWalletsCurrencies')->name('user.exchange_money.currency_list_except_selected');
        Route::post('exchange/get-currencies-exchange-rate', 'ExchangeController@getCurrenciesExchangeRate')->name('user.exchange_money.exchange_rate');
        Route::post('exchange/selected-currency-wallet-balance', 'ExchangeController@getBalanceOfToWallet')->name('user.exchange_money.wallet_balance');
        Route::post('exchange/amount-limit-check', 'ExchangeController@amountLimitCheck')->name('user.exchange_money.amount_limit_check');
    });

    // Request Payments - Without Suspend Middleware
    Route::group(['middleware' => ['permission:manage_request_payment']], function () {
        Route::get('request-payment/print/{id}', 'RequestPaymentController@printPdf')->name('user.request_money.print');
        Route::post('request_payment/cancel', 'RequestPaymentController@cancel')->name('user.request_money.cancel');
    });

    // Request Payments - With Suspend Middleware
    Route::group(['middleware' => ['permission:manage_request_payment', 'check-user-suspended']], function () {
        Route::get('request_payment/check-creator-status', 'RequestPaymentController@checkReqCreatorStatus')->name('user.request_money.creator_status_check');
        Route::get('request_payment/add', 'RequestPaymentController@add')->name('user.request_money.create');
        Route::post('request', 'RequestPaymentController@store')->name('user.request_money.store');
        Route::get('request_payment/accept/{id}', 'RequestPaymentController@requestAccept')->name('user.request_money.accept.create');
        Route::post('request-payment/amount-limit', 'RequestPaymentController@amountLimitCheck')->name('user.request_money.accept.limit');
        Route::post('request_payment/request-user-email-phone-receiver-status-validate', 'RequestPaymentController@requestUserEmailPhoneReceiverStatusValidate')->name('user.request_money.check_email_or_phone');
        Route::post('request_payment/accepted', 'RequestPaymentController@requestAccepted')->name('user.request_money.accept.confirm');
        Route::post('request_payment/accept-money-confirm', 'RequestPaymentController@requestAcceptedConfirm')->name('user.request_money.accept.success');
        Route::post('request-money-confirm', 'RequestPaymentController@requestMoneyConfirm')->name('user.request_money.confirm');
    });

    // Merchants
    Route::group(['middleware' => ['permission:manage_merchant']], function () {
        Route::get('merchants', 'MerchantController@index')->name('user.merchants.index');
        Route::get('merchant/detail/{id}', 'MerchantController@detail')->name('user.merchants.details');
        Route::get('merchant/payments', 'MerchantController@payments')->name('user.merchants.payments');
        Route::get('merchant/add', 'MerchantController@add')->name('user.merchants.create');
        Route::post('merchant/store', 'MerchantController@store')->name('user.merchants.store');
        Route::get('merchant/edit/{id}', 'MerchantController@edit')->name('user.merchants.edit');
        Route::post('merchant/update', 'MerchantController@update')->name('user.merchants.update');

        // QR Code - starts
        Route::post('merchant/generate-or-update-standard-merchant-payment-qrcode', 'MerchantController@generateStandardMerchantPaymentQrCode')->name('user.standard_merchant.payment_qrcode');
        Route::get('merchant/get-express-merchant-qr-code', 'MerchantController@getExpressMerchantQrCode')->name('user.express_merchant.get_qrcode');
        Route::post('merchant/generate-or-update-express-merchant-qr-code', 'MerchantController@generateOrUpdateExpressMerchantQrCode')->name('user.express_merchant.qrcode');
        Route::get('merchant/qr-code-print/{id}/{printQrCode}', 'MerchantController@printMerchantQrCode');
    });

    // Disputes
    Route::group(['middleware' => ['permission:manage_dispute']], function () {
        Route::get('disputes', 'DisputeController@index')->name('user.disputes.index');
        Route::get('dispute/add/{id}', 'DisputeController@add')->name('user.disputes.create');
        Route::post('dispute/open', 'DisputeController@store')->name('user.disputes.store');
        Route::get('dispute/discussion/{id}', 'DisputeController@discussion')->name('user.disputes.discussion');
        Route::post('dispute/reply', 'DisputeController@storeReply')->name('user.disputes.reply.store');
        Route::post('dispute/change_reply_status', 'DisputeController@changeReplyStatus')->name('user.disputes.change_status');
        Route::get('dispute/download/{file}', 'DisputeController@download')->name('user.disputes.download');
    });

    // Tickets
    Route::group(['middleware' => ['permission:manage_ticket']], function () {
        Route::get('tickets', 'TicketController@index')->name('user.tickets.index');
        Route::get('ticket/add', 'TicketController@create')->name('user.tickets.create');
        Route::post('ticket/store', 'TicketController@store')->name('user.tickets.store');
        Route::get('ticket/reply/{id}', 'TicketController@reply')->name('user.tickets.reply');
        Route::post('ticket/reply_store', 'TicketController@reply_store')->name('user.tickets.reply.store');
        Route::post('ticket/change_reply_status', 'TicketController@changeReplyStatus')->name('user.tickets.change_status');
        Route::get('ticket/download/{file}', 'TicketController@download')->name('user.tickets.download');
    });

});

/* Merchant Payment Start*/
Route::match(array('GET', 'POST'), 'payment/form', 'MerchantPaymentController@index')->name('user.merchant.payment_form');
Route::get('payment/method-form', 'MerchantPaymentController@showPaymentForm')->name('user.merchant.show_payment_form');
Route::get('payment/success', 'MerchantPaymentController@success')->name('merchant.payment.success');
Route::get('payment/fail', 'MerchantPaymentController@fail');

//paymoney
Route::post('payment/mts_pay', 'MerchantPaymentController@mtsPayment');

//stripe
Route::post('payment/stripe', 'MerchantPaymentController@stripePayment');
Route::post('standard-merchant/stripe-make-payment', 'MerchantPaymentController@stripeMakePayment');

//paypal
Route::POST('payment/paypal_payment_success', 'MerchantPaymentController@paypalPaymentSuccess');

//payumoney
Route::post('payment/payumoney', 'MerchantPaymentController@payumoney');
Route::post('payment/payumoney_success', 'MerchantPaymentController@payuPaymentSuccess');
Route::post('payment/payumoney_fail', 'MerchantPaymentController@merchantPayumoneyPaymentFail');

//CoinPayments
Route::post('payment/coinpayments', 'MerchantPaymentController@coinPayments');
Route::post('payment/coinpayments/make-transaction', 'MerchantPaymentController@coinPaymentMakeTransaction');
Route::get('payment/coinpayments/coinpayment-transaction-info', 'MerchantPaymentController@viewCoinpaymentTransactionInfo');

/* PayMoney Merchant API Start*/
Route::post('merchant/api/verify', 'ExpressMerchantPaymentController@verifyClient');
Route::match(array('GET', 'POST'), 'merchant/payment', 'ExpressMerchantPaymentController@generatedUrl');
Route::post('merchant/api/transaction-info', 'ExpressMerchantPaymentController@storeTransactionInfo');
Route::get('merchant/payment/cancel', 'ExpressMerchantPaymentController@cancelPayment');

Route::group(['middleware' => ['guest:users']], function () {
    Route::get('merchant/payment/confirm', 'ExpressMerchantPaymentController@confirmPayment');
});


Route::get('gateway-payment/success', 'MerchantPaymentController@paymentSuccess')->name('gateway.payment.success');

Route::get('gateway/payment/{gateway}', 'GatewayController@pay')->name('gateway.pay');
Route::post('gateway/confirm-payment', 'GatewayController@confirmPayment')->name('gateway.confirm_payment');
Route::match(array('GET', 'POST'), 'gateway/payment-verify/{gateway}', 'GatewayController@verify')->name('gateway.payment_verify');
Route::match(array('GET', 'POST'), 'gateway/payment-cancel/{gateway}', 'GatewayController@cancelPayment')->name('gateway.payment_cancel');




Route::post('deposit/bank-payment/get-bank-detail', 'Users\DepositController@getBankDetailOnChange')->name('user.deposit.bank.details');
Route::get('deposit/success', 'Users\DepositController@depositSuccess')->name('user.deposit.success');
Route::get('deposit/complete', 'Users\DepositController@depositComplete')->name('deposit.complete');
Route::get('deposit-money/print/{id}', 'Users\DepositController@depositPrintPdf')->name('user.deposit.print');

Route::get('coinpayment/summery', 'Users\DepositController@coinPaymentSummary')->name('coinpayment.summery');



Route::get('download/package', 'ContentController@downloadPackage');
Route::get('{url}', 'ContentController@pageDetail');

