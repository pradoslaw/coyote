<?php

use Illuminate\Database\Migrations\Migration;

class ChangeAfterTopicUpdateTrigger extends Migration
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
	
	IF NEW.forum_id != OLD.forum_id THEN
 		NEW.prev_forum_id = OLD.forum_id;

 		UPDATE posts SET forum_id = NEW.forum_id WHERE topic_id = OLD."id";
 		UPDATE topic_track SET forum_id = NEW.forum_id WHERE topic_id = OLD."id";

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
        ');
    }
}
