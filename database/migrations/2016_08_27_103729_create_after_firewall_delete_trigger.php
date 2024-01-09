<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterFirewallDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_firewall_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	IF OLD.user_id IS NOT NULL THEN
 	    IF (SELECT COUNT(*) FROM firewall WHERE user_id = OLD.user_id) = 0 THEN
 	        UPDATE users SET is_blocked = 0 WHERE "id" = OLD.user_id;
 	    END IF;
    END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_firewall_delete AFTER DELETE ON firewall FOR EACH ROW EXECUTE PROCEDURE "after_firewall_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_firewall_delete" ON firewall;');
        DB::unprepared('DROP FUNCTION after_firewall_delete();');
    }
}
