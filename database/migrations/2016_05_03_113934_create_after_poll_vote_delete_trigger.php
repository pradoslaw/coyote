<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPollVoteDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_poll_vote_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE poll_items SET total = total - 1 WHERE "id" = OLD.item_id;

	RETURN OLD;
END;$$;

CREATE TRIGGER after_poll_vote_delete AFTER DELETE ON poll_votes FOR EACH ROW EXECUTE PROCEDURE "after_poll_vote_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_poll_vote_delete" ON poll_votes;');
        DB::unprepared('DROP FUNCTION after_poll_vote_delete();');
    }
}
