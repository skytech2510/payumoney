<?php

// crypto-notification Webhook
Route::post('receive/blockio-balance-change-notification', 'BlockIoNotificationController@balanceNotification');
// For development
Route::get('receive/blockio-balance-change-notification-development', 'BlockIoNotificationController@balanceNotificationDevelopment');

Route::group(config('addons.route_group.authenticated.admin'), function()
{
    // Crypto Send Transactions details
    Route::get('crypto-sent-transactions', 'Admin\CryptoSentTransactionController@index')->name('admin.crypto_sent_transaction.index')->middleware('permission:view_crypto_transactions');
    Route::get('crypto-sent-transactions/csv', 'Admin\CryptoSentTransactionController@cryptoSentTransactionsCsv')->name('admin.crypto_sent_transaction.csv');
    Route::get('crypto-sent-transactions/pdf', 'Admin\CryptoSentTransactionController@cryptoSentTransactionsPdf')->name('admin.crypto_sent_transaction.pdf');
    Route::get('crypto-sent-transactions/view/{id}', 'Admin\CryptoSentTransactionController@view')->name('admin.crypto_sent_transaction.view')->middleware('permission:view_crypto_transactions');
    Route::get('crypto-sent-transactions/search/user', 'Admin\CryptoSentTransactionController@cryptoSentTransactionsSearchUser')->name('admin.crypto_sent_transaction.search_user');

    // Crypto Receive Transactions
    Route::get('crypto-received-transactions', 'Admin\CryptoReceivedTransactionController@index')->name('admin.crypto_received_transaction.index')->middleware('permission:view_crypto_transactions');
    Route::get('crypto-received-transactions/csv', 'Admin\CryptoReceivedTransactionController@cryptoReceivedTransactionsCsv')->name('admin.crypto_received_transaction.csv');
    Route::get('crypto-received-transactions/pdf', 'Admin\CryptoReceivedTransactionController@cryptoReceivedTransactionsPdf')->name('admin.crypto_received_transaction.pdf');
    Route::get('crypto-received-transactions/view/{id}', 'Admin\CryptoReceivedTransactionController@view')->name('admin.crypto_received_transaction.view')->middleware('permission:view_crypto_transactions');
    Route::get('crypto-received-transactions/search/user', 'Admin\CryptoReceivedTransactionController@cryptoReceivedTransactionsSearchUser')->name('admin.crypto_received_transaction.search_user');

    // Asset Settings
    Route::get('blockio/create', 'Admin\BlockIoAssetSettingController@create')->name('admin.blockio_asset.create')->middleware('permission:add_crypto_asset');
    Route::post('blockio/store', 'Admin\BlockIoAssetSettingController@store')->name('admin.blockio_asset.store')->middleware('permission:add_crypto_asset');
    Route::get('blockio/edit/{network}', 'Admin\BlockIoAssetSettingController@edit')->name('admin.blockio_asset.edit')->middleware('permission:edit_crypto_asset');
    Route::post('blockio/update/{network}', 'Admin\BlockIoAssetSettingController@update')->name('admin.blockio_asset.update')->middleware('permission:edit_crypto_asset');
    Route::get('blockio/check-duplicate-network', 'Admin\BlockIoAssetSettingController@checkDuplicateNetwork')->name('admin.blockio_asset.check_duplicate_network');
    Route::get('blockio/crypto-address-validity-check', 'Admin\BlockIoAssetSettingController@validateCryptoAddress')->name('admin.blockio_asset.validate_crypto_address');
    Route::get('blockio/check-merhant-network-address', 'Admin\BlockIoAssetSettingController@checkMerchantNetworkAddress')->name('admin.blockio_asset.check_merchant_address');
    Route::post('blockio/asset-status-change', 'Admin\BlockIoAssetSettingController@assetStatusChange')->name('admin.blockio_asset.status_change');
    Route::get('blockio/validate-address', 'Admin\BlockIoAssetSettingController@validateAddress')->name('admin.blockio_asset.validate_address');
    Route::get('blockio/address-list-details/{network}', 'Admin\BlockIoAssetSettingController@addressListDetails')->name('admin.blockio_asset.address_list');
    Route::get('blockio/current-price-list/{network}', 'Admin\BlockIoAssetSettingController@currentParentList')->name('admin.blockio_asset.current_price');

    // Admin Crypto Send
    Route::get('blockio/crypto-send/initiate/{code}', 'Admin\CryptoSendReceiveConroller@eachUserCryptoSentCreate')->name('admin.crypto_send.create');
    Route::post('blockio/crypto-send/confirm', 'Admin\CryptoSendReceiveConroller@eachUserCryptoSentConfirm')->name('admin.crypto_send.confirm');
    Route::post('blockio/crypto-send/success', 'Admin\CryptoSendReceiveConroller@eachUserCryptoSentSuccess')->name('admin.crypto_send.success');
    Route::get('blockio/crypto-send/get-merchant-user-network-address-with-merchant-balance', 'Admin\CryptoSendReceiveConroller@getMerchantUserNetworkAddressWithMerchantBalance')->name('admin.crypto.address_balance');
    Route::get('blockio/crypto-send/validate-merchant-address-balance', 'Admin\CryptoSendReceiveConroller@validateMerchantAddressBalanceAgainstAmount')->name('admin.crypto.validate_balance');

    // Admin Crypto Receive
    Route::get('blockio/crypto-receive/initiate/{code}', 'Admin\CryptoSendReceiveConroller@eachUserCryptoReceiveCreate')->name('admin.crypto_receive.create');
    Route::post('blockio/crypto-receive/confirm', 'Admin\CryptoSendReceiveConroller@eachUserCryptoReceiveConfirm')->name('admin.crypto_receive.confirm');
    Route::post('blockio/crypto-receive/success', 'Admin\CryptoSendReceiveConroller@eachUserCryptoReceiveSuccess')->name('admin.crypto_receive.success');
    Route::get('blockio/crypto-receive/get-user-network-address-balance-with-merchant-address', 'Admin\CryptoSendReceiveConroller@getUserNetworkAddressBalanceWithMerchantNetworkAddress')->name('admin.crypto_receive.network_balance');
    Route::get('blockio/crypto-receive/validate-user-address-balance', 'Admin\CryptoSendReceiveConroller@validateUserAddressBalanceAgainstAmount')->name('admin.crypto_receive.validate_balance');

    // Admin crypto send-receive print pdf
     Route::get('blockio/crypto/send-receive/print/{id}', 'Admin\CryptoSendReceiveConroller@merchantCryptoSentReceivedTransactionPrintPdf')->name('admin.crypto_send_receive.print');
});

