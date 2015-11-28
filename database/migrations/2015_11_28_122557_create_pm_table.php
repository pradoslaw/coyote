<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('root_id');
            $table->mediumInteger('user_id');
            $table->mediumInteger('author_id');
            $table->tinyInteger('folder');
            $table->integer('text_id');
            $table->timestampTz('read_at')->nullable();

            $table->index('user_id');
            $table->index('root_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pm');
    }
}
