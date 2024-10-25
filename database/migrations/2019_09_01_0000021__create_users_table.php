<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table)
        {

            $table->increments('id');

            $table->integer('role_id')->unsigned()->index()->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');

            $table->string('type', 30)->default('user')->comment('user or merchant');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('formattedPhone', 30)->nullable()->default(null);
            $table->string('phone', 20)->unique()->nullable()->default(null);
            $table->text('google2fa_secret')->nullable();
            $table->string('defaultCountry', 4)->nullable()->default(null);
            $table->string('carrierCode', 6)->nullable()->default(null);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phrase')->nullable()->default(null);
            $table->boolean('address_verified')->default(false);
            $table->boolean('identity_verified')->default(false);
            $table->string('status', 11)->default('Active')->comment('Active, Inactive, Suspended');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
