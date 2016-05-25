<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki_attachments', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->integer('wiki_id')->nullable();
            $table->string('name', 100);
            $table->string('file', 30);
            $table->integer('size');
            $table->string('mime', 50);
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));

            $table->index('wiki_id');
            $table->unique('file');

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
        Schema::drop('wiki_attachments');
    }
}