Route::group(config('addons.route_group.authenticated.user'), function()
{
    // Crypto Send
    Route::prefix('crypto/send/blockio')->as('user.crypto_send.')->namespace('Users')->middleware('permission:manage_crypto_send_receive')->group(function() {
        Route::get('validate-address', 'CryptoSendController@validateCryptoAddress')->name('validate_address');
        Route::get('validate-user-balance', 'CryptoSendController@validateUserBalanceAgainstAmount')->name('validate_balance');
        Route::post('success', 'CryptoSendController@sendCryptoSuccess')->name('success');
        Route::post('confirm', 'CryptoSendController@sendCryptoConfirm')->name('confirm');
        Route::get('{walletCurrencyCode}/{walletId}', 'CryptoSendController@sendCryptoCreate')->name('create');
    });

    // Crypto Receive
    Route::get('crypto/receive/blockio/{walletCurrencyCode}/{walletId}', 'Users\CryptoReceiveController@receiveCrypto')->name('user.crypto_receive.create')->middleware(['permission:manage_crypto_send_receive']);

    // Crypto send receive print pdf
    Route::get('transactions/crypto-sent-received-print/{id}', 'Users\CryptoReceiveController@cryptoSentReceivedTransactionPrintPdf')->name('user.crypto_send_receive.print');
});