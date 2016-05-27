<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wiki_id');
            $table->mediumInteger('user_id');
            $table->timestampsTz();
            $table->softDeletes();
            $table->text('text');
            $table->string('ip', 100)->nullable();

            $table->index('wiki_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('wiki_id')->references('id')->on('wiki_pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wiki_comments');
    }
}
