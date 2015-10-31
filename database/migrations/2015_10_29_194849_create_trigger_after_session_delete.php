<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTriggerAfterSessionDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION before_session_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF OLD.user_id IS NOT NULL THEN
	   UPDATE users SET ip = OLD.ip, browser = OLD.browser, visited_at = CURRENT_TIMESTAMP(0), visits = visits + 1 WHERE "id" = OLD.user_id;
	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER before_session_delete AFTER DELETE ON sessions FOR EACH ROW EXECUTE PROCEDURE "before_session_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "before_session_delete" ON sessions;');
        DB::unprepared('DROP FUNCTION before_session_delete();');
    }
}
