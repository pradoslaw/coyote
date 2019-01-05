<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('activities', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->useCurrent();
            $table->smallInteger('forum_id');
            $table->integer('topic_id');
            $table->integer('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->morphs('content');
            $table->string('excerpt')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('forum_id')->references('id')->on('forums')->onDelete('cascade');
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');

            $table->index('forum_id');
        });

        $posts = \Coyote\Post::limit(10)->get();
        $comments = \Coyote\Post\Comment::limit(10)->get();

        $items = collect($posts)->merge($comments)->sortBy('created_at');

        foreach ($items as $item) {
            $data = ['created_at' => $item->created_at->toDateTimeString(), 'user_id' => $item->user_id];

            if (isset($item['topic_id'])) {
                $data += [
                    'topic_id' => $item->topic_id,
                    'forum_id' => $item->forum_id,
                    'excerpt' => excerpt($item->text),
                    'content_id' => $item->id,
                    'content_type' => \Coyote\Post::class,
                    'user_name' => $item->post->user_name
                ];
            } else {
                $data += [
                    'topic_id' => $item->post->topic_id,
                    'forum_id' => $item->post->topic->forum_id,
                    'excerpt' => excerpt($item->text),
                    'content_id' => $item->id,
                    'content_type' => \Coyote\Post\Comment::class
                ];
            }

            $this->db->table('activities')->insert($data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('activities');
    }
}
