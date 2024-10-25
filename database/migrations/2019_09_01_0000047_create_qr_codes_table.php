<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQrCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qr_codes', function (Blueprint $table)
        {
            $table->increments('id');
            
            $table->integer('object_id')->index()->nullable()->default(NULL);

            $table->string('object_type', 20)->nullable()->default(NULL);
            $table->string('secret', 191)->nullable()->default(NULL);
            $table->string('qr_image', 50)->nullable()->default(NULL);
            $table->string('status', 16);

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
        Schema::dropIfExists('qr_codes');
    }
}
