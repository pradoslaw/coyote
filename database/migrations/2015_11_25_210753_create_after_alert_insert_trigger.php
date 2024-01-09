<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterAlertInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_alert_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE users
 	SET alerts = (alerts + 1), alerts_unread = (alerts_unread + 1)
 	WHERE "id" = NEW.user_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_alert_insert AFTER INSERT ON alerts FOR EACH ROW EXECUTE PROCEDURE "after_alert_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_alert_insert" ON alerts;');
        DB::unprepared('DROP FUNCTION after_alert_insert();');
    }
}
