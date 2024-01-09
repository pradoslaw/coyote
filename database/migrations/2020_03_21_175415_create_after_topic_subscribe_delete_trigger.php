<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterTopicSubscribeDeleteTrigger extends Migration
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
CREATE OR REPLACE FUNCTION after_topic_subscribe_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    UPDATE topics SET subscribers = subscribers - 1 WHERE id = OLD.topic_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_topic_subscribe_delete AFTER DELETE ON topic_subscribers FOR EACH ROW EXECUTE PROCEDURE "after_topic_subscribe_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_topic_subscribe_delete" ON topic_subscribers;');
        $this->db->unprepared('DROP FUNCTION after_topic_subscribe_delete();');
    }
}
