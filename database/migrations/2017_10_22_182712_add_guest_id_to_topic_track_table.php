<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGuestIdToTopicTrackTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('topic_track', function (Blueprint $table) {
            $table->addColumn('uuid', 'guest_id')->nullable();

            $table->unique(['topic_id', 'guest_id']);
        });

        $this->db->update('UPDATE topic_track SET guest_id = session_id::UUID WHERE session_id IS NOT NULL');
        $this->db->update('UPDATE topic_track SET guest_id = (SELECT users.guest_id FROM users WHERE users.id = user_id)::UUID WHERE user_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('topic_track', function (Blueprint $table) {
            $table->dropColumn('guest_id');
        });
    }
}
