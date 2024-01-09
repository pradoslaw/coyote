<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterTopicTagsDeleteTrigger extends Migration
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
CREATE OR REPLACE FUNCTION after_topic_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET topics = topics - 1 WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_topic_tags_delete AFTER DELETE ON topic_tags FOR EACH ROW EXECUTE PROCEDURE "after_topic_tags_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_topic_tags_delete" ON topic_tags;');
        $this->db->unprepared('DROP FUNCTION after_topic_tags_delete();');
    }
}
