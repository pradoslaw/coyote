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

        $channels = ['db', 'mail', 'push'];

        $this->db->table('notification_settings')->whereNull('channel')->orderBy('id')->chunk(10000, function ($result) use ($channels) {
            foreach ($result as $row) {
                foreach ($channels as $channel) {
                    $this->db->table('notification_settings')->insert([
                        'channel' => $channel,
                        'user_id' => $row->user_id,
                        'type_id' => $row->type_id,
                        'is_enabled' => value(function () use ($channel, $row) {
                            if ($channel == 'push') {
                                return true;
                            } else if ($channel == 'db') {
                                return (bool) $row->profile;
                            } else {
                                return (bool) $row->email;
                            }
                        })
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = 'DELETE FROM notification_settings WHERE channel IS NOT NULL';
        $this->db->unprepared($sql);

        $sql = "ALTER TABLE notification_settings DROP COLUMN channel";
        $this->db->unprepared($sql);

        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
            $table->smallInteger('profile')->change();
            $table->smallInteger('email')->change();
        });
    }
}
