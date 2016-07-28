<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->string('title');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->timestampTz('updated_at')->nullable();
            $table->integer('length')->default(0);
            $table->smallInteger('max_items')->default(1);
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->foreign('poll_id')->references('id')->on('polls')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['poll_id']);
        });

        Schema::drop('polls');
    }
}
