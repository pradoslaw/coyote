<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('path_id');
            $table->integer('ref_id')->nullable();
            $table->text('path')->nullable();

            $table->index('path_id');

            $table->foreign('path_id')->references('path_id')->on('wiki_paths')->onDelete('cascade');
            $table->foreign('ref_id')->references('path_id')->on('wiki_paths')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wiki_links');
    }
}
