<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterReputationInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_reputation_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE users SET reputation = reputation + NEW."value" WHERE "id" = NEW.user_id;
	RETURN NEW;
 END;$$;

CREATE TRIGGER after_reputation_insert AFTER INSERT ON reputations FOR EACH ROW EXECUTE PROCEDURE "after_reputation_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_reputation_insert" ON reputations;');
        DB::unprepared('DROP FUNCTION after_reputation_insert();');
    }
}
