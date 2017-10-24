<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToTopicTrackTable extends Migration
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
            $table->dropIndex('topic_track_topic_id_guest_id_index');

            $table->unique(['topic_id', 'guest_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('topic_track', function (Blueprint $table) {
            $table->dropUnique('topic_track_topic_id_guest_id_unique');

            $table->index(['topic_id', 'guest_id']);
        });
    }
}
