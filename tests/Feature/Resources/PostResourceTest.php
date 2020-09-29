<?php

namespace Tests\Feature\Resources;

use Coyote\Forum;
use Coyote\Http\Resources\PostResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Coyote\Topic;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Faker;

class PostResourceTest extends TestCase
{
    use DatabaseTransactions;

    public function testPostUnreadForNewUser()
    {
        $faker = Faker\Factory::create();

        $guestId = $faker->uuid;

        $forum = factory(Forum::class)->create();
        $topic = factory(Topic::class)->create(['forum_id' => $forum->id]);
        $topic->refresh();

        $guest = new Guest($guestId);
        $guest->setDefaultSessionTime(now()->subMinutes(5)); // simulate session start

        $tracker = new Tracker($topic, $guest);
        $tracker->setRepository($this->app[TopicRepositoryInterface::class]);

        $post = (new PostResource($topic->firstPost))->setTracker($tracker)->toArray(request());

        $this->assertFalse($post['is_read']);

        $tracker->asRead($topic->last_post_created_at);

        $post = (new PostResource($topic->firstPost))->setTracker($tracker)->toArray(request());

        $this->assertTrue($post['is_read']);
    }
}
