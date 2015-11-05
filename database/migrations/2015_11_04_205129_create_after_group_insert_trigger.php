<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterGroupInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION after_group_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO acl_data (group_id, permission_id, "value") SELECT NEW."id", "id", "default" FROM acl_permissions;

	IF NEW.leader_id > 0 THEN
		INSERT INTO user_groups (user_id, group_id) VALUES(NEW.leader_id, NEW."id");
		UPDATE users SET permissions = NULL WHERE "id" = NEW.leader_id;
	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_group_insert AFTER INSERT ON groups FOR EACH ROW EXECUTE PROCEDURE "after_group_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_group_insert" ON groups;');
        DB::unprepared('DROP FUNCTION after_group_insert();');
    }
}
