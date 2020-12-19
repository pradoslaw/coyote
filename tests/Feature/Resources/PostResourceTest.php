<?php

namespace Tests\Feature\Resources;

use Coyote\Forum;
use Coyote\Http\Resources\PostResource;
use Coyote\Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
        PostResource::withoutWrapping();
    }

    public function testPostUnreadForNewUser()
    {
        $forum = factory(Forum::class)->create();
        $topic = factory(Topic::class)->create(['forum_id' => $forum->id]);
        $topic->refresh();

        $guest = new Guest($this->faker->uuid);
        $guest->setDefaultSessionTime(now()->subMinutes(5)); // simulate session start

        $tracker = new Tracker($topic, $guest);
        $tracker->setRepository($this->app[TopicRepositoryInterface::class]);

        $post = (new PostResource($topic->firstPost))->setTracker($tracker)->toResponse(request())->getData(true);

        $this->assertFalse($post['is_read']);

        $tracker->asRead($topic->last_post_created_at);

        $post = (new PostResource($topic->firstPost))->setTracker($tracker)->toResponse(request())->getData(true);

        $this->assertTrue($post['is_read']);
    }

    public function testShowIpOnlyForModerators()
    {
        $user = factory(User::class)->make(['id' => $this->faker->numberBetween()]);

        Auth::setUser($user);

        Gate::define('forum-delete', function () {
            return true;
        });

        $forum = factory(Forum::class)->make();
        $topic = factory(Topic::class)->state('id')->make();

        $topic->forum()->associate($forum);

        $post = factory(Post::class)->make();

        $post->topic()->associate($topic);
        $post->forum()->associate($forum);

        $guest = new Guest($this->faker->uuid);

        /** @var Request $request */
        $request = $this->app['request'];

        $resource = (new PostResource($post))->setTracker(new Tracker($topic, $guest));
        $result = $resource->resolve($request);

        $this->assertArrayHasKey('ip', $result);
        $this->assertArrayHasKey('browser', $result);
    }
}
