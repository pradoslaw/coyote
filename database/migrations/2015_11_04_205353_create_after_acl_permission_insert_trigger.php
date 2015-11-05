<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterAclPermissionInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION after_acl_permission_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	INSERT INTO acl_data (group_id, permission_id, "value") SELECT "id", NEW."id", NEW."default" FROM groups;
 	UPDATE "users" SET permissions = NULL WHERE permissions IS NOT NULL;

	RETURN NEW;
 END;$$;

CREATE TRIGGER after_acl_permission_insert AFTER INSERT ON acl_permissions FOR EACH ROW EXECUTE PROCEDURE "after_acl_permission_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_acl_permission_insert" ON acl_permissions;');
        DB::unprepared('DROP FUNCTION after_acl_permission_insert();');
    }
}
