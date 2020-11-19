<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAfterJobTagsInsertTrigger extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_job_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs + 1, last_used_at = now() WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_job_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs + 1 WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');
    }
}
