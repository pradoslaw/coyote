<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropBonusColumnInMicroblogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('microblogs', function (Blueprint $table) {
            $table->dropIndex('microblogs_parent_id_deleted_at_votes_bonus_index');

            $table->dropColumn('bonus');

            $table->index(['parent_id', 'deleted_at', 'votes', 'is_sponsored']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('microblogs', function (Blueprint $table) {
            $table->dropIndex('microblogs_parent_id_deleted_at_votes_is_sponsored_index');
            $table->smallInteger('bonus')->nullable();
            $table->index(['parent_id', 'deleted_at', 'votes', 'bonus']);
        });
    }
}
