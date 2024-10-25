<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawal_details', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('withdrawal_id')->unsigned()->index()->nullable();
            $table->foreign('withdrawal_id')->references('id')->on('withdrawals')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('type')->comment = "2=Paypal, 5=Bank, 8=Crypto";
            $table->string('email', 191)->nullable();
            $table->string('crypto_address', 191)->nullable();
            $table->string('account_name',191)->nullable();
            $table->string('account_number',191)->nullable();
            $table->string('bank_branch_name',191)->nullable();
            $table->string('bank_branch_city',191)->nullable();
            $table->string('bank_branch_address', 191)->nullable();
            $table->integer('country')->unsigned()->nullable();
            $table->string('swift_code', 191)->nullable();
            $table->string('bank_name', 191)->nullable();
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
        Schema::dropIfExists('withdrawal_details');
    }
}
