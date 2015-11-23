<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('type_id');
            $table->mediumInteger('user_id');
            $table->tinyInteger('profile');
            $table->tinyInteger('email');

            $table->index('type_id');
            $table->index('user_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('alert_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_settings');
    }
}
