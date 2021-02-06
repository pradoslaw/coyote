<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTopicSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('topic_subscribers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('topic_subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('topic_id');
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));

            $table->unique(['topic_id', 'user_id']);
            $table->index('topic_id'); // pobranie listy obserwatorow danego watku

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
        });
    }
}
