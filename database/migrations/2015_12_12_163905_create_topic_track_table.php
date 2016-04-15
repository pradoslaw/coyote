<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopicTrackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topic_track', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('topic_id');
            $table->smallInteger('forum_id');
            $table->integer('user_id')->nullable();
            $table->timestampTz('marked_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->string('session_id')->nullable();

            $table->index('forum_id');
            $table->index(['topic_id', 'user_id']);
            $table->index(['topic_id', 'session_id']);

            $table->foreign('forum_id')->references('id')->on('forums')->onDelete('cascade');
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('topic_track');
    }
}
