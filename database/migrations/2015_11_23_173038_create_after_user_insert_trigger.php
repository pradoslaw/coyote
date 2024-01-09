<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterUserInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_user_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO alert_settings (type_id, user_id, profile, email)
	SELECT "id", NEW."id", profile, email
	FROM alert_types;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_user_insert AFTER INSERT ON users FOR EACH ROW EXECUTE PROCEDURE "after_user_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_user_insert" ON users;');
        DB::unprepared('DROP FUNCTION after_user_insert();');
    }
}
