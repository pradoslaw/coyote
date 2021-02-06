<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPostAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('post_attachments');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('post_attachments', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->integer('post_id')->nullable();
            $table->string('name');
            $table->string('file', 30);
            $table->smallInteger('count')->default(0);
            $table->integer('size');
            $table->string('mime', 255);
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->timestampTz('updated_at');

            $table->index('post_id');
            $table->unique('file');

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }
}
