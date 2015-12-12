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

        $group = $this->tester->grabRecord('groups', ['name' => 'Grupa AA']);
        DB::table('group_users')->insert(['user_id' => $user->id, 'group_id' => $group->id]);

        $this->testCreationNewPermission();

        $permission = $this->tester->grabRecord('permissions', ['name' => 'do-smth']);
        $this->tester->seeRecord('group_permissions', ['group_id' => $group->id, 'permission_id' => $permission->id, 'value' => false]);
    }
}