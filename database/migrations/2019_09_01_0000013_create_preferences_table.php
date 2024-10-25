<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreferencesTable extends Migration
{
    public function up()
    {
        Schema::create('preferences', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('category');
            $table->string('field', 30)->unique()->index();
            $table->string('value', 50);
        });
    }

    public function down()
    {
        Schema::dropIfExists('preferences');
    }
}
