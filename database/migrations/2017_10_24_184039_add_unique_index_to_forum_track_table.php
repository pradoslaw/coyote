<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToForumTrackTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('forum_track', function (Blueprint $table) {
            $table->dropIndex('forum_track_forum_id_guest_id_index');

            $table->unique(['forum_id', 'guest_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('forum_track', function (Blueprint $table) {
            $table->dropUnique('forum_track_forum_id_guest_id_unique');

            $table->index(['forum_id', 'guest_id']);
        });
    }
}
