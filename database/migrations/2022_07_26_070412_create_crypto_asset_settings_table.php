<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoAssetSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_asset_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('currency_id')->unique()->unsigned()->index();
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('crypto_provider_id')->unsigned()->index('crypto_asset_settings_crypto_provider_id_idx');
            $table->foreign('crypto_provider_id')->references('id')->on('crypto_providers')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('payment_method_id')->unsigned()->index('crypto_asset_settings_payment_method_id_idx');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onUpdate('cascade')->onDelete('cascade');
            $table->string('network', 30)->unique()->index('crypto_asset_settings_network_idx')->comment('Networks/Cryto Curencies - BTC,LTC,DT etc.');
            $table->text('network_credentials');
            $table->string('status', 11)->default('Active')->comment('Active/Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crypto_asset_settings');
    }
}
