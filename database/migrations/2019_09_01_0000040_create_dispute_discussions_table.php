<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputeDiscussionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispute_discussions', function (Blueprint $table)
        {
            $table->increments('id');

            $table->integer('dispute_id')->unsigned()->index()->nullable();
            $table->foreign('dispute_id')->references('id')->on('disputes')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('user_id')->index();
            $table->string('type', 6)->comment('Admin, User');
            $table->longText('message')->nullable();
            $table->string('file', 255)->nullable();
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
        Schema::dropIfExists('dispute_discussions');
    }
}
