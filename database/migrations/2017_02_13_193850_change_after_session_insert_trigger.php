<?php

use Illuminate\Database\Migrations\Migration;

class ChangeAfterSessionInsertTrigger extends Migration
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
	    UPDATE users SET is_online = 1, visited_at = CURRENT_TIMESTAMP(0) WHERE "id" = NEW.user_id;
	END IF;

	RETURN NEW;
END;$$;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_session_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF NEW.user_id IS NOT NULL THEN
	    UPDATE users SET is_online = 1 WHERE "id" = NEW.user_id;
	END IF;

	RETURN NEW;
END;$$;
        ');
    }
}
