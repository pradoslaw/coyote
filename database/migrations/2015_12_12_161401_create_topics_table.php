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
            $table->string('slug');
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
            $table->smallInteger('poll_id')->nullable();
            $table->smallInteger('prev_forum_id')->nullable();
            $table->integer('first_post_id')->nullable();
            $table->integer('last_post_id')->nullable();
            $table->timestampTz('last_post_created_at')->nullable();

            // domyslny widok forum to sortowanie po is_sticky oraz last_post_id.
            $table->index(['forum_id', 'is_sticky', 'last_post_id']);
            // inny tryb sortowania wymagac bedzie uzycia tego indeksu.
            $table->index('forum_id');
            $table->index('last_post_id');
            // sprawdzanie czy sa jeszcze jakies nieprzeczytane watki w danym dziale
            $table->index(['forum_id', 'deleted_at', 'last_post_created_at']);

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
