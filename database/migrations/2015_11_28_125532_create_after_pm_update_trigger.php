<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPmUpdateTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_pm_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF NEW.read_at IS NOT NULL AND OLD.read_at IS NULL AND NEW.folder = 1 THEN
  		UPDATE users SET pm_unread = pm_unread - 1 WHERE "id" = NEW.user_id;
  	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_pm_update AFTER UPDATE ON pm FOR EACH ROW EXECUTE PROCEDURE "after_pm_update"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_pm_update" ON pm;');
        DB::unprepared('DROP FUNCTION after_pm_update();');
    }
}
