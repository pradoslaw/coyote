<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterTopicTagsInsertTrigger extends Migration
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
CREATE FUNCTION after_topic_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET topics = topics + 1 WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_topic_tags_insert AFTER INSERT ON topic_tags FOR EACH ROW EXECUTE PROCEDURE "after_topic_tags_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_topic_tags_insert" ON topic_tags;');
        $this->db->unprepared('DROP FUNCTION after_topic_tags_insert();');
    }
}
