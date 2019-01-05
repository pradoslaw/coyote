<?php

use Illuminate\Database\Seeder;

class ActivityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
                    'user_name' => $item->user_name
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
}
