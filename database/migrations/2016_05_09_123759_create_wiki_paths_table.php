<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiPathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki_paths', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wiki_id');
            $table->integer('parent_id')->nullable();
            $table->text('path');

            $table->index('parent_id');
            $table->index('wiki_id');
        });

        Schema::table('wiki_paths', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('wiki_paths')->onDelete('cascade');
        });

        DB::unprepared('CREATE INDEX "wiki_paths_path_index" ON "wiki_paths" USING btree (LOWER(path))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wiki_paths');
    }
}
