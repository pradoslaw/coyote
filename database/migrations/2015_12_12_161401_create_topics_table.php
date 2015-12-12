<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject');
            $table->string('path');
            $table->smallInteger('forum_id');
            $table->timestampsTz();
            $table->softDeletes();
            $table->mediumInteger('views')->default(0);
            $table->mediumInteger('replies')->default(0);
            $table->mediumInteger('replies_real')->default(0);
            $table->smallInteger('score')->default(0);
            $table->tinyInteger('is_sticky')->default(0);
            $table->tinyInteger('is_announcement')->default(0);
            $table->tinyInteger('is_locked')->default(0);
            $table->tinyInteger('is_solved')->default(0);
            $table->smallInteger('poll_id')->nullable();
            $table->smallInteger('prev_forum_id')->nullable();
            $table->integer('first_post_id')->nullable();
            $table->integer('last_post_id')->nullable();
            $table->timestampTz('last_post_created_at')->nullable();

            $table->index('forum_id');
            $table->index('last_post_id');
            $table->index('last_post_created_at');
            $table->index('is_solved');
            $table->index('views');
            $table->index(['deleted_at', 'forum_id', 'is_sticky']);

            $table->foreign('forum_id')->references('id')->on('forums');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('topics');
    }
}
