<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAfterNotificationTypeInsertTrigger extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_notification_type_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO notification_settings (type_id, user_id, channel, is_enabled)
	SELECT NEW."id", "id", t.channel, NEW."default"::jsonb ? t.channel::varchar
	FROM users
	    CROSS JOIN (SELECT unnest(enum_range(null::channel)) AS channel) as t;

	RETURN NEW;
END;$$;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_notification_type_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO notification_settings (type_id, user_id, profile, email)
	SELECT NEW."id", "id", NEW.profile, NEW.email
	FROM users;

	RETURN NEW;
END;$$;
        ');
    }
}
