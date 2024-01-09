<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterPermissionInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION after_permission_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	INSERT INTO group_permissions (group_id, permission_id, "value") SELECT "id", NEW."id", NEW."default" FROM groups;

	RETURN NEW;
 END;$$;

CREATE TRIGGER after_permission_insert AFTER INSERT ON permissions FOR EACH ROW EXECUTE PROCEDURE "after_permission_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_permission_insert" ON permissions;');
        DB::unprepared('DROP FUNCTION after_permission_insert();');
    }
}
