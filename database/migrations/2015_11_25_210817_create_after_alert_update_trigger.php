<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterAlertUpdateTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_alert_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF NEW.read_at IS NOT NULL AND OLD.read_at IS NULL THEN
 		UPDATE users SET alerts_unread = alerts_unread -1
 		WHERE "id" = NEW.user_id;
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_alert_update AFTER UPDATE ON alerts FOR EACH ROW EXECUTE PROCEDURE "after_alert_update"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_alert_update" ON alerts;');
        DB::unprepared('DROP FUNCTION after_alert_update();');
    }
}
