<?php

use Illuminate\Database\Migrations\Migration;

class DropBeforeTopicUpdateTrigger extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "before_topic_update" ON topics;');
        $this->db->unprepared('DROP FUNCTION before_topic_update();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION before_topic_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	IF NEW.forum_id != OLD.forum_id THEN
 		NEW.prev_forum_id = OLD.forum_id;

 		UPDATE posts SET forum_id = NEW.forum_id WHERE topic_id = OLD."id";
 		UPDATE topic_track SET forum_id = NEW.forum_id WHERE topic_id = OLD."id";
 		--DELETE FROM topic_track WHERE topic_id = OLD."id";

		UPDATE forums
		SET topics = topics -1, posts = posts - (NEW.replies + 1), last_post_id = get_forum_last_post_id(OLD.forum_id)
		WHERE "id" = OLD.forum_id;

		UPDATE forums
		SET topics = topics +1, posts = posts + (NEW.replies + 1), last_post_id = get_forum_last_post_id(NEW.forum_id)
		WHERE "id" = NEW.forum_id;

		UPDATE post_votes SET forum_id = NEW.forum_id
		WHERE post_id IN(
			SELECT "id"
			FROM posts
			WHERE topic_id = NEW."id"
		);
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER before_topic_update BEFORE UPDATE ON topics FOR EACH ROW EXECUTE PROCEDURE "before_topic_update"();
        ');
    }
}
