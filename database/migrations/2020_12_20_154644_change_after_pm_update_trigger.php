<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAfterPmUpdateTrigger extends Migration
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
CREATE OR REPLACE FUNCTION after_pm_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF NEW.read_at IS NOT NULL AND OLD.read_at IS NULL AND NEW.folder = 1 THEN
  		UPDATE users SET pm_unread = count_unread_messages(NEW.user_id) WHERE "id" = NEW.user_id;
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
CREATE OR REPLACE FUNCTION after_pm_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF NEW.read_at IS NOT NULL AND OLD.read_at IS NULL AND NEW.folder = 1 THEN
  		UPDATE users SET pm_unread = pm_unread - 1 WHERE "id" = NEW.user_id;
  	END IF;

	RETURN NEW;
END;$$;
        ');
    }
}
