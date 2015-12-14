<?php

use Illuminate\Database\Schema\Blueprint;
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
CREATE FUNCTION after_topic_update() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE
	postCount INTEGER;
BEGIN
	IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN -- kasowanie watku

		UPDATE users
		SET posts = posts - p.user_posts
		FROM (
			SELECT user_id, COUNT(*) AS user_posts
			FROM posts
			WHERE topic_id = OLD."id" AND deleted_at IS NULL AND user_id IS NOT NULL
			GROUP BY user_id
		) AS p
		WHERE "id" = p.user_id;

		postCount = (SELECT COUNT(*) FROM posts WHERE topic_id = OLD."id" AND deleted_at IS NULL);
		UPDATE posts SET deleted_at = NOW() WHERE topic_id = OLD."id" AND deleted_at IS NULL;

		UPDATE forums SET topics = (topics - 1), posts = (posts - postCount), last_post_id = get_forum_last_post_id(OLD.forum_id) WHERE "id" = OLD.forum_id;
		DELETE FROM topic_track WHERE "topic_id" = OLD."id";

	ELSEIF OLD.deleted_at IS NOT NULL AND NEW.deleted_at IS NULL THEN

		UPDATE users
		SET posts = posts + p.user_posts
		FROM (
			SELECT user_id, COUNT(*) AS user_posts
			FROM posts
			WHERE topic_id = OLD."id" AND deleted_at IS NOT NULL AND user_id IS NOT NULL
			GROUP BY user_id
		) AS p
		WHERE "id" = p.user_id;

		postCount = (SELECT COUNT(*) FROM posts WHERE topic_id = OLD."id" AND deleted_at IS NOT NULL);
		UPDATE posts SET deleted_at = NULL WHERE topic_id = OLD."id" AND deleted_at IS NOT NULL;

		UPDATE forums SET topics = (topics + 1), posts = (posts + postCount), last_post_id = get_forum_last_post_id(OLD.forum_id) WHERE "id" = NEW.forum_id;
	END IF;


 	IF NEW.forum_id != OLD.forum_id THEN
 		NEW.prev_forum_id = OLD.forum_id;

 		UPDATE posts SET forum_id = NEW.forum_id WHERE topic_id = OLD."id";
 		UPDATE topic_track SET forum_id = NEW.forum_id WHERE topic_id = OLD."id";

 		postCount = (SELECT COUNT("id") FROM posts WHERE topic_id = NEW."id" AND deleted_at IS NOT NULL);

		IF OLD.deleted_at IS NULL THEN
	 		UPDATE forums
	 		SET topics = topics -1, posts = posts - postCount, last_post_id = get_forum_last_post_id(OLD.forum_id)
	 		WHERE "id" = OLD.forum_id;

	 		UPDATE forums
	 		SET topics = topics +1, posts = posts + postCount, last_post_id = get_forum_last_post_id(NEW.forum_id)
	 		WHERE "id" = NEW.forum_id;
	 	END IF;

		UPDATE post_votes SET forum_id = NEW.forum_id
		WHERE post_id IN(
			SELECT "id"
			FROM posts
			WHERE topic_id = NEW."id"
		);
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
