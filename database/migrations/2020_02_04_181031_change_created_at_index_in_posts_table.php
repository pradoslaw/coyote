<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCreatedAtIndexInPostsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_created_at_index');
            $table->index([\Illuminate\Support\Facades\DB::raw('topic_id DESC'), \Illuminate\Support\Facades\DB::raw('created_at ASC')], 'posts_topic_id_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_topic_id_created_at_index');
            $table->index('created_at');
        });
    }
}
