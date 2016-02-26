<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('firms', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->integer('user_id');
            $table->string('name', 100);
            $table->timestampsTz();
            $table->softDeletes();
            $table->string('logo', 45)->nullable();
            $table->string('website')->nullable();
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('employees')->nullable();
            $table->smallInteger('founded')->nullable();
            $table->tinyInteger('is_agency')->default(0);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('firms');
    }
}
