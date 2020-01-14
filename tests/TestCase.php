<?php

namespace Tests;

use Coyote\Group;
use Coyote\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function createUserWithGroup(): User
    {
        $user = factory(User::class)->create();

        /** @var Group $group */
        $group = factory(Group::class)->create();
        $group->users()->attach($user->id);

        return $user;
    }
}
