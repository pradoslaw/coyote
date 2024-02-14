<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeMicroblogsIndexes extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('microblogs', function (Blueprint $table) {
            $table->index([
                $this->db->raw('is_sponsored DESC'),
                $this->db->raw('score DESC'),
                'parent_id',
                'deleted_at'],
                'microblogs_is_sponsored desc_score desc_parent_id_deleted_at_in');

            $table->dropIndex('microblogs_is_sponsored_id_parent_id_deleted_at_index');
            $table->dropIndex('microblogs_parent_id_deleted_at_votes_is_sponsored_index');

            $table->index([
                $this->db->raw('is_sponsored DESC'),
                $this->db->raw('id DESC'),
                'parent_id',
                'deleted_at'],
                'microblogs_is_sponsored desc_id desc_parent_id_deleted_at_index');
            $table->index(['parent_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('microblogs', function (Blueprint $table) {
            $table->dropIndex('microblogs_is_sponsored desc_score desc_parent_id_deleted_at_in');
            $table->dropIndex('microblogs_is_sponsored desc_id desc_parent_id_deleted_at_index');
            $table->dropIndex('microblogs_parent_id_deleted_at_index');

            $table->index(['is_sponsored', 'id', 'parent_id', 'deleted_at'], 'microblogs_is_sponsored_id_parent_id_deleted_at_index');
            $table->index(['parent_id', 'deleted_at', 'votes'], 'microblogs_parent_id_deleted_at_votes_is_sponsored_index');
        });
    }
}
