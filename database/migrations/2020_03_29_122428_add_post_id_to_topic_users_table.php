<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostIdToTopicUsersTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('topic_users', function (Blueprint $table) {
            $table->integer('post_id')->nullable();
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });

        $this->db->update('UPDATE topic_users SET post_id = (SELECT id FROM posts WHERE posts.topic_id = topic_users.topic_id AND posts.user_id = topic_users.user_id ORDER BY id ASC LIMIT 1)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('topic_users', function (Blueprint $table) {
            $table->dropColumn('post_id');
        });
    }
}
