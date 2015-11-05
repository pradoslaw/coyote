<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeforeGroupDeleteTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION before_group_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE users SET permissions = NULL WHERE "id" IN(
 		SELECT user_id FROM user_groups WHERE group_id = OLD."id"
 	);

	RETURN OLD;
END;$$;

CREATE TRIGGER before_group_delete BEFORE DELETE ON groups FOR EACH ROW EXECUTE PROCEDURE "before_group_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "before_group_delete" ON groups;');
        DB::unprepared('DROP FUNCTION before_group_delete();');
    }
}
