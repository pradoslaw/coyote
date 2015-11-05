<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterUserGroupInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION after_user_group_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE users SET permissions = NULL WHERE "id" = NEW.user_id;
	RETURN NEW;
END;$$;

CREATE TRIGGER after_user_group_insert AFTER INSERT ON user_groups FOR EACH ROW EXECUTE PROCEDURE "after_user_group_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_user_group_insert" ON user_groups;');
        DB::unprepared('DROP FUNCTION after_user_group_insert();');
    }
}
