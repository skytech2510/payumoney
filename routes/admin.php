<?php

use App\Http\Helpers\Common;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Unauthenticated Admin
Route::group(['middleware' => ['no_auth:admin', 'locale', 'ip_middleware']], function ()
{
    Route::get('/', function ()
    {
        return view('admin.auth.login');
    })->name('admin');

    Route::post('adminlog', 'AdminController@authenticate');
    Route::view('forget-password', 'admin.auth.forgetPassword');
    Route::post('forget-password', 'AdminController@forgetPassword');
    Route::get('password/resets/{token}', 'AdminController@verifyToken');
    Route::post('confirm-password', 'AdminController@confirmNewPassword');
});

// Authenticated Admin
Route::group(['middleware' => ['guest:admin', 'locale', 'ip_middleware']], function ()
{
    Route::get('/clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        (new Common())->one_time_message('success',__('Cache successfully cleared.'));
        return back();
    })->name('clear.cache');

    Route::get('home', 'DashboardController@index')->name('dashboard');
    Route::get('adminlogout', 'AdminController@logout');
    Route::get('profile', 'AdminController@profile');
    Route::post('update-admin/{id}', 'AdminController@update');

    Route::get('change-password', 'AdminController@changePassword');
    Route::post('change-password', 'AdminController@updatePassword');

    Route::post('check-password', 'AdminController@passwordCheck');

    // Change language
    Route::post('change-lang', 'DashboardController@switchLanguage');

    // users
    Route::get('users', 'UserController@index')->middleware(['permission:view_user']);
    Route::get('users/create', 'UserController@create')->middleware(['permission:add_user']);
    Route::post('users/store', 'UserController@store');
    Route::get('users/view/{id}', 'UserController@show');
    Route::get('users/edit/{id}', 'UserController@edit')->middleware(['permission:edit_user']);
    Route::post('users/update', 'UserController@update');
    Route::get('users/delete/{id}', 'UserController@destroy')->middleware(['permission:delete_role']);

    Route::post('email_check', 'UserController@postEmailCheck');
    Route::post('duplicate-phone-number-check', 'UserController@duplicatePhoneNumberCheck');
    Route::get('users/transactions/{id}', 'UserController@eachUserTransaction');

    //Admin Can deposit for a user
    Route::match(array('GET', 'POST'), 'users/deposit/create/{id}', 'UserController@eachUserDeposit');
    Route::post('users/deposit/amount-fees-limit-check', 'UserController@amountFeesLimitCheck');
    Route::post('users/deposit/storeFromAdmin', 'UserController@eachUserDepositSuccess');
    Route::get('users/deposit/print/{id}', 'UserController@eachUserdepositPrintPdf');

    //Admin Can withdraw for a user
    Route::match(array('GET', 'POST'), 'users/withdraw/create/{id}', 'UserController@eachUserWithdraw');
    Route::post('users/withdraw/amount-fees-limit-check', 'UserController@amountFeesLimitCheck');
    Route::post('users/withdraw/storeFromAdmin', 'UserController@eachUserWithdrawSuccess');
    Route::get('users/withdraw/print/{id}', 'UserController@eachUserWithdrawPrintPdf');

    Route::get('users/wallets/{id}', 'UserController@eachUserWallet');
    Route::get('users/tickets/{id}', 'UserController@eachUserTicket');
    Route::get('users/disputes/{id}', 'UserController@eachUserDispute');

    // admin_users
    Route::get('admin_users', 'UserController@adminList')->middleware(['permission:view_admins']);
    Route::get('admin-user/create', 'UserController@adminCreate')->middleware(['permission:add_admin']);
    Route::post('admin-users/store', 'UserController@adminStore');
    Route::get('admin-user/edit/{id}', 'UserController@adminEdit')->middleware(['permission:edit_admin']);
    Route::post('admin-users/update', 'UserController@adminUpdate');
    Route::get('admin-user/delete/{id}', 'UserController@adminDestroy')->middleware(['permission:delete_admin']);

    // Merchants
    Route::get('merchants', 'MerchantController@index')->middleware(['permission:view_merchant']);
    Route::get('merchant/edit/{id}', 'MerchantController@edit')->middleware(['permission:edit_merchant']);
    Route::post('merchant/update', 'MerchantController@update');
    Route::post('merchant/logo_delete', 'MerchantController@deleteLogo');
    Route::post('merchant/delete-merchant-logo', 'MerchantController@deleteMerchantLogo');
    Route::get('merchant/payments/{id}', 'MerchantController@eachMerchantPayment');
    Route::get('merchants/userSearch', 'MerchantController@merchantsUserSearch');
    Route::get('merchants/csv', 'MerchantController@merchantCsv');
    Route::get('merchants/pdf', 'MerchantController@merchantPdf');
    Route::post('merchants/change-fee-with-group-change', 'MerchantController@changeMerchantFeeWithGroupChange');

    //Merchant Payments
    Route::get('merchant_payments', 'MerchantPaymentController@index')->middleware(['permission:view_merchant_payment']);
    Route::get('merchant_payments/edit/{id}', 'MerchantPaymentController@edit')->middleware(['permission:edit_merchant_payment']);
    Route::post('merchant_payments/update', 'MerchantPaymentController@update');
    Route::get('merchant_payments/csv', 'MerchantPaymentController@merchantPaymentCsv');
    Route::get('merchant_payments/pdf', 'MerchantPaymentController@merchantPaymentPdf');

    // Transactions
    Route::get('transactions', 'TransactionController@index')->middleware(['permission:view_transaction']);
    Route::get('transactions/edit/{id}', 'TransactionController@edit')->middleware(['permission:edit_transaction']);
    Route::post('transactions/update/{id}', 'TransactionController@update');
    Route::get('transactions_user_search', 'TransactionController@transactionsUserSearch');
    Route::get('transactions/csv', 'TransactionController@transactionCsv');
    Route::get('transactions/pdf', 'TransactionController@transactionPdf');

    // Deposits
    Route::get('deposits', 'DepositController@index')->middleware(['permission:view_deposit']);
    Route::get('deposits/edit/{id}', 'DepositController@edit')->middleware(['permission:edit_deposit']);
    Route::post('deposits/update', 'DepositController@update');
    Route::get('deposits/user_search', 'DepositController@depositsUserSearch');
    Route::get('deposits/csv', 'DepositController@depositCsv');
    Route::get('deposits/pdf', 'DepositController@depositPdf');

    // Withdrawals
    Route::get('withdrawals', 'WithdrawalController@index')->middleware(['permission:view_withdrawal']);
    Route::get('withdrawals/edit/{id}', 'WithdrawalController@edit')->middleware(['permission:edit_withdrawal']);
    Route::post('withdrawals/update', 'WithdrawalController@update');
    Route::get('withdrawals/user_search', 'WithdrawalController@withdrawalsUserSearch');
    Route::get('withdrawals/csv', 'WithdrawalController@withdrawalCsv');
    Route::get('withdrawals/pdf', 'WithdrawalController@withdrawalPdf');

    // Transfers
    Route::get('transfers', 'MoneyTransferController@index')->middleware(['permission:view_transfer']);
    Route::get('transfers/edit/{id}', 'MoneyTransferController@edit')->middleware(['permission:edit_transfer']);
    Route::post('transfers/update', 'MoneyTransferController@update');
    Route::get('transfers/user_search', 'MoneyTransferController@transfersUserSearch');
    Route::get('transfers/csv', 'MoneyTransferController@transferCsv');
    Route::get('transfers/pdf', 'MoneyTransferController@transferPdf');

    // Currency Exchanges
    Route::get('exchanges', 'ExchangeController@index')->middleware(['permission:view_exchange']);
    Route::get('exchange/edit/{id}', 'ExchangeController@edit')->middleware(['permission:edit_exchange']);
    Route::post('exchange/update', 'ExchangeController@update');
    Route::get('exchanges/user_search', 'ExchangeController@exchangesUserSearch');
    Route::get('exchanges/csv', 'ExchangeController@exchangeCsv');
    Route::get('exchanges/pdf', 'ExchangeController@exchangePdf');

    // Request Payments
    Route::get('request_payments', 'RequestPaymentController@index')->middleware(['permission:view_request_payment']);
    Route::get('request_payments/edit/{id}', 'RequestPaymentController@edit')->middleware(['permission:edit_request_payment']);
    Route::post('request_payments/update', 'RequestPaymentController@update');
    Route::get('request_payments/user_search', 'RequestPaymentController@requestpaymentsUserSearch');
    Route::get('request_payments/csv', 'RequestPaymentController@requestpaymentCsv');
    Route::get('request_payments/pdf', 'RequestPaymentController@requestpaymentPdf');

    // Revenues
    Route::get('revenues', 'RevenueController@revenues_list')->middleware(['permission:view_revenue']);
    Route::get('revenues/user_search', 'RevenueController@revenuesUserSearch');
    Route::get('revenues/csv', 'RevenueController@revenueCsv');
    Route::get('revenues/pdf', 'RevenueController@revenuePdf');

    // disputes
    Route::get('disputes', 'DisputeController@index')->middleware(['permission:view_disputes']);

    Route::get('dispute/add/{id}', 'DisputeController@add');
    Route::post('dispute/open', 'DisputeController@store');

    Route::get('dispute/discussion/{id}', 'DisputeController@discussion')->middleware(['permission:edit_dispute']);
    Route::post('dispute/reply', 'DisputeController@storeReply');
    Route::post('dispute/change_reply_status', 'DisputeController@changeReplyStatus');
    Route::get('disputes_user_search', 'DisputeController@disputesUserSearch');
    Route::get('disputes_transactions_search', 'DisputeController@disputesTransactionsSearch');
    Route::get('dispute/download/{file}', 'DisputeController@download');

    // Tickets
    Route::get('tickets/list', 'TicketController@index')->middleware(['permission:view_tickets']);
    Route::get('tickets/add', 'TicketController@create')->middleware(['permission:add_ticket']);
    Route::post('tickets/store', 'TicketController@store');
    Route::get('ticket_user_search', 'TicketController@ticketUserSearch');
    Route::get('tickets/reply/{id}', 'TicketController@reply')->middleware(['permission:edit_ticket']);
    Route::post('tickets/change_ticket_status', 'TicketController@change_ticket_status');
    Route::post('tickets/reply/store', 'TicketController@adminTicketReply');
    Route::post('tickets/reply/update', 'TicketController@replyUpdate');
    Route::post('tickets/reply/delete', 'TicketController@replyDelete');
    Route::get('tickets/edit/{id}', 'TicketController@edit')->middleware(['permission:edit_ticket']);
    Route::post('tickets/update', 'TicketController@update');
    Route::get('tickets/delete/{id}', 'TicketController@delete')->middleware(['permission:delete_ticket']);
    Route::get('ticket/download/{file}', 'TicketController@download');

    // Email Templates
    Route::get('template/{alias?}', 'EmailTemplateController@index')->middleware(['permission:view_email_template'])->name('email.template.index');
    Route::post('template_update/{alias}', 'EmailTemplateController@update')->middleware(['permission:edit_email_template'])->name('email.template.update');

    Route::get('sms-template/{alias?}', 'SmsTemplateController@index')->middleware(['permission:view_sms_template'])->name('sms.template.index');
    Route::post('sms-template/update/{alias}', 'SmsTemplateController@update')->middleware(['permission:edit_sms_template'])->name('sms.template.update');

    // Activity Logs
    Route::get('activity_logs', 'ActivityLogController@activities_list')->middleware(['permission:view_activity_log']);

    // Verifications - identity-proofs
    Route::get('identity-proofs', 'IdentityProofController@index')->middleware(['permission:view_identity_verfication']);
    Route::get('identity-proofs/csv', 'IdentityProofController@identityProofsCsv');
    Route::get('identity-proofs/pdf', 'IdentityProofController@identityProofsPdf');
    Route::get('identity-proofs/edit/{id}', 'IdentityProofController@identityProofEdit')->middleware(['permission:edit_identity_verfication']);
    Route::post('identity-proofs/update', 'IdentityProofController@identityProofUpdate');

    // Verifications - address-proofs
    Route::get('address-proofs', 'AddressProofController@index')->middleware(['permission:view_address_verfication']);
    Route::get('address-proofs/csv', 'AddressProofController@addressProofsCsv');
    Route::get('address-proofs/pdf', 'AddressProofController@addressProofsPdf');
    Route::get('address-proofs/edit/{id}', 'AddressProofController@addressProofEdit')->middleware(['permission:edit_address_verfication']);
    Route::post('address-proofs/update', 'AddressProofController@addressProofUpdate');

    // currencies
    Route::get('settings/currency', 'CurrencyController@index')->middleware(['permission:view_currency']);
    Route::match(array('GET', 'POST'), 'settings/add_currency', 'CurrencyController@add')->middleware(['permission:add_currency']);
    Route::match(array('GET', 'POST'), 'settings/edit_currency/{id}', 'CurrencyController@update')->middleware(['permission:edit_currency']);
    Route::get('settings/delete_currency/{id}', 'CurrencyController@delete')->middleware(['permission:delete_currency']);
    Route::post('currency/image_delete', 'CurrencyController@deleteImage');
    Route::post('settings/currency/delete-currency-logo', 'CurrencyController@deleteCurrencyLogo');

    // FeesLimit
    Route::get('settings/feeslimit/{tab}/{id}', 'FeesLimitController@limitList')->middleware(['permission:edit_currency']);
    Route::post('settings/get-feeslimit-details', 'FeesLimitController@getFesslimitDetails');
    Route::post('settings/feeslimit/update-deposit-limit', 'FeesLimitController@updateDepositLimit');
    Route::post('settings/get-specific-currency-details', 'FeesLimitController@getSpecificCurrencyDetails');

    //Currency PaymentMethod Settings
    Route::get('settings/payment-methods/{tab}/{id}', 'CurrencyPaymentMethodController@paymentMethodList')->middleware(['permission:edit_currency']);
    Route::post('settings/payment-methods/update-paymentMethod-Credentials', 'CurrencyPaymentMethodController@updatePaymentMethodCredentials');
    Route::post('settings/get-payment-methods-details', 'CurrencyPaymentMethodController@getPaymentMethodsDetails');
    Route::post('settings/get-payment-methods-specific-currency-details', 'CurrencyPaymentMethodController@getPaymentMethodsSpecificCurrencyDetails');

    //bank
    Route::post('settings/payment-methods/add-bank', 'CurrencyPaymentMethodController@addBank');
    Route::post('settings/payment-methods/update-bank', 'CurrencyPaymentMethodController@updateBank');
    Route::post('settings/payment-methods/delete-bank', 'CurrencyPaymentMethodController@deleteBank');
    Route::post('settings/payment-methods/getCpmId', 'CurrencyPaymentMethodController@getCpmId');
    Route::post('settings/payment-methods/show-bank-details', 'CurrencyPaymentMethodController@showbankDetails');
    Route::post('settings/payment-methods/delete-bank-logo', 'CurrencyPaymentMethodController@deleteBankLogo');

    //settings
    Route::match(array('GET', 'POST'), 'settings', 'SettingController@general');

    Route::post('settings/update-sidebar-company-logo', 'SettingController@updateSideBarCompanyLogo');
    Route::post('settings/logo-delete', 'SettingController@deleteLogo');
    Route::post('settings/logo-delete', 'SettingController@deleteLogo');
    Route::post('settings/check-sms-settings', 'SettingController@checkSmsGatewaySettings');
    Route::post('settings/delete-logo', 'SettingController@deleteSettingLogo');
    Route::post('settings/delete-favicon', 'SettingController@deleteSettingFavicon');

    // Admin Security Settings
    Route::match(['get', 'post'], 'settings/admin-security-settings', 'SettingController@adminSecuritySettings')->middleware(['permission:view_admin_security']);

    //social_links
    Route::match(array('GET', 'POST'), 'settings/social_links', 'SettingController@social_links')->middleware(['permission:view_social_links']);

    Route::get('settings/theme-set/{theme}', 'SettingController@themeSet')->middleware(['permission:view_social_links']);

    //api_informations
    Route::match(array('GET', 'POST'), 'settings/api_informations', 'SettingController@api_informations')->middleware(['permission:view_api_credentials']);

    // currency conversion rate api
    Route::match(array('GET', 'POST'), 'settings/currency-conversion-rate-api', 'SettingController@currencyConversionRateApi')->middleware(['permission:view_conversion_rate_api']);

    //appstore credentials
    Route::get('settings/app-store-credentials', 'AppStoreCredentialController@getAppStoreCredentials')->middleware(['permission:view_appstore_credentials']);
    Route::post('settings/app-store-credentials/update-google-credentials', 'AppStoreCredentialController@updateGoogleCredentials');
    Route::post('settings/app-store-credentials/update-apple-credentials', 'AppStoreCredentialController@updateAppleCredentials');
    Route::post('settings/app-store-credentials/delete-playstore-logo', 'AppStoreCredentialController@deletePlaystoreLogo');
    Route::post('settings/app-store-credentials/delete-appstore-logo', 'AppStoreCredentialController@deleteAppStoreLogo');

    //email_settings
    Route::match(array('GET', 'POST'), 'settings/email', 'SettingController@email')->middleware(['permission:view_email_setting']);

    // Route::match(array('GET', 'POST'), 'settings/sms', 'SettingController@sms')->middleware(['permission:view_sms_setting']);

    Route::match(array('GET', 'POST'), 'settings/sms/{type}', 'SettingController@sms')->middleware(['permission:view_sms_setting']);

    //countries
    Route::get('settings/country', 'CountryController@index')->middleware(['permission:view_country']);
    Route::match(array('GET', 'POST'), 'settings/add_country', 'CountryController@add')->middleware(['permission:add_country']);
    Route::match(array('GET', 'POST'), 'settings/edit_country/{id}', 'CountryController@update')->middleware(['permission:edit_country']);
    Route::get('settings/delete_country/{id}', 'CountryController@delete')->middleware(['permission:delete_country']);

    //languages
    Route::get('settings/language', 'LanguageController@index')->middleware(['permission:view_language']);
    Route::match(array('GET', 'POST'), 'settings/add_language', 'LanguageController@add')->middleware(['permission:add_language']);
    Route::match(array('GET', 'POST'), 'settings/edit_language/{id}', 'LanguageController@update')->middleware(['permission:edit_language']);
    Route::get('settings/delete_language/{id}', 'LanguageController@delete')->middleware(['permission:delete_language']);
    Route::post('settings/language/delete-flag', 'LanguageController@deleteFlag');

    //Merchant Group/Roles
    Route::get('settings/merchant-group', 'MerchantGroupController@index')->middleware(['permission:view_merchant_group']);
    Route::match(array('GET', 'POST'), 'settings/add-merchant-group', 'MerchantGroupController@add')->middleware(['permission:add_merchant_group']);
    Route::match(array('GET', 'POST'), 'settings/edit-merchant-group/{id}', 'MerchantGroupController@update')->middleware(['permission:edit_merchant_group']);
    Route::get('settings/delete-merchant-group/{id}', 'MerchantGroupController@delete')->middleware(['permission:delete_merchant_group']);

    //User Group/Roles
    Route::get('settings/user_role', 'UsersRoleController@index')->middleware(['permission:view_group']);
    Route::match(array('GET', 'POST'), 'settings/add_user_role', 'UsersRoleController@add')->middleware(['permission:add_group']);
    Route::match(array('GET', 'POST'), 'settings/edit_user_role/{id}', 'UsersRoleController@update')->middleware(['permission:edit_group']);
    Route::get('settings/delete_user_role/{id}', 'UsersRoleController@delete')->middleware(['permission:delete_group']);
    Route::get('settings/roles/check-user-permissions', 'UsersRoleController@checkUserPermissions');

    //Admin Group/Roles
    Route::get('settings/roles', 'RoleController@index')->middleware(['permission:view_role']);
    Route::match(array('GET', 'POST'), 'settings/add_role', 'RoleController@add')->middleware(['permission:add_role']);
    Route::match(array('GET', 'POST'), 'settings/edit_role/{id}', 'RoleController@update')->middleware(['permission:edit_role']);
    Route::get('settings/delete_role/{id}', 'RoleController@delete')->middleware(['permission:delete_role']);
    Route::post('settings/roles/duplicate-role-check', 'RoleController@duplicateRoleCheck');

    //Database Backup
    Route::get('settings/backup', 'BackupController@index')->middleware(['permission:view_database_backup']);
    Route::get('backup/save', 'BackupController@add')->middleware(['permission:add_database_backup']);
    Route::get('backup/download/{id}', 'BackupController@download')->middleware(['permission:edit_database_backup']);

    //metas
    Route::get('settings/metas', 'MetaController@index')->middleware(['permission:view_meta']);
    Route::match(array('GET', 'POST'), 'settings/edit_meta/{id}', 'MetaController@update')->middleware(['permission:edit_meta']);

    //Pages
    Route::get('settings/pages', 'PagesController@index')->middleware(['permission:view_page']);
    Route::get('settings/page/add', 'PagesController@add')->middleware(['permission:add_page']);
    Route::post('settings/page/store', 'PagesController@store');
    Route::get('settings/page/edit/{id}', ['uses' => 'PagesController@edit', 'as' => 'admin.page.edit'])->middleware(['permission:edit_page']);
    Route::post('settings/page/update', 'PagesController@update');
    Route::get('settings/page/delete/{id}', 'PagesController@delete')->middleware(['permission:delete_page']);

    //Preferences
    Route::get('settings/preference', 'SettingController@preference')->middleware(['permission:view_preference']);
    Route::post('save-preference', 'SettingController@savePreference')->middleware(['permission:edit_preference']);

    //Enable Woocommerce
    Route::match(array('GET', 'POST'), 'settings/enable-woocommerce', 'SettingController@enableWoocommerce')->middleware(['permission:view_enable_woocommerce']);

    //Notifications
    Route::get('settings/notification-types', 'NotificationTypeController@index')->middleware(['permission:view_notification_type']);
    Route::get('settings/notification-types/edit/{id}', 'NotificationTypeController@edit')->middleware(['permission:edit_notification_type']);
    Route::post('settings/notification-types/update/{id}', 'NotificationTypeController@update')->middleware(['permission:edit_notification_type']);
    Route::post('settings/notification-type-name/check', 'NotificationTypeController@uniqueNotificationTypeNameCheck');
    Route::get('settings/notification-settings/{type}', 'NotificationSettingController@index')->middleware(['permission:view_notification_setting']);
    Route::post('settings/notification-settings/update', 'NotificationSettingController@update')->middleware(['permission:edit_notification_setting']);

    // Addon
    Route::match(array('GET', 'POST'), '/custom/addons', 'AddonController@index');
    Route::get('/custom/addon/activation/{status}/{id}', 'AddonController@activation');

    // ModuleManager
    Route::get('module-manager/addons', 'ModuleManagerController@index')->middleware(['permission:view_addon_manager']);

    // Crypto Providers
    Route::get('crypto-providers/{provider?}', 'CryptoProviderController@index')->name('admin.crypto_providers.list')->middleware(['permission:view_crypto_provider']);
    Route::post('crypto-provider/{provider}/status-change', 'CryptoProviderController@statusChange')->name('admin.crypto_providers.status_change')->middleware(['permission:edit_crypto_provider']);

    // System info
    Route::get('system-info', 'SystemInfoController@index')->name('systemInfo.index');
});