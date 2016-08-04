<?php

use Illuminate\Database\Migrations\Migration;

class CreateBeforeForumInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION before_forum_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	NEW."order" := (SELECT COALESCE(MAX("order"), 0) FROM forums WHERE COALESCE(parent_id, 0) = COALESCE(NEW.parent_id, 0)) + 1;

	RETURN NEW;
END;$$;

CREATE TRIGGER before_forum_insert BEFORE INSERT ON forums FOR EACH ROW EXECUTE PROCEDURE "before_forum_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "before_forum_insert" ON forums;');
        DB::unprepared('DROP FUNCTION before_forum_insert();');
    }
}
