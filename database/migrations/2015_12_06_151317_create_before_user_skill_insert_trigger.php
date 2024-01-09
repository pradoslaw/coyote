<?php

use Illuminate\Database\Migrations\Migration;

class CreateBeforeUserSkillInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION before_user_skill_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	NEW."order" := (SELECT COALESCE(MAX("order"), 0) FROM user_skills WHERE user_id = NEW.user_id) + 1;

	RETURN NEW;
END;$$;

CREATE TRIGGER before_user_skill_insert BEFORE INSERT ON user_skills FOR EACH ROW EXECUTE PROCEDURE "before_user_skill_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "before_user_skill_insert" ON user_skills;');
        DB::unprepared('DROP FUNCTION before_user_skill_insert();');
    }
}
