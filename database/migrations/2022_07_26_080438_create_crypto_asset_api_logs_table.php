<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoAssetApiLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_asset_api_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payment_method_id')->unsigned()->index();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('object_id')->index('crypto_asset_api_logs_object_id_idx')->comment('wallet_id or cryto_sent_id or crypto_received_id');
            $table->string('object_type', 20)->index('crypto_asset_api_logs_object_type_idx')->commnet('wallet_address, crypto_sent, crypto_received');
            $table->string('network', 10)->index('crypto_asset_api_logs_network_idx')->comment('Networks/Cryto Curencies - BTC,LTC,DOGE');
            $table->text('payload')->comment('Crypto Api\'s Payloads (e.g - get_new_address(), get_balance(), withdraw(),etc.');
            $table->integer('confirmations')->index('crypto_asset_api_logs_confirmations_idx')->default(0);
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
        Schema::dropIfExists('crypto_asset_api_logs');
    }
}
