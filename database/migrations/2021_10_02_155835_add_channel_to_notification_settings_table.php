<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChannelToNotificationSettingsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(false);
            $table->smallInteger('profile')->nullable()->change();
            $table->smallInteger('email')->nullable()->change();
        });

        $sql = "ALTER TABLE notification_settings ADD COLUMN channel channel DEFAULT NULL";
        $this->db->unprepared($sql);

        $sql = "INSERT INTO notification_settings (type_id, user_id, channel, is_enabled) SELECT type_id, user_id, 'db', profile::int::bool FROM notification_settings WHERE channel IS NULL";
        $this->db->unprepared($sql);

        $sql = "INSERT INTO notification_settings (type_id, user_id, channel, is_enabled) SELECT type_id, user_id, 'mail', email::int::bool FROM notification_settings WHERE channel IS NULL";
        $this->db->unprepared($sql);

        $sql = "INSERT INTO notification_settings (type_id, user_id, channel, is_enabled) SELECT type_id, user_id, 'push', true FROM notification_settings WHERE channel IS NULL";
        $this->db->unprepared($sql);

        $sql = "DELETE FROM notification_settings WHERE channel is null";
        $this->db->unprepared($sql);

        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn(['profile', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = "ALTER TABLE notification_settings DROP COLUMN channel";
        $this->db->unprepared($sql);

        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
            $table->smallInteger('profile');
            $table->smallInteger('email');
        });
    }
}
