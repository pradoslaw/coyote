<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterTopicUpdateTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_topic_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN -- kasowanie watku
		UPDATE posts SET deleted_at = NOW() WHERE topic_id = OLD."id" AND deleted_at IS NULL;

		UPDATE forums SET topics = (topics - 1), last_post_id = get_forum_last_post_id(OLD.forum_id) WHERE "id" = OLD.forum_id;
		DELETE FROM topic_track WHERE "topic_id" = OLD."id";

	ELSEIF OLD.deleted_at IS NOT NULL AND NEW.deleted_at IS NULL THEN
		UPDATE posts SET deleted_at = NULL WHERE topic_id = OLD."id" AND deleted_at IS NOT NULL;

		UPDATE forums SET topics = (topics + 1), last_post_id = get_forum_last_post_id(OLD.forum_id) WHERE "id" = NEW.forum_id;
	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_topic_update AFTER UPDATE ON topics FOR EACH ROW EXECUTE PROCEDURE "after_topic_update"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_topic_update" ON topics;');
        DB::unprepared('DROP FUNCTION after_topic_update();');
    }
}
