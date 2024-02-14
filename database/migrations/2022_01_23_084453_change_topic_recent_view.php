<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeTopicRecentView extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->statement('DROP MATERIALIZED VIEW topic_recent');

        $this->db->statement('CREATE MATERIALIZED VIEW topic_recent AS
         SELECT
            topics.id AS id,
            forum_id,
            topics.title,
            topics.slug,
            last_post_created_at,
            views,
            score,
            replies,
            deleted_at,
            first_post_id,
            rank,
            forums.name AS forum,
            forums.slug AS forum_slug
         FROM topics
         JOIN forums ON forums.id = forum_id
         WHERE topics.is_locked = 0 AND forums.is_locked = 0 AND enable_homepage = true
         ORDER BY topics.id DESC LIMIT 3000');

        $this->schema->table('topic_recent', function (Blueprint $table) {
            $table->index('id');
            $table->index(['replies', $this->db->raw('rank DESC')],
                'topic_recent_replies_rank desc_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->statement('DROP MATERIALIZED VIEW topic_recent');

        $this->db->statement('CREATE MATERIALIZED VIEW IF NOT EXISTS topic_recent AS
         SELECT
            topics.id AS id,
            forum_id,
            topics.title,
            topics.slug,
            last_post_created_at,
            views,
            score,
            replies,
            deleted_at,
            first_post_id,
            rank,
            forums.name AS forum,
            forums.slug AS forum_slug
         FROM topics
         JOIN forums ON forums.id = forum_id
         WHERE topics.is_locked = 0 AND forums.is_locked = 0
         ORDER BY topics.id DESC LIMIT 3000');

        $this->schema->table('topic_recent', function (Blueprint $table) {
            $table->index('id');
            $table->index(['replies', $this->db->raw('rank DESC')],
                'topic_recent_replies_rank desc_index');
        });
    }
}
