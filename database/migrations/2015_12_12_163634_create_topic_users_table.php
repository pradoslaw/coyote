<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopicUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topic_users', function (Blueprint $table) {
            $table->integer('topic_id');
            $table->integer('user_id');

            $table->index(['topic_id', 'user_id']);

            $table->foreign('topic_id')->references('id')->on('topics');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('topic_users');
    }
}
