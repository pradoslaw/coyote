<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->nullable(); // dla kompatybilnosci wstecznej
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->smallInteger('poll_id');
            $table->smallInteger('item_id');
            $table->string('ip', 45);

            $table->index('poll_id');

            $table->foreign('poll_id')->references('id')->on('polls')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('poll_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('poll_votes');
    }
}
