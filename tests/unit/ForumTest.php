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
        Forum::create(['name' => 'A', 'path' => 'A', 'description' => 'Lorem ipsum']);
        Forum::create(['name' => 'B', 'path' => 'B', 'description' => 'Lorem ipsum']);
    }

    // tests
    public function testForumCreationOrder()
    {
        $this->create();

        $this->tester->seeRecord('forums', ['name' => 'A']);
        $forumA = $this->tester->grabRecord('forums', ['name' => 'A']);
        $forumB = $this->tester->grabRecord('forums', ['name' => 'B']);

        $this->assertEquals($forumA->order + 1, $forumB->order);
    }

    public function testForumDelete()
    {
        $this->create();

        $before = $this->tester->grabRecord('forums', ['name' => 'B']);
        Forum::where('name', 'A')->delete();
        $after = $this->tester->grabRecord('forums', ['name' => 'B']);

        $this->assertEquals($before->order - 1, $after->order);
    }
}