<?php

use Illuminate\Database\Migrations\Migration;

class RenameSubjectInTopicRecentView extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = 'ALTER MATERIALIZED VIEW topic_recent RENAME COLUMN "subject" TO title;';

        $this->db->statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = 'ALTER MATERIALIZED VIEW topic_recent RENAME COLUMN "title" TO subject;';

        $this->db->statement($sql);
    }
}
