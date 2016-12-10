<?php

use Coyote\Forum;
use Coyote\User;

class ForumTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    private function create()
    {
        Forum::create(['name' => 'A', 'slug' => 'A', 'description' => 'Lorem ipsum']);
        Forum::create(['name' => 'B', 'slug' => 'B', 'description' => 'Lorem ipsum']);
    }

    // tests
    public function testForumCreationOrder()
    {
        $this->create();

        $this->tester->seeRecord('forums', ['name' => 'A']);
        $forumA = $this->tester->grabRecord('Coyote\Forum', ['name' => 'A']);
        $forumB = $this->tester->grabRecord('Coyote\Forum', ['name' => 'B']);

        $this->assertEquals($forumA->order + 1, $forumB->order);

        $user = $this->tester->createUser();
        $this->tester->haveRecord('forum_orders', ['user_id' => $user->id, 'forum_id' => $forumA->id, 'order' => 1]);

        Forum::create(['name' => 'C', 'slug' => 'C', 'description' => 'Lorem ipsum']);
        $forumC = $this->tester->grabRecord('Coyote\Forum', ['name' => 'C']);

        $this->tester->seeRecord('forum_orders', ['forum_id' => $forumC->id, 'user_id' => $user->id]);
    }

    public function testForumDelete()
    {
        $this->create();

        $before = $this->tester->grabRecord('Coyote\Forum', ['name' => 'B']);
        Forum::where('name', 'A')->delete();
        $after = $this->tester->grabRecord('Coyote\Forum', ['name' => 'B']);

        $this->assertEquals($before->order - 1, $after->order);
    }

    public function testForumCreationWithSpecificOrder()
    {
        Forum::create(['name' => 'A', 'slug' => 'A', 'description' => 'Lorem ipsum', 'order' => 4]);
        Forum::create(['name' => 'B', 'slug' => 'B', 'description' => 'Lorem ipsum', 'order' => 5]);

        $this->tester->seeRecord('forums', ['name' => 'A', 'order' => 4]);
        $this->tester->seeRecord('forums', ['name' => 'B', 'order' => 5]);
    }
}
