<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('forum_id');
            $table->integer('user_id');
            $table->string('section', 50)->nullable();
            $table->tinyInteger('is_hidden')->default(0);
            $table->smallInteger('order');

            $table->index('forum_id');
            $table->index('user_id');

            $table->unique(['forum_id', 'user_id']);

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
        Schema::drop('forum_orders');
    }
}
