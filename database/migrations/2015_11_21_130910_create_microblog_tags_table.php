<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMicroblogTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('microblog_tags', function (Blueprint $table) {
            $table->mediumInteger('id', true);
            $table->mediumInteger('microblog_id');
            $table->string('name', 100);

            $table->index('microblog_id');
            $table->index('name');

            $table->foreign('microblog_id')->references('id')->on('microblogs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('microblog_tags');
    }
}
