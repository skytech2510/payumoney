<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('notification_settings'))
        {
            Schema::create('notification_settings', function (Blueprint $table)
            {
                $table->increments('id');

                $table->integer('notification_type_id')->unsigned()->index();
                $table->foreign('notification_type_id')->references('id')->on('notification_settings')->onDelete('cascade');

                $table->string('recipient_type')->nullable()->default(null);
                $table->string('recipient')->index()->nullable()->default(null);
                $table->string('status')->index()->default('No');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_settings');
    }
}
