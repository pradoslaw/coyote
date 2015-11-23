<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_types', function (Blueprint $table) {
            $table->smallInteger('id', false);
            $table->string('name', 100);
            $table->string('headline');
            $table->tinyInteger('profile');
            $table->tinyInteger('email');

            $table->unique('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_types');
    }
}
