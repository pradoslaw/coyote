<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable();
            $table->string('title');
            $table->string('long_title')->nullable();
            $table->string('slug');
            $table->text('path');
            $table->timestampsTz();
            $table->softDeletes();
            $table->text('excerpt')->nullable();
            $table->text('text')->nullable();
            $table->tinyInteger('is_locked')->default(0);
            $table->string('template')->nullable();

            $table->index('parent_id');
            $table->index('deleted_at');

            $table->foreign('parent_id')->references('id')->on('wiki')->onDelete('cascade');
        });

        DB::unprepared('CREATE INDEX "wiki_path_index" ON "wiki" USING btree (LOWER(path))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wiki');
    }
}
