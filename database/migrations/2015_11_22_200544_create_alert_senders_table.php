<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertSendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_senders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('alert_id');
            $table->mediumInteger('user_id')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->string('name')->nullable();

            $table->index('alert_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('alert_id')->references('id')->on('alerts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_senders');
    }
}
