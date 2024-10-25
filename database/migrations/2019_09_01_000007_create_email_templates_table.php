<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('language_id')->unsigned()->index('email_templates_language_id_idx')->nullable();
            $table->foreign('language_id')->references('id')->on('languages')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name')->index('email_templates_name_idx');
            $table->string('alias')->index('email_templates_alias_idx');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('lang', 2);
            $table->string('type', 5)->comment('email or sms');
            $table->string('status', 10)->default('Active')->comment('Active/Inactive');
            $table->string('group', 40);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_templates');
    }
}
