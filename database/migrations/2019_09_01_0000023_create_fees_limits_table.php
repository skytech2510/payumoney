<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeesLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees_limits', function (Blueprint $table)
        {
            $table->increments('id');

            $table->integer('currency_id')->unsigned()->index()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('transaction_type_id')->unsigned()->nullable();
            $table->foreign('transaction_type_id')->references('id')->on('transaction_types')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('payment_method_id')->unsigned()->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onUpdate('cascade')->onDelete('cascade');

            $table->decimal('charge_percentage', 20, 8)->default(0.00000000);
            $table->decimal('charge_fixed', 20, 8)->default(0.00000000);
            $table->decimal('min_limit', 20, 8)->default(1.00000000);
            $table->decimal('max_limit', 20, 8)->nullable();
            $table->string('processing_time', 4)->nullable()->default('0')->comment('time in days'); 
            $table->string('has_transaction', 3)->comment('Yes or No');
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
        Schema::dropIfExists('fees_limits');
    }
}
