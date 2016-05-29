<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('long_title')->nullable();
            $table->string('slug');
            $table->timestampsTz();
            $table->softDeletes();
            $table->text('excerpt')->nullable();
            $table->text('text')->nullable();
            $table->tinyInteger('is_locked')->default(0);
            $table->string('template')->nullable();
            $table->integer('visits')->default(0);

            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wiki_pages');
    }
}
