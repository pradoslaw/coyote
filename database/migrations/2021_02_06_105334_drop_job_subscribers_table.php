<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropJobSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('job_subscribers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('job_subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_id');
            $table->integer('user_id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'))->nullable();

            $table->unique(['job_id', 'user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });
    }
}
