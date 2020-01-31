<?php

namespace Tests\Feature;

use Coyote\Forum;
use Coyote\Http\Resources\ForumCollection;
use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Faker;

class ForumCollectionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var ForumRepositoryInterface
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

        $child1->refresh();
        $child2->refresh();

        $this->assertEquals(1, $child1->order);
        $this->assertEquals(2, $child2->order);

        $faker = Faker\Factory::create();

        $guestId = $faker->uuid;

        $this->repository->pushCriteria(new AccordingToUserOrder(null));
        $forums = $this->repository->categories($guestId)->mapCategory();

        $collection = new ForumCollection($forums);
        $result = $collection->toArray(request());

        $result = collect($result)->keyBy('id');

        $this->assertTrue(isset($result[$parent->id]));
        $this->assertTrue(isset($result[$parent->id]['children']));

        $children = $result[$parent->id]['children'];

        $this->assertEquals(2, count($children));
        $this->assertEquals(1, $children[0]['order']);
        $this->assertEquals(2, $children[1]['order']);

//        var_dump($children, $child1, $child2);

        $this->assertEquals($child1->name, $children[0]['name']);
        $this->assertEquals($child2->name, $children[1]['name']);

        $this->assertEquals(1, $result[$parent->id]['topics']);
        $this->assertEquals(1, $result[$parent->id]['posts']);

        $this->assertEquals(1, $children[1]['topics']);
        $this->assertEquals(1, $children[1]['posts']);
    }
}
