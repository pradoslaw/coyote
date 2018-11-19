<?php

use Coyote\User;
use Illuminate\Support\Facades\DB;

class PermissionTest extends \Codeception\TestCase\Test
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

    // tests
    public function testIfAdminGroupExists()
    {
        $this->tester->canSeeRecord('groups', ['name' => 'Administrator']);
    }

    public function testCreationNewGroup()
    {
        \Coyote\Group::create([
            'name'           => 'Grupa AA'
        ]);

        $this->tester->seeRecord('groups', ['name' => 'Grupa AA']);
    }

    public function testCreationNewPermission()
    {
        $this->tester->haveRecord('permissions', ['name' => 'do-smth', 'default' => false]);
    }

    public function testAssignUserToNewGroup()
    {
        $this->testCreationNewGroup();
        $user = User::first();

        $group = $this->tester->grabRecord('Coyote\Group', ['name' => 'Grupa AA']);
        DB::table('group_users')->insert(['user_id' => $user->id, 'group_id' => $group->id]);

        $this->testCreationNewPermission();

        $permission = $this->tester->grabRecord('Coyote\Permission', ['name' => 'do-smth']);
        $this->tester->seeRecord('group_permissions', ['group_id' => $group->id, 'permission_id' => $permission->id, 'value' => false]);
    }

    public function testCanUserDeletePostAsAnAuthor()
    {
        $user = $this->tester->createUser();

        $forum = $this->tester->createForum();

        $topic = $this->tester->createTopic(['forum_id' => $forum->id]);
        $post = $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user->id]);

        $this->assertTrue($user->can('delete', $post));
    }

    public function testCanUserDeletePostDespiteAnswers()
    {
        $user = $this->tester->createUser();

        $forum = $this->tester->createForum();

        $topic = $this->tester->createTopic(['forum_id' => $forum->id]);
        $post = $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user->id]);

        $user2 = $this->tester->createUser();
        // new post by another author
        $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user2->id]);

        $this->assertTrue($user->can('delete', $post));
    }

    public function testCanUserDeletePostDespiteItsOld()
    {
        $user = $this->tester->createUser();

        $forum = $this->tester->createForum();

        $topic = $this->tester->createTopic(['forum_id' => $forum->id]);
        $post = $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user->id, 'created_at' => \Carbon\Carbon::yesterday()]);

        $this->assertTrue($user->can('delete', $post));
    }

    public function testCanUserDeletePostDespiteItsOldAndHasAnswers()
    {
        $user = $this->tester->createUser();

        $forum = $this->tester->createForum();

        $topic = $this->tester->createTopic(['forum_id' => $forum->id]);
        $post = $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user->id, 'created_at' => \Carbon\Carbon::yesterday()]);

        $user2 = $this->tester->createUser();
        // new post by another author
        $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user2->id, 'created_at' => \Carbon\Carbon::yesterday()]);

        $this->assertFalse($user->can('delete', $post));
    }

    public function testCanUserDeletePostHavingReputation()
    {
        $user = $this->tester->createUser(['reputation' => 99]);

        $forum = $this->tester->createForum();

        $topic = $this->tester->createTopic(['forum_id' => $forum->id]);
        $post = $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user->id, 'created_at' => \Carbon\Carbon::yesterday()]);

        $user2 = $this->tester->createUser();
        // new post by another author
        $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user2->id, 'created_at' => \Carbon\Carbon::yesterday()]);

        $this->assertFalse($user->can('delete', $post));

        $user->reputation = 100;
        $this->assertTrue($user->can('delete', $post));
    }

    public function testCanUserAccessForum()
    {
        $user = $this->tester->createUser();
        $forum = $this->tester->createForum();

        $this->assertTrue($user->can('access', $forum));

        $admins = $this->tester->haveRecord(\Coyote\Group::class, ['name' => 'Admins']);
        $this->tester->haveRecord(\Coyote\Forum\Access::class, ['forum_id' => $forum->id, 'group_id' => $admins->id]);

        $this->assertFalse($user->can('access', $forum));

        $this->tester->haveRecord(\Coyote\Group\User::class, ['user_id' => $user->id, 'group_id' => $admins->id]);

        $this->assertTrue($user->can('access', $forum));

    }
}
