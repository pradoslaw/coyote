<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('ip', 45);
            $table->mediumInteger('user_id')->nullable();
            $table->text('payload');
            $table->dateTimeTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->dateTimeTz('updated_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->string('url', 4000)->nullable();
            $table->string('browser', 1000);
            $table->string('robot')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sessions');
    }
}
