<?php

namespace Tests\Feature;

use Coyote\Forum;
use Coyote\Http\Resources\ForumCollection;
use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Faker;

class ForumCollectionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = app(ForumRepositoryInterface::class);
    }

    public function testCollectForumWithChildren()
    {
        $parent = factory(Forum::class)->create();
        $child1 = factory(Forum::class)->create(['parent_id' => $parent->id]);
        $child2 = factory(Forum::class)->create(['parent_id' => $parent->id]);

        $this->createTopic($child2->id);

        $faker = Faker\Factory::create();
        $guestId = $faker->uuid;
        $forums = $this->repository->categories($guestId)->mapCategory($guestId);

        $collection = new ForumCollection($forums);
        $result = $collection->toArray(request());

        $result = collect($result)->keyBy('id');

        $this->assertTrue(isset($result[$parent->id]));
        $this->assertTrue(isset($result[$parent->id]['children']));

        $children = $result[$parent->id]['children'];

        $this->assertEquals(2, count($children));

        $this->assertEquals($child1->name, $children[0]['name']);
        $this->assertEquals($child2->name, $children[1]['name']);

        $this->assertEquals(1, $result[$parent->id]['topics']);
        $this->assertEquals(1, $result[$parent->id]['posts']);

        $this->assertEquals(1, $child2['topics']);
        $this->assertEquals(1, $child2['posts']);
    }
}
