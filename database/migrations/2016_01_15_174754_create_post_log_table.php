<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->integer('user_id')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->string('subject')->nullable();
            $table->text('text')->nullable();
            $table->json('tags')->nullable();
            $table->string('comment')->nullable();
            $table->string('ip');
            $table->string('browser')->nullable();
            $table->string('host');

            $table->index('post_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
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
        Schema::drop('post_log');
    }
}
