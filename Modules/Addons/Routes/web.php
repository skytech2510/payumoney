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

Route::group(config('addons.route_group.authenticated.admin'), function() {
    Route::post('addon/upload', 'AddonsController@upload')->name('addon.upload');
    Route::get('addon/switch-status/{alias}', 'AddonsController@switchStatus')->name('addon.switch-status');
    Route::get('addon/verify/{name}', 'AddonsController@verifyForm')->name('addon.verify');
    Route::post('addon/verify/{name}', 'AddonsController@verifyUsernamePurchasecode')->name('addon.verify');
});
