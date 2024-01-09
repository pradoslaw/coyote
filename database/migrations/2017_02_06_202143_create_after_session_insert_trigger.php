<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterSessionInsertTrigger extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_session_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF NEW.user_id IS NOT NULL THEN
	    UPDATE users SET is_online = 1 WHERE "id" = NEW.user_id;
	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_session_insert AFTER INSERT ON sessions FOR EACH ROW EXECUTE PROCEDURE "after_session_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_session_insert" ON sessions;');
        $this->db->unprepared('DROP FUNCTION after_session_insert();');
    }
}
