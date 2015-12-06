<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterUserSkillsDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION after_user_skills_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  	UPDATE user_skills SET "order" = "order" - 1 WHERE user_id = OLD.user_id AND "order" > OLD."order";

	RETURN NEW;
END;$$;

CREATE TRIGGER after_user_skills_delete AFTER DELETE ON user_skills FOR EACH ROW EXECUTE PROCEDURE "after_user_skills_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_user_skills_delete" ON user_skills;');
        DB::unprepared('DROP FUNCTION after_user_skills_delete();');
    }
}
