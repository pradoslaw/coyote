<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropJobTagsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('job_tags');

        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_job_tags_delete" ON job_tags;');
        $this->db->unprepared('DROP FUNCTION IF EXISTS after_job_tags_delete();');

        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_job_tags_insert" ON job_tags;');
        $this->db->unprepared('DROP FUNCTION IF EXISTS after_job_tags_insert();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('job_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_id');
            $table->integer('tag_id');
            $table->smallInteger('priority')->nullable();

            $table->index('job_id');

            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_job_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs + 1, last_used_at = now() WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_job_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs - 1 WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_job_tags_delete AFTER DELETE ON job_tags FOR EACH ROW EXECUTE PROCEDURE "after_job_tags_delete"();
        ');
    }
}
