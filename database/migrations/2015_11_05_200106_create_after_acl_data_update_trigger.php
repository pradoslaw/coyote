<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterAclDataUpdateTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION after_acl_data_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE "users" SET permissions = NULL WHERE "id" IN(
		SELECT user_id
		FROM user_groups
		WHERE group_id = NEW.group_id
	);

	RETURN NEW;
 END;$$;

CREATE TRIGGER after_acl_data_update AFTER UPDATE ON acl_data FOR EACH ROW EXECUTE PROCEDURE "after_acl_data_update"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_acl_data_update" ON acl_data;');
        DB::unprepared('DROP FUNCTION after_acl_data_update();');
    }
}
