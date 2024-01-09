<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterForumDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_forum_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  	UPDATE forums SET "order" = "order" - 1 WHERE COALESCE(parent_id, 0) = COALESCE(OLD.parent_id, 0) AND "order" > OLD."order";

	RETURN NEW;
END;$$;

CREATE TRIGGER after_forum_delete AFTER DELETE ON forums FOR EACH ROW EXECUTE PROCEDURE "after_forum_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_forum_delete" ON forums;');
        DB::unprepared('DROP FUNCTION after_forum_delete();');
    }
}
