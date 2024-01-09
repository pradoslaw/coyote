<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPmInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_pm_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	IF NEW.folder = 1 THEN
 		UPDATE users SET pm = pm + 1, pm_unread = pm_unread + 1 WHERE "id" = NEW.user_id;
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_pm_insert AFTER INSERT ON pm FOR EACH ROW EXECUTE PROCEDURE "after_pm_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_pm_insert" ON pm;');
        DB::unprepared('DROP FUNCTION after_pm_insert();');
    }
}
