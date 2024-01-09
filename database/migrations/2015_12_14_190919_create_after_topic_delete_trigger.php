<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterTopicDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_topic_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE forums SET last_post_id = get_forum_last_post_id(OLD.forum_id), topics = (topics - 1) WHERE "id" = OLD.forum_id;
	RETURN NEW;
END;$$;

CREATE TRIGGER after_topic_delete AFTER DELETE ON topics FOR EACH ROW EXECUTE PROCEDURE "after_topic_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_topic_delete" ON topics;');
        DB::unprepared('DROP FUNCTION after_topic_delete();');
    }
}
