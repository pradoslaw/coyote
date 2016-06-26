<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiRedirectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki_redirects', function (Blueprint $table) {
            $table->increments('id');
            $table->text('path');
            $table->integer('path_id');

            $table->index('path');

            $table->foreign('path_id')->references('path_id')->on('wiki_paths')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wiki_redirects');
    }
}
