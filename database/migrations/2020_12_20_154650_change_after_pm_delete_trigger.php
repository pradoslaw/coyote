<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAfterPmDeleteTrigger extends Migration
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
CREATE OR REPLACE FUNCTION after_pm_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	IF (SELECT COUNT(*) FROM pm WHERE text_id = OLD.text_id) = 0 THEN
 	   DELETE FROM pm_text WHERE "id" = OLD.text_id;
 	END IF;

 	IF OLD.folder = 1 THEN
 	   UPDATE users SET pm = pm - 1 WHERE "id" = OLD.user_id;

 	   IF OLD.read_at IS NULL THEN
 	       UPDATE users SET pm_unread = count_unread_messages(OLD.user_id) WHERE "id" = OLD.user_id;
 	   END IF;
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
CREATE OR REPLACE FUNCTION after_pm_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	IF (SELECT COUNT(*) FROM pm WHERE text_id = OLD.text_id) = 0 THEN
 	   DELETE FROM pm_text WHERE "id" = OLD.text_id;
 	END IF;

 	IF OLD.folder = 1 THEN
 	   UPDATE users SET pm = pm - 1 WHERE "id" = OLD.user_id;

 	   IF OLD.read_at IS NULL THEN
 	       UPDATE users SET pm_unread = pm_unread - 1 WHERE "id" = OLD.user_id;
 	   END IF;
 	END IF;

	RETURN NEW;
END;$$;
        ');
    }
}
