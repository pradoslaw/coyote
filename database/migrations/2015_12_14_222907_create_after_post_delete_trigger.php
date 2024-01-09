<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPostDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_post_delete() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE
	_post_id INTEGER;
	_post_time TIMESTAMP WITH TIME ZONE;
BEGIN
 	SELECT "id", created_at INTO _post_id, _post_time
 	FROM posts WHERE topic_id = OLD.topic_id AND deleted_at IS NULL
 	ORDER BY "id" DESC
 	LIMIT 1;

	UPDATE topics SET replies = (replies -1),
							replies_real = (replies_real -1),
							last_post_id = _post_id,
							last_post_created_at = _post_time
	WHERE "id" = OLD.topic_id;

	UPDATE forums SET posts = (posts -1),
								last_post_id = get_forum_last_post_id(OLD.forum_id)
	WHERE "id" = OLD.forum_id;

	IF OLD.user_id IS NOT NULL THEN
		UPDATE users SET posts = posts - 1 WHERE "id" = OLD.user_id;

		IF (SELECT COUNT(*) FROM posts WHERE topic_id = OLD.topic_id AND user_id = OLD.user_id AND deleted_at IS NULL) = 0 THEN
				DELETE FROM topic_users WHERE topic_id = OLD.topic_id AND user_id = OLD.user_id;

		END IF;
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_post_delete AFTER DELETE ON posts FOR EACH ROW EXECUTE PROCEDURE "after_post_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_post_delete" ON posts;');
        DB::unprepared('DROP FUNCTION after_post_delete();');
    }
}
