<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTopicTrackForumIdIndex extends Migration
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
            $table->dropIndex('topic_track_forum_id_index');
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
            $table->index('forum_id');
        });
    }
}
