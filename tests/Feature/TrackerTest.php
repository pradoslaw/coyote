<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\Forum\Tracker;
use Coyote\Topic;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Faker;

class TrackerTest extends TestCase
{
    use DatabaseTransactions;

    private $forum;
    private $guestId;

    public function setUp()
    {
        parent::setUp();

        $faker = Faker\Factory::create();

        $this->forum = factory(Forum::class)->create();
        $this->guestId = $faker->uuid;

        $this->app['request']->setLaravelSession(app('session.store'));

        session()->put('created_at', Carbon::now()->timestamp);
    }

    public function testGetMarkTimeWithJustRegisteredUser()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $post = factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1)]);

        $marker = Tracker::make($topic);

        // is post new
        $this->assertTrue($marker->getMarkTime($this->guestId) > $post->created_at);

        $post = factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->addMinute(1)]);

        // is post new
        $this->assertTrue($post->created_at > $marker->getMarkTime($this->guestId));
    }

    public function testMarkAsReadWithOnlyOneTopic()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1)]);

        $tracker = Tracker::make($topic);
        $tracker->asRead($this->guestId, Carbon::now());

        $this->assertDatabaseHas('forum_track', ['forum_id' => $this->forum->id, 'guest_id' => $this->guestId]);
    }

    public function testMarkAsReadWithMultipleTopics()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1)]);

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1)]);

        $tracker = Tracker::make($topic);
        $tracker->asRead($this->guestId, Carbon::now());

        $this->assertDatabaseHas('topic_track', ['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'guest_id' => $this->guestId]);
    }

    public function testMarkAsReadWithDeletedTopics()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id, 'deleted_at' => Carbon::now()]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1), 'deleted_at' => Carbon::now()]);

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1)]);

        $tracker = Tracker::make($topic);
        $tracker->asRead($this->guestId, Carbon::now());

        $this->assertDatabaseHas('forum_track', ['forum_id' => $this->forum->id, 'guest_id' => $this->guestId]);
    }
}
