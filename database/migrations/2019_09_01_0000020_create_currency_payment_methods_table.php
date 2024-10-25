<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_payment_methods', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('currency_id')->unsigned()->index();
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('method_id')->unsigned()->index();
            $table->foreign('method_id')->references('id')->on('payment_methods')->onUpdate('cascade')->onDelete('cascade');

            $table->text('activated_for')->nullable()->comment('transaction types like deposit, withdrawal, investment etc.');
            $table->string('method_data', 255)->comment('input field\'s title and value like client_id, client_secret etc');

            $table->tinyInteger('processing_time')->default(0)->comment('time in days'); //added default to 0 for banks

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
        Schema::dropIfExists('currency_payment_methods');
    }
}
