<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastUsedAtToTagsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->timestampTz('last_used_at')->nullable();
        });

        $sql = '
        UPDATE tags
        SET last_used_at = greatest(
            (select max(microblogs.created_at)
             from microblog_tags
                      join microblogs on
                 microblogs.id = microblog_tags.microblog_id
             where microblog_tags.tag_id = tags.id),

            (select max(topics.created_at)
             from topic_tags
                      join topics on
                 topics.id = topic_tags.topic_id
             where topic_tags.tag_id = tags.id),

            (select max(jobs.created_at)
             from job_tags
                      join jobs on
                 jobs.id = job_tags.job_id
             where job_tags.tag_id = tags.id)
        )';

        $this->db->statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('last_used_at');
        });
    }
}
