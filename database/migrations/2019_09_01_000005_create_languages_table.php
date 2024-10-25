<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('short_name', 5);
            $table->string('flag', 100)->nullable();
            $table->string('default', 3)->default('0')->comment('1 or 0');
            $table->string('deletable', 5)->default('Yes')->comment('Yes or No'); //ALTER TABLE languages ADD deletable enum('Yes','No') AFTER 'default'  -- for mysql query;
            $table->string('status', 11)->default('Active')->comment('Active or Inactive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
