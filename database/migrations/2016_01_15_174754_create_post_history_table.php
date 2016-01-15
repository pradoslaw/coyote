<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_history', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('type_id');
            $table->integer('post_id');
            $table->integer('user_id')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->text('data')->nullable();
            $table->string('comment')->nullable();
            $table->string('guid');

            $table->index('post_id');

            $table->foreign('type_id')->references('id')->on('post_history_types')->onDelete('no action');
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
        Schema::drop('post_history');
    }
}
