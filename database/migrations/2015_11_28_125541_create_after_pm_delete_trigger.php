<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPmDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_pm_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	IF (SELECT COUNT(*) FROM pm WHERE text_id = OLD.text_id) = 0 THEN
 	   DELETE FROM pm_text WHERE "id" = OLD.text_id;
 	END IF;

 	IF OLD.folder = 1 THEN
 	   UPDATE users SET pm = pm - 1 WHERE "id" = OLD.user_id;

 	   IF OLD.read_at IS NULL THEN
 	       UPDATE users SET pm_unread = pm_unread - 1 WHERE "id" = OLD.user_id;
 	   END IF;
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_pm_delete AFTER DELETE ON pm FOR EACH ROW EXECUTE PROCEDURE "after_pm_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_pm_delete" ON pm;');
        DB::unprepared('DROP FUNCTION after_pm_delete();');
    }
}
