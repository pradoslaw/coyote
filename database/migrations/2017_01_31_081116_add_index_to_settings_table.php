<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToSettingsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('settings', function (Blueprint $table) {
            $table->dropIndex('settings_name_user_id_session_id_index');
            $table->index(['user_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('settings', function (Blueprint $table) {
            $table->dropIndex('settings_user_id_session_id_index');
            $table->index(['name', 'user_id', 'session_id']);
        });
    }
}
