<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTopicTagsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('topic_tags');

        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_topic_tags_delete" ON topic_tags;');
        $this->db->unprepared('DROP FUNCTION IF EXISTS after_topic_tags_delete();');

        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_topic_tags_insert" ON topic_tags;');
        $this->db->unprepared('DROP FUNCTION IF EXISTS after_topic_tags_insert();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('topic_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('topic_id');
            $table->integer('tag_id');

            $table->index('tag_id');
            $table->index('topic_id');

            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
        });
    }
}
