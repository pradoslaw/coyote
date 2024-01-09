<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterAlertTypeInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_alert_type_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO alert_settings (type_id, user_id, profile, email)
	SELECT NEW."id", "id", NEW.profile, NEW.email
	FROM users;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_alert_type_insert AFTER INSERT ON alert_types FOR EACH ROW EXECUTE PROCEDURE "after_alert_type_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_alert_type_insert" ON alert_types;');
        DB::unprepared('DROP FUNCTION after_alert_type_insert();');
    }
}
