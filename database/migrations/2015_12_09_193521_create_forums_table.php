<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forums', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->smallInteger('parent_id')->nullable();
            $table->string('name', 50);
            $table->string('path', 50);
            $table->string('title', 200)->nullable();
            $table->string('description', 255);
            $table->string('section', 50)->nullable();
            $table->string('url', 200)->nullable();
            $table->mediumInteger('topics')->default(0);
            $table->mediumInteger('posts')->default(0);
            $table->smallInteger('order');
            $table->integer('last_post_id')->nullable();
            $table->tinyInteger('is_locked')->default(0);
            $table->tinyInteger('require_tag')->default(0);
            $table->tinyInteger('enable_prune')->default(0);
            $table->tinyInteger('enable_reputation')->default(1);
            $table->tinyInteger('enable_anonymous')->default(1);
            $table->smallInteger('prune_days')->nullable();
            $table->integer('prune_last')->nullable(); // timestamp
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('forums');
    }
}
