<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayoutSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payout_settings', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('type');
            $table->string('email', 191)->nullable();
            $table->integer('currency_id')->nullable();
            $table->string('crypto_address', 191)->nullable();
            $table->string('account_name',191)->nullable();
            $table->string('account_number',191)->nullable();
            $table->string('bank_branch_name',191)->nullable();
            $table->string('bank_branch_city',191)->nullable();
            $table->string('bank_branch_address', 191)->nullable();
            $table->integer('country')->unsigned()->nullable();
            $table->string('swift_code', 191)->nullable();
            $table->string('bank_name', 191)->nullable();
            $table->tinyInteger('default_payout')->comment('0=not default, 1=default')->default('0');
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
        Schema::dropIfExists('payout_settings');
    }
}
