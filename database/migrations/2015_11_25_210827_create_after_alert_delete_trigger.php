<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterAlertDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_alert_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE users
 	SET alerts = (alerts -1), alerts_unread = (alerts_unread - CASE WHEN OLD.read_at IS NOT NULL THEN 0 ELSE 1 END)
 	WHERE "id" = OLD.user_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_alert_delete AFTER DELETE ON alerts FOR EACH ROW EXECUTE PROCEDURE "after_alert_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_alert_delete" ON alerts;');
        DB::unprepared('DROP FUNCTION after_alert_delete();');
    }
}
