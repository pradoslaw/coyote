<?php

use Illuminate\Database\Migrations\Migration;

class ChangeAfterUserInsertTrigger extends Migration
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
CREATE OR REPLACE FUNCTION after_user_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO notification_settings (type_id, user_id, channel, is_enabled)
	SELECT "id", NEW."id", t.channel, "default"::jsonb ? t.channel::varchar
    FROM notification_types
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
CREATE OR REPLACE FUNCTION after_user_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO alert_settings (type_id, user_id, profile, email)
	SELECT "id", NEW."id", profile, email
	FROM alert_types;

	RETURN NEW;
END;$$;
        ');
    }
}
