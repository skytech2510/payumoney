<?php

namespace App\Providers;

use App\Models\{TransactionType, PaymentMethod};
use Illuminate\Support\ServiceProvider;
use TechVill\Theme\Facades\Theme;
use Config, View, Schema;

class AppServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
        header('x-powered-by:');
        Schema::defaultStringLength(191);

        if (!defined('BLOCKIO_API_VERSION')) define('BLOCKIO_API_VERSION', 2);

        if (env('APP_INSTALL') == true) {
            
            $transactionTypes = TransactionType::all()->toArray();
            foreach ($transactionTypes as $transactionType) {
                if (!defined($transactionType['name'])) define($transactionType['name'], $transactionType['id']);
            }

            $paymentMethods = PaymentMethod::all()->toArray();
            foreach( $paymentMethods as $paymentMethod) {
                if (!defined($paymentMethod['name'])) define($paymentMethod['name'], $paymentMethod['id']);
            }

            $adminUrlPrefix = preference('admin_url_prefix');
            if (!empty($adminUrlPrefix)) {
                Config::set(['adminPrefix' => $adminUrlPrefix]);
                View::share('adminPrefix', $adminUrlPrefix);
            }
        }
    }

    public function register()
    {
    }
}
