<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table)
        {
            $table->increments('id');

            $table->integer('claimant_id')->unsigned()->index()->nullable();
            $table->foreign('claimant_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('defendant_id')->unsigned()->index()->nullable();
            $table->foreign('defendant_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('transaction_id')->unsigned()->index()->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('reason_id')->unsigned()->index()->nullable();
            $table->foreign('reason_id')->references('id')->on('reasons')->onUpdate('cascade')->onDelete('cascade');

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('code', 45)->nullable();
            $table->string('status', 7)->default('Open')->comment('Open, Closed, Solved');
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
        Schema::dropIfExists('disputes');
    }
}
