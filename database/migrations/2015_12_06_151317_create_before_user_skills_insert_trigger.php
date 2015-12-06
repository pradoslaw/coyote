<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeforeUserSkillsInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION before_user_skills_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	NEW."order" := (SELECT MAX("order") FROM user_skills WHERE user_id = NEW.user_id) + 1;

	RETURN NEW;
END;$$;

CREATE TRIGGER before_user_skills_insert BEFORE INSERT ON user_skills FOR EACH ROW EXECUTE PROCEDURE "before_user_skills_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "before_user_skills_insert" ON user_skills;');
        DB::unprepared('DROP FUNCTION before_user_skills_insert();');
    }
}
