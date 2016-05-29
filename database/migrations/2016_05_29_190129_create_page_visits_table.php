<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('page_id');
            $table->integer('user_id');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->smallInteger('visits')->default(0);

            $table->index('page_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('page_visits');
    }
}
