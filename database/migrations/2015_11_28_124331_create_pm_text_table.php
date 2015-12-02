<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePmTextTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm_text', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->text('text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pm_text');
    }
}
