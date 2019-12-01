<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUnusedFromTopicTrackTable extends Migration
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
            $table->dropColumn(['user_id', 'session_id']);
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
            $table->integer('user_id')->nullable();
            $table->string('session_id')->nullable();
        });
    }
}
