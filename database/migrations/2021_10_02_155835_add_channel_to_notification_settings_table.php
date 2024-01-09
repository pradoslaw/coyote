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

        $this->db->unprepared("ALTER TABLE notification_settings ADD COLUMN channel channel DEFAULT NULL");
        $this->db->unprepared("INSERT INTO notification_settings (type_id, user_id, channel, is_enabled) SELECT type_id, user_id, 'db', profile::int::bool FROM notification_settings WHERE channel IS NULL");
        $this->db->unprepared("INSERT INTO notification_settings (type_id, user_id, channel, is_enabled) SELECT type_id, user_id, 'mail', email::int::bool FROM notification_settings WHERE channel IS NULL");
        $this->db->unprepared("INSERT INTO notification_settings (type_id, user_id, channel, is_enabled) SELECT type_id, user_id, 'push', true FROM notification_settings WHERE channel IS NULL");
        $this->db->unprepared("DELETE FROM notification_settings WHERE channel is null");

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
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->smallInteger('profile')->nullable();
            $table->smallInteger('email')->nullable();
        });

        $this->db->unprepared("DELETE FROM notification_settings WHERE profile IS NULL");
        $this->db->unprepared('ALTER TABLE notification_settings DROP COLUMN channel');

        Schema::table('notification_settings', function (Blueprint $table) {
            $table->smallInteger('email')->nullable(false)->change();
            $table->smallInteger('profile')->nullable(false)->change();
            $table->dropColumn('is_enabled');
        });
    }
}
