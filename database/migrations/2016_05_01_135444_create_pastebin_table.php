<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePastebinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pastebin', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->timestampTz('expired_at')->nullable();
            $table->text('text');
            $table->string('syntax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pastebin');
    }
}
