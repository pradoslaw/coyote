<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMicroblogsToTagsTable extends Migration
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
            $table->integer('microblogs')->default(0);
        });

        $this->db
            ->table('tags')
            ->update([
                'microblogs' => $this->db->raw(
                    '(SELECT COUNT(*)
                    FROM microblog_tags
                        JOIN microblogs ON microblogs.id = microblog_tags.microblog_id
                    WHERE microblog_tags.tag_id = tags.id AND microblogs.deleted_at IS NULL)'
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
            $table->dropColumn('microblogs');
        });
    }
}
