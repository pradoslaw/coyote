<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterFirewallInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_firewall_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.user_id IS NOT NULL THEN
 	    UPDATE users SET is_blocked = 1 WHERE "id" = NEW.user_id;
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_firewall_insert AFTER INSERT ON firewall FOR EACH ROW EXECUTE PROCEDURE "after_firewall_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_firewall_insert" ON firewall;');
        DB::unprepared('DROP FUNCTION after_firewall_insert();');
    }
}
