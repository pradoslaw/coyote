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

            $table->index(['topic_id', 'guest_id']);

            $table->dropIndex('topic_track_session_id_index');
            $table->dropUnique('topic_track_topic_id_session_id_unique');
            $table->dropUnique('topic_track_topic_id_user_id_unique');
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

            $table->index('session_id');
            $table->unique(['topic_id', 'user_id']);
            $table->unique(['topic_id', 'session_id']);
        });
    }
}
