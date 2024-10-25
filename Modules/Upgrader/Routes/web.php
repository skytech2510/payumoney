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
    Route::match(['GET', 'POST'], 'system-update', 'SystemUpdateController@upgrade')->name('systemUpdate.upgrade');
    Route::get('version/check', 'SystemUpdateController@checkVersion')->name('version.check');
    Route::post('version/download', 'SystemUpdateController@downloadVersion')->name('version.download');

});
