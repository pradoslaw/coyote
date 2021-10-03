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

        $this->db->table('notification_settings')->whereNull('channel')->orderBy('id')->chunk(100000, function ($result) use ($channels) {
            $data = [];

            foreach ($result as $row) {
                foreach ($channels as $channel) {
                    $data[] = [
                        'channel' => $channel,
                        'user_id' => $row->user_id,
                        'type_id' => $row->type_id,
                        'is_enabled' => value(function () use ($channel, $row) {
                            if ($channel == 'push') {
                                return true;
                            } elseif ($channel == 'db') {
                                return (bool) $row->profile;
                            } else {
                                return (bool) $row->email;
                            }
                        })
                    ];
                }
            }

            $this->db->table('notification_settings')->insert($data);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        $result = $this->db->table('notification_settings')->selectRaw('user_id, type_id, array_to_string(array_agg(channel), \',\')')->groupBy(['user_id', 'type_id'])->get();
//
//
//            $data = [];
//
//            foreach ($result as $row) {
//                $channels = explode(',', $row->array_to_string);
//
//                $data[] = [
//                    'user_id'       => $row->user_id,
//                    'type_id'       => $row->type_id,
//                    'profile'       => in_array('db', $channels),
//                    'email'         => in_array('mail', $channels)
//                ];
//            }
//
//            $this->db->table('notification_settings')->insert($data);


//        $sql = 'DELETE FROM notification_settings WHERE channel IS NOT NULL';
//        $this->db->unprepared($sql);

        $sql = "ALTER TABLE notification_settings DROP COLUMN channel";
        $this->db->unprepared($sql);

        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
            $table->smallInteger('profile')->change();
            $table->smallInteger('email')->change();
        });
    }
}
