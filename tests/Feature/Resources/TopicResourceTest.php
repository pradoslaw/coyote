<?php

namespace Tests\Feature\Resources;

use Coyote\Http\Resources\TopicCollection;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Services\Guest;
use Coyote\Topic;
use Faker\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class TopicResourceTest extends TestCase
{
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app[TopicRepositoryInterface::class];
    }

    public function testTransformPaginatorToJson()
    {
        $faker = Factory::create();

        $guest = new Guest($faker->uuid);
        $topic = factory(Topic::class)->state('id')->make();
        $paginator = new LengthAwarePaginator([$topic], 1, 10);

        $collection = (new TopicCollection($paginator))
            ->setGuest($guest)
            ->setRepository($this->repository);

        $result = $collection->toResponse(request())->getData(true);

        $this->assertEquals(1, $result['meta']['total']);
        $this->assertEquals(1, $result['meta']['current_page']);
        $this->assertEquals($topic['subject'], $result['data'][0]['subject']);
    }
}
