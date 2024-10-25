<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30)->index('crypto_providers_name_idx');
            $table->string('alias', 30)->unique()->index('crypto_providers_alias_idx');
            $table->string('description')->nullable();
            $table->string('logo', 91)->nullable();
            $table->text('subscription_details')->nullable();
            $table->String('status', 11)->default('Active');
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
        Schema::dropIfExists('crypto_providers');
    }
}
