<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAfterSessionDeleteTrigger extends Migration
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
CREATE OR REPLACE FUNCTION before_session_delete() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE
	affected INTEGER;
BEGIN
	IF OLD.user_id IS NOT NULL THEN
	    UPDATE users SET ip = OLD.ip, browser = OLD.browser, visited_at = CURRENT_TIMESTAMP(0), visits = visits + 1, is_online = CASE WHEN (SELECT COUNT(*) FROM sessions WHERE user_id = OLD.user_id) >= 1 THEN 1 ELSE 0 END WHERE "id" = OLD.user_id;
	   
	    WITH rows AS (UPDATE session_log SET updated_at = CURRENT_TIMESTAMP(0), ip = OLD.ip, url = OLD.url, browser = OLD.browser, robot = OLD.robot WHERE user_id = OLD.user_id RETURNING 1)
	    SELECT COUNT(*) INTO affected FROM rows;
			
		IF affected = 0 THEN
		    INSERT INTO session_log (user_id, created_at, updated_at, ip, url, browser, robot) VALUES(OLD.user_id, OLD.created_at, OLD.updated_at, OLD.ip, OLD.url, OLD.browser, OLD.robot);
		END IF;
	ELSE
	    WITH rows AS (UPDATE session_log SET updated_at = CURRENT_TIMESTAMP(0), ip = OLD.ip, url = OLD.url, browser = OLD.browser, robot = OLD.robot WHERE id = OLD.id RETURNING 1)
		SELECT COUNT(*) INTO affected FROM rows;
			
		IF affected = 0  THEN
			INSERT INTO session_log (id, created_at, updated_at, ip, url, browser, robot) VALUES(OLD.id, OLD.created_at, OLD.updated_at, OLD.ip, OLD.url, OLD.browser, OLD.robot);
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
CREATE OR REPLACE FUNCTION before_session_delete() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE
	affected INTEGER;
BEGIN
	IF OLD.user_id IS NOT NULL THEN
	    UPDATE users SET ip = OLD.ip, browser = OLD.browser, visited_at = CURRENT_TIMESTAMP(0), visits = visits + 1 WHERE "id" = OLD.user_id;
	   
	    WITH rows AS (UPDATE session_log SET updated_at = CURRENT_TIMESTAMP(0), ip = OLD.ip, url = OLD.url, browser = OLD.browser, robot = OLD.robot WHERE user_id = OLD.user_id RETURNING 1)
	    SELECT COUNT(*) INTO affected FROM rows;
			
		IF affected = 0 THEN
		    INSERT INTO session_log (user_id, created_at, updated_at, ip, url, browser, robot) VALUES(OLD.user_id, OLD.created_at, OLD.updated_at, OLD.ip, OLD.url, OLD.browser, OLD.robot);
		END IF;
	ELSE
	    WITH rows AS (UPDATE session_log SET updated_at = CURRENT_TIMESTAMP(0), ip = OLD.ip, url = OLD.url, browser = OLD.browser, robot = OLD.robot WHERE id = OLD.id RETURNING 1)
		SELECT COUNT(*) INTO affected FROM rows;
			
		IF affected = 0  THEN
			INSERT INTO session_log (id, created_at, updated_at, ip, url, browser, robot) VALUES(OLD.id, OLD.created_at, OLD.updated_at, OLD.ip, OLD.url, OLD.browser, OLD.robot);
		END IF;
	END IF;

	RETURN NEW;
END;$$;
        ');
    }
}
