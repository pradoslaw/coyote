<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('type_id');
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->string('subject');
            $table->string('excerpt')->nullable();
            $table->string('url')->nullable();
            $table->string('object_id');
            $table->timestampTz('read_at')->nullable();
            $table->tinyInteger('is_marked')->default(0);

            $table->index('user_id');
            $table->index('object');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('alert_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alerts');
    }
}
