<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPostInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_post_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE topics SET
		last_post_id = NEW."id",
		last_post_created_at = NEW.created_at,
		replies = (CASE WHEN first_post_id IS NULL THEN replies ELSE replies + 1 END),
		replies_real = (CASE WHEN first_post_id IS NULL THEN replies_real ELSE replies_real + 1 END),
		first_post_id = (CASE WHEN first_post_id IS NULL THEN NEW."id" ELSE first_post_id END)
	WHERE "id" = NEW.topic_id;

	UPDATE forums SET posts = (posts + 1), last_post_id = NEW."id" WHERE "id" = NEW.forum_id;

	IF (SELECT first_post_id FROM topics WHERE "id" = NEW.topic_id) IS NULL THEN
		UPDATE topics SET first_post_id = NEW."id" WHERE "id" = NEW.topic_id;
	END IF;

 	IF NEW.user_id IS NOT NULL THEN
 		UPDATE users SET posts = posts + 1 WHERE "id" = NEW.user_id;

		IF (SELECT COUNT(*) FROM topic_users WHERE topic_id = NEW.topic_id AND user_id = NEW.user_id) = 0 THEN
			INSERT INTO topic_users (topic_id, user_id) VALUES(NEW.topic_id, NEW.user_id);
		END IF;
		--INSERT post_subscribe (post_id, user_id) VALUES(NEW.post_id, NEW.post_user);
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_post_insert AFTER INSERT ON posts FOR EACH ROW EXECUTE PROCEDURE "after_post_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_post_insert" ON posts;');
        DB::unprepared('DROP FUNCTION after_post_insert();');
    }
}
