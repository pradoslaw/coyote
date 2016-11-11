<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('forum_id');
            $table->integer('topic_id');
            $table->integer('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->timestampsTz();
            $table->softDeletes();
            $table->smallInteger('edit_count')->default(0);
            $table->integer('editor_id')->nullable();
            $table->smallInteger('score')->default(0);
            $table->text('text');
            $table->string('ip');
            $table->string('browser')->nullable(); // wsteczna kompatybilnosc
            $table->string('host');

            $table->index('forum_id');
            $table->index('topic_id');
            $table->index('user_id');
            $table->index('created_at');

            $table->foreign('forum_id')->references('id')->on('forums');
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('editor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('posts');
    }
}
