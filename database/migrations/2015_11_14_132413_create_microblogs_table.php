<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMicroblogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('microblogs', function (Blueprint $table) {
            $table->mediumInteger('id', true);
            $table->mediumInteger('parent_id')->nullable()->index();
            $table->mediumInteger('user_id');
            $table->timestampsTz();
            $table->softDeletes();
            $table->text('text');
            $table->smallInteger('votes')->default(0);
            $table->integer('score')->nullable()->index();
            $table->tinyInteger('is_sponsored')->default(0);
            $table->smallInteger('bonus')->nullable();
            $table->json('media')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('microblogs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('microblogs');
    }
}
