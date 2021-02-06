<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPostSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('post_subscribers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('post_subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));

            $table->unique(['post_id', 'user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }
}
