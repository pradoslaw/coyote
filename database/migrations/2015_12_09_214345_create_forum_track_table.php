<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumTrackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_track', function (Blueprint $table) {
            $table->smallInteger('forum_id');
            $table->integer('user_id')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->string('session_id')->nullable();

            $table->index('forum_id');
            $table->index('session_id');

            $table->foreign('forum_id')->references('id')->on('forums')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('forum_track');
    }
}
