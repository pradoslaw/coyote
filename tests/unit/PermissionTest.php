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

    public function testCanUserDeletePostInLockedForumOrTopic()
    {
        $user = $this->tester->createUser();
        $forum = $this->tester->createForum(['is_locked' => true]);

        $topic = $this->tester->createTopic(['forum_id' => $forum->id]);
        $post = $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user->id]);

        $this->assertFalse($user->can('delete', $post));

        $post->forum->is_locked = false;
        $post->forum->save();

        $this->assertTrue($user->can('delete', $post));

        /////////////////////////////////

        $post->topic->is_locked = true;
        $post->topic->save();

        $this->assertFalse($user->can('delete', $post));
    }

    public function testCanAdminDeletePostInLockedForum()
    {
        $user = $this->tester->createUser();
        $forum = $this->tester->createForum(['is_locked' => true]);

        $topic = $this->tester->createTopic(['forum_id' => $forum->id]);
        $post = $this->tester->createPost(['topic_id' => $topic->id, 'forum_id' => $forum->id, 'user_id' => $user->id]);

        $admin = $this->tester->createUser();
        $this->tester->grantAdminAccess($admin);
        $this->assertTrue($admin->can('delete', $post));
    }

    public function testCanUserAccessForum()
    {
        $user = $this->tester->createUser();
        $forum = $this->tester->createForum();

        $this->assertTrue($user->can('access', $forum));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::allows('access', $forum));

        $admins = $this->tester->haveRecord(\Coyote\Group::class, ['name' => 'Admins']);
        $this->tester->haveRecord(\Coyote\Forum\Access::class, ['forum_id' => $forum->id, 'group_id' => $admins->id]);

        $forum->is_prohibited = true;
        $forum->save();

        $this->assertFalse($user->can('access', $forum));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::allows('access', $forum));

        $this->tester->haveRecord(\Coyote\Group\User::class, ['user_id' => $user->id, 'group_id' => $admins->id]);

        $this->assertTrue($user->can('access', $forum));
    }

    public function testCanUserWriteInTopic()
    {
        $user = $this->tester->createUser();
        $forum = $this->tester->createForum();
        $topic = $this->tester->createTopic(['forum_id' => $forum->id]);

        $this->assertTrue(\Illuminate\Support\Facades\Gate::allows('write', $topic));
        $this->assertTrue($user->can('write', $topic));

        $topic->is_locked = true;
        $topic->save();

        $this->assertFalse(\Illuminate\Support\Facades\Gate::allows('write', $topic));
        $this->assertFalse($user->can('write', $topic));

        //////////////////////////////////////////////////////////////

        $user = $this->tester->createUser();
        $topic = $this->tester->createTopic(['forum_id' => $forum->id, 'is_locked' => true]);

        $admins = $this->tester->haveRecord(\Coyote\Group::class, ['name' => 'Admins']);
        $this->tester->haveRecord(\Coyote\Group\User::class, ['user_id' => $user->id, 'group_id' => $admins->id]);

        $permission = $this->tester->grabRecord(\Coyote\Permission::class, ['name' => 'forum-update']);
        $this->assertIsNumeric($permission->id);
        $this->tester->haveRecord('group_permissions', ['group_id' => $admins->id, 'permission_id' => $permission->id, 'value' => 1]);

        $this->assertTrue($user->can('forum-update'));
        $this->assertTrue($user->can('write', $topic));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::allows('write', $topic));
    }

    public function testCanUserWriteInLockedForum()
    {
        $user = $this->tester->createUser();
        $forum = $this->tester->createForum(['enable_anonymous' => true]);

        // anonymous user can write in category unless it's locked or has restricted guest access
        $this->assertTrue(\Illuminate\Support\Facades\Gate::allows('write', $forum));

        $forum->enable_anonymous = false;
        $forum->save();

        $this->assertFalse(\Illuminate\Support\Facades\Gate::allows('write', $forum));
        $this->assertTrue($user->can('write', $forum));

        $forum->is_locked = true;
        $forum->save();

        // only moderators can update posts in category
        $this->assertFalse($user->can('update', $forum));
        $this->assertFalse($user->can('write', $forum));
    }

    public function testCanModeratorWriteInLockedForum()
    {
        $user = $this->tester->createUser();
        $forum = $this->tester->createForum(['is_locked' => true]);

        $this->tester->grantAdminAccess($user);

        // only moderators can update posts in category
        $this->assertTrue($user->can('update', $forum));
        $this->assertTrue($user->can('write', $forum));
    }
}
