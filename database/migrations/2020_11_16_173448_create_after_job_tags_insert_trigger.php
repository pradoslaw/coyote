<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterJobTagsInsertTrigger extends Migration
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
 	UPDATE tags SET jobs = jobs + 1 WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_job_tags_insert AFTER INSERT ON job_tags FOR EACH ROW EXECUTE PROCEDURE "after_job_tags_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_job_tags_insert" ON job_tags;');
        $this->db->unprepared('DROP FUNCTION after_job_tags_insert();');
    }
}
