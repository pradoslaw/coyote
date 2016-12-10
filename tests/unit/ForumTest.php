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
