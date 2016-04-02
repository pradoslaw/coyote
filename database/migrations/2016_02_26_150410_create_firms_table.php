<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('firms', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->integer('user_id');
            $table->string('name', 100);
            $table->string('path')->nullable();
            $table->timestampsTz();
            $table->softDeletes();
            $table->string('logo', 45)->nullable();
            $table->string('website')->nullable();
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('employees')->nullable();
            $table->smallInteger('founded')->nullable();
            $table->tinyInteger('is_agency')->default(0);
            $table->smallInteger('country_id')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('house', 50)->nullable();
            $table->string('postcode', 50)->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('firms');
    }
}
