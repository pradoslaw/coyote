<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterJobTagsDeleteTrigger extends Migration
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
CREATE FUNCTION after_job_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs - 1 WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_job_tags_delete AFTER DELETE ON job_tags FOR EACH ROW EXECUTE PROCEDURE "after_job_tags_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_job_tags_delete" ON job_tags;');
        $this->db->unprepared('DROP FUNCTION after_job_tags_delete();');
    }
}
