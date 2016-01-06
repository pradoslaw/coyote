<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterPostVoteInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION after_post_vote_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE posts SET score = score + 1 WHERE "id" = NEW.post_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_post_vote_insert AFTER INSERT ON post_votes FOR EACH ROW EXECUTE PROCEDURE "after_post_vote_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_post_vote_insert" ON post_votes;');
        DB::unprepared('DROP FUNCTION after_post_vote_insert();');
    }
}
