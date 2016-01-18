<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll_items', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->string('text');
            $table->smallInteger('poll_id');
            $table->smallInteger('total');

            $table->index('poll_id');

            $table->foreign('poll_id')->references('id')->on('polls')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('poll_items');
    }
}
