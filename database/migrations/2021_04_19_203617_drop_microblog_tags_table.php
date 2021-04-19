<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMicroblogTagsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('microblog_tags');

        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_microblog_tags_delete" ON microblog_tags;');
        $this->db->unprepared('DROP FUNCTION IF EXISTS after_microblog_tags_delete();');

        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_microblog_tags_insert" ON microblog_tags;');
        $this->db->unprepared('DROP FUNCTION IF EXISTS after_microblog_tags_insert();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->create('microblog_tags', function (Blueprint $table) {
            $table->mediumInteger('id', true);
            $table->mediumInteger('microblog_id');
            $table->integer('tag_id');

            $table->index('microblog_id');

            $table->foreign('microblog_id')->references('id')->on('microblogs')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }
}
