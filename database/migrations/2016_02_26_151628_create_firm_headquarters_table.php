<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirmHeadquartersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('firm_headquarters', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->smallInteger('firm_id');
            $table->smallInteger('country_id');
            $table->string('city');
            $table->string('street');
            $table->string('house', 50);
            $table->string('postcode', 50);
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();

            $table->index('firm_id');

            $table->foreign('firm_id')->references('id')->on('firms')->onDelete('cascade');
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
        Schema::drop('firm_headquarters');
    }
}
