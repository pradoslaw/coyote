<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPostVoteDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_post_vote_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE posts SET score = score - 1 WHERE "id" = OLD.post_id;
 	UPDATE topics SET score = score - 1 WHERE first_post_id = OLD.post_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_post_vote_delete AFTER DELETE ON post_votes FOR EACH ROW EXECUTE PROCEDURE "after_post_vote_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_post_vote_delete" ON post_votes;');
        DB::unprepared('DROP FUNCTION after_post_vote_delete();');
    }
}
