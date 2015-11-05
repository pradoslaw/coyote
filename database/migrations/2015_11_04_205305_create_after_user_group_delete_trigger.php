<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterUserGroupDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION after_user_group_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE users SET permissions = NULL WHERE "id" = OLD.user_id;
	RETURN NEW;
END;$$;

CREATE TRIGGER after_user_group_delete AFTER DELETE ON user_groups FOR EACH ROW EXECUTE PROCEDURE "after_user_group_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_user_group_delete" ON user_groups;');
        DB::unprepared('DROP FUNCTION after_user_group_delete();');
    }
}
