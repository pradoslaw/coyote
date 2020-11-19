<?php

use Illuminate\Database\Migrations\Migration;

class ChangeAfterTopicTagsInsertTrigger extends Migration
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
CREATE OR REPLACE FUNCTION after_topic_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET topics = topics + 1, last_used_at = now() WHERE id = NEW.tag_id;

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
CREATE OR REPLACE FUNCTION after_topic_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET topics = topics + 1 WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');
    }
}
