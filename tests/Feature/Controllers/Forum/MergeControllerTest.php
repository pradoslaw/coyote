<?php

namespace Tests\Feature\Controllers\Forum;

use Coyote\Permission;
use Coyote\Post;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MergeControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testMergeTwoPosts()
    {
        $user = $this->createUserWithGroup();

        $forum = $this->createForum([], $user->groups()->first()->id);
        $forum->permissions()->create(['value' => 1, 'group_id' => $user->groups()->first()->id, 'permission_id' => Permission::where('name', 'forum-merge')->get()->first()->id]);

        $topic = $this->createTopic($forum->id);
        $post = factory(Post::class)->create(['topic_id' => $topic->id, 'forum_id' => $forum->id]);

        $topic->refresh();

        $original = $topic->firstPost->text;

        $response = $this->actingAs($user)->json(
            'POST',
            "/Forum/Post/Merge/$post->id"
        );

        $response->assertJsonFragment([
            'text' => $original . "\n\n" . $post->text
        ]);

        $post->refresh();

        $this->assertNotNull($post->deleted_at);
    }
}
