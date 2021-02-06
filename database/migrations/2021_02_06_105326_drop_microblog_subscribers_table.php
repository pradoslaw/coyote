<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMicroblogSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('microblog_subscribers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('microblog_subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('microblog_id');
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'))->nullable();

            $table->unique(['microblog_id', 'user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('microblog_id')->references('id')->on('microblogs')->onDelete('cascade');
        });
    }
}
