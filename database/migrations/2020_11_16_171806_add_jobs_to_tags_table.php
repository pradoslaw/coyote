<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobsToTagsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('tags', function (Blueprint $table) {
            $table->integer('jobs')->default(0);
        });

        $this->db
            ->table('tags')
            ->update([
                'jobs' => $this->db->raw(
                    '(SELECT COUNT(*)
                    FROM job_tags
                        JOIN jobs ON jobs.id = job_tags.job_id
                    WHERE job_tags.tag_id = tags.id AND jobs.deleted_at IS NULL)'
                )
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('tags', function (Blueprint $table) {
            $table->dropColumn('jobs');
        });
    }
}
