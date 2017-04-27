<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUnusedIndexes extends Migration
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
            $table->dropIndex('forum_track_forum_id_index');
        });

        $this->schema->table('password_resets', function (Blueprint $table) {
            $table->dropIndex('password_resets_email_index');
            $table->dropIndex('password_resets_token_index');
        });

        $this->schema->table('forum_orders', function (Blueprint $table) {
            $table->dropIndex('forum_orders_forum_id_index');
        });

        $this->schema->table('group_permissions', function (Blueprint $table) {
            $table->dropIndex('group_permissions_group_id_index');
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
            $table->index('forum_id');
        });

        $this->schema->table('password_resets', function (Blueprint $table) {
            $table->index('email');
            $table->index('token');
        });

        $this->schema->table('forum_orders', function (Blueprint $table) {
            $table->index('forum_id');
        });

        $this->schema->table('group_permissions', function (Blueprint $table) {
            $table->index('group_id');
        });
    }
}
