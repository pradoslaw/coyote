<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSessionIdInSettingsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('session_id', 'guest_id');
        });

        $sql = 'UPDATE settings AS s SET guest_id = u.guest_id FROM users AS u WHERE s.user_id IS NOT NULL AND u.id = s.user_id';
        $this->db->statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('guest_id', 'session_id');
        });
    }
}
