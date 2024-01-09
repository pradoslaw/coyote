<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPollVoteInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_poll_vote_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE poll_items SET total = total + 1 WHERE "id" = NEW.item_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_poll_vote_insert AFTER INSERT ON poll_votes FOR EACH ROW EXECUTE PROCEDURE "after_poll_vote_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_poll_vote_insert" ON poll_votes;');
        DB::unprepared('DROP FUNCTION after_poll_vote_insert();');
    }
}
