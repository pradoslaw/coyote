<?php

use Illuminate\Database\Migrations\Migration;

class ChangeAfterPostInsertTrigger2 extends Migration
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
CREATE OR REPLACE FUNCTION after_post_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE topics SET
		last_post_id = (CASE WHEN last_post_id IS NULL OR NEW.created_at > last_post_created_at THEN NEW."id" ELSE last_post_id END),
		last_post_created_at = GREATEST(last_post_created_at, NEW.created_at),
		replies = (CASE WHEN first_post_id IS NULL THEN replies ELSE replies + 1 END),
		replies_real = (CASE WHEN first_post_id IS NULL THEN replies_real ELSE replies_real + 1 END),
		first_post_id = (CASE WHEN first_post_id IS NULL THEN NEW."id" ELSE first_post_id END)
	WHERE "id" = NEW.topic_id;

	UPDATE forums SET posts = (posts + 1), last_post_id = NEW."id" WHERE "id" = NEW.forum_id;

 	IF NEW.user_id IS NOT NULL THEN
 		UPDATE users SET posts = posts + 1 WHERE "id" = NEW.user_id;

		IF (SELECT COUNT(*) FROM topic_users WHERE topic_id = NEW.topic_id AND user_id = NEW.user_id) = 0 THEN
			INSERT INTO topic_users (topic_id, user_id, post_id) VALUES(NEW.topic_id, NEW.user_id, NEW.id);
		END IF;
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

 	IF NEW.user_id IS NOT NULL THEN
 		UPDATE users SET posts = posts + 1 WHERE "id" = NEW.user_id;

		IF (SELECT COUNT(*) FROM topic_users WHERE topic_id = NEW.topic_id AND user_id = NEW.user_id) = 0 THEN
			INSERT INTO topic_users (topic_id, user_id, post_id) VALUES(NEW.topic_id, NEW.user_id, NEW.id);
		END IF;
 	END IF;

	RETURN NEW;
END;$$;
        ');
    }
}
