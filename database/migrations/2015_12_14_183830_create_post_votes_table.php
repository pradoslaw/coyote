<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_votes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->smallInteger('forum_id');
            $table->integer('user_id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'))->nullable();
            $table->string('ip', 45)->nullable();

            $table->index('forum_id');
            $table->index('post_id');

            $table->foreign('forum_id')->references('id')->on('forums')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('post_votes');
    }
}
