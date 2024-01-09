<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameAlertsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_alert_type_insert" ON alert_types;');
        DB::unprepared('DROP FUNCTION after_alert_type_insert();');

        DB::unprepared('DROP TRIGGER IF EXISTS "after_alert_insert" ON alerts;');
        DB::unprepared('DROP FUNCTION after_alert_insert();');

        DB::unprepared('DROP TRIGGER IF EXISTS "after_alert_update" ON alerts;');
        DB::unprepared('DROP FUNCTION after_alert_update();');

        DB::unprepared('DROP TRIGGER IF EXISTS "after_alert_delete" ON alerts;');
        DB::unprepared('DROP FUNCTION after_alert_delete();');

        $this->schema->rename('alerts', 'notifications');
        $this->schema->rename('alert_types', 'notification_types');
        $this->schema->rename('alert_settings', 'notification_settings');
        $this->schema->rename('alert_senders', 'notification_senders');

        $this->schema->table('notification_senders', function (Blueprint $table) {
            $table->renameColumn('alert_id', 'notification_id');
        });

        $this->schema->table('users', function (Blueprint $table) {
            $table->renameColumn('alerts', 'notifications');
            $table->renameColumn('alerts_unread', 'notifications_unread');
        });

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_notification_type_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO notification_settings (type_id, user_id, profile, email)
	SELECT NEW."id", "id", NEW.profile, NEW.email
	FROM users;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_notification_type_insert AFTER INSERT ON notification_types FOR EACH ROW EXECUTE PROCEDURE "after_notification_type_insert"();
        ');

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_notification_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE users
 	SET notifications = (notifications + 1), notifications_unread = (notifications_unread + 1)
 	WHERE "id" = NEW.user_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_notification_insert AFTER INSERT ON notifications FOR EACH ROW EXECUTE PROCEDURE "after_notification_insert"();
        ');

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_notification_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF NEW.read_at IS NOT NULL AND OLD.read_at IS NULL THEN
 		UPDATE users SET notifications_unread = notifications_unread -1
 		WHERE "id" = NEW.user_id;
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_notification_update AFTER UPDATE ON notifications FOR EACH ROW EXECUTE PROCEDURE "after_notification_update"();
        ');

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_notification_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE users
 	SET notifications = (notifications -1), notifications_unread = (notifications_unread - CASE WHEN OLD.read_at IS NOT NULL THEN 0 ELSE 1 END)
 	WHERE "id" = OLD.user_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_notification_delete AFTER DELETE ON notifications FOR EACH ROW EXECUTE PROCEDURE "after_notification_delete"();
        ');

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_user_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO notification_settings (type_id, user_id, profile, email)
	SELECT "id", NEW."id", profile, email
	FROM notification_types;

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
        $this->schema->table('notification_senders', function (Blueprint $table) {
            $table->renameColumn('notification_id', 'alert_id');
        });

        $this->schema->table('users', function (Blueprint $table) {
            $table->renameColumn('notifications', 'alerts');
            $table->renameColumn('notifications_unread', 'alerts_unread');
        });

        $this->schema->rename('notifications', 'alerts');
        $this->schema->rename('notification_types', 'alert_types');
        $this->schema->rename('notification_settings', 'alert_settings');
        $this->schema->rename('notification_senders', 'alert_senders');

        DB::unprepared('DROP TRIGGER IF EXISTS "after_notification_type_insert" ON alert_types;');
        DB::unprepared('DROP FUNCTION after_notification_type_insert();');

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_alert_type_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	INSERT INTO alert_settings (type_id, user_id, profile, email)
	SELECT NEW."id", "id", NEW.profile, NEW.email
	FROM users;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_alert_type_insert AFTER INSERT ON alert_types FOR EACH ROW EXECUTE PROCEDURE "after_alert_type_insert"();
        ');

        DB::unprepared('DROP TRIGGER IF EXISTS "after_notification_insert" ON alerts;');
        DB::unprepared('DROP FUNCTION after_notification_insert();');

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_alert_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE users
 	SET alerts = (alerts + 1), alerts_unread = (alerts_unread + 1)
 	WHERE "id" = NEW.user_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_alert_insert AFTER INSERT ON alerts FOR EACH ROW EXECUTE PROCEDURE "after_alert_insert"();
        ');

        DB::unprepared('DROP TRIGGER IF EXISTS "after_notification_update" ON alerts;');
        DB::unprepared('DROP FUNCTION after_notification_update();');

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_alert_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	IF NEW.read_at IS NOT NULL AND OLD.read_at IS NULL THEN
 		UPDATE users SET alerts_unread = alerts_unread -1
 		WHERE "id" = NEW.user_id;
 	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_alert_update AFTER UPDATE ON alerts FOR EACH ROW EXECUTE PROCEDURE "after_alert_update"();
        ');

        DB::unprepared('DROP TRIGGER IF EXISTS "after_notification_delete" ON alerts;');
        DB::unprepared('DROP FUNCTION after_notification_delete();');

        DB::unprepared('
CREATE OR REPLACE FUNCTION after_alert_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
	UPDATE users
 	SET alerts = (alerts -1), alerts_unread = (alerts_unread - CASE WHEN OLD.read_at IS NOT NULL THEN 0 ELSE 1 END)
 	WHERE "id" = OLD.user_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_alert_delete AFTER DELETE ON alerts FOR EACH ROW EXECUTE PROCEDURE "after_alert_delete"();
        ');

        DB::unprepared('
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
