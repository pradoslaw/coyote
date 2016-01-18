<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->integer('user_id');
            $table->smallInteger('poll_id');
            $table->string('ip', 45);

            $table->index('poll_id');

            $table->foreign('poll_id')->references('id')->on('polls')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('poll_votes');
    }
}
