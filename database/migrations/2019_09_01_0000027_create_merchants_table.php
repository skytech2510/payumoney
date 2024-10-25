<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantsTable extends Migration
{
    public function up()
    {
        Schema::create('merchants', function (Blueprint $table)
        {
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('currency_id')->unsigned()->index()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('merchant_group_id')->unsigned()->index()->nullable();
            $table->foreign('merchant_group_id')->references('id')->on('merchant_groups')->onUpdate('cascade')->onDelete('cascade');

            $table->string('merchant_uuid', 13)->nullable()->comment = "Unique ID for each Merchant";
            $table->string('business_name');
            $table->string('site_url', 100);
            $table->string('type', 11)->comment('standard or express');
            $table->string('note', 255);
            $table->string('logo', 100)->nullable();
            $table->decimal('fee', 20, 8)->nullable()->default(0.00000000);
            $table->string('status', 12)->default('Moderation')->comment('Moderation, Disapproved, Approved');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchants');
    }
}
