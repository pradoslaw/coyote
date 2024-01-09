<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterUserSkillDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_user_skill_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  	UPDATE user_skills SET "order" = "order" - 1 WHERE user_id = OLD.user_id AND "order" > OLD."order";

	RETURN NEW;
END;$$;

CREATE TRIGGER after_user_skill_delete AFTER DELETE ON user_skills FOR EACH ROW EXECUTE PROCEDURE "after_user_skill_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_user_skill_delete" ON user_skills;');
        DB::unprepared('DROP FUNCTION after_user_skill_delete();');
    }
}
