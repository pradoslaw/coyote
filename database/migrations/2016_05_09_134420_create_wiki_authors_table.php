<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki_authors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wiki_id');
            $table->integer('user_id');
            $table->double('share');
            $table->integer('length');

            $table->index('wiki_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('wiki_id')->references('id')->on('wiki')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wiki_authors');
    }
}
