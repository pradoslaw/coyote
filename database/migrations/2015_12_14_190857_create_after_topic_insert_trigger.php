<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterTopicInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_topic_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  	UPDATE forums SET topics = (topics + 1) WHERE "id" = NEW.forum_id;
	RETURN NEW;
END;$$;

CREATE TRIGGER after_topic_insert AFTER INSERT ON topics FOR EACH ROW EXECUTE PROCEDURE "after_topic_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_topic_insert" ON topics;');
        DB::unprepared('DROP FUNCTION after_topic_insert();');
    }
}
