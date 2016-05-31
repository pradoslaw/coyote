<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->timestampTz('updated_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->string('title');
            $table->string('path', 2000);
            $table->integer('content_id')->nullable();
            $table->string('content_type')->nullable();
            $table->tinyInteger('allow_sitemap')->default(1);
            
            $table->index(['content_id', 'content_type']);
        });

        DB::unprepared('CREATE INDEX "pages_path_index" ON "pages" USING btree (LOWER(path))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pages');
    }
}
