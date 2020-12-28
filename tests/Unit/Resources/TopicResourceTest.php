<?php

namespace Tests\Unit\Resources;

use Coyote\Http\Resources\TopicCollection;
use Coyote\Http\Resources\TopicResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class TopicResourceTest extends TestCase
{
    private $repository;
    private $guest;

    public function setUp(): void
    {
        parent::setUp();

        $faker = Factory::create();

        $this->repository = $this->app[TopicRepositoryInterface::class];
        $this->guest = new Guest($faker->uuid);
    }

    public function testTransformPaginatorToJson()
    {
        $topic = factory(Topic::class)->state('id')->make();
        $paginator = new LengthAwarePaginator([$topic], 1, 10);

        TopicCollection::wrap('data');

        $collection = (new TopicCollection($paginator))
            ->setGuest($this->guest)
            ->setRepository($this->repository);

        $result = $collection->toResponse(request())->getData(true);

        $this->assertEquals(1, $result['meta']['total']);
        $this->assertEquals(1, $result['meta']['current_page']);
        $this->assertEquals($topic['title'], $result['data'][0]['title']);
    }

    public function testTransformResource()
    {
        $user = factory(User::class)->state('id')->make();

        /** @var Topic $topic */
        $topic = factory(Topic::class)->state('id')->make();
        $topic->firstPost->user_id = $user->id;

        TopicResource::withoutWrapping();

        $tracker = new Tracker($topic, $this->guest);
        $result = (new TopicResource($tracker))->toResponse(request())->getData(true);

        $this->assertTrue(isset($result['owner_id']));
        $this->assertEquals($user->id, $result['owner_id']);

        $topic->unsetRelation('firstPost');

        $tracker = new Tracker($topic, $this->guest);
        $result = (new TopicResource($tracker))->toResponse(request())->getData(true);

        $this->assertFalse(isset($result['owner_id']));
    }
}
