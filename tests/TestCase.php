<?php

namespace Tests;

use Coyote\Group;
use Coyote\Topic;
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

    public function createTopic(int $forumId, array $data = []): Topic
    {
        return factory(Topic::class)->create(array_merge($data, ['forum_id' => $forumId]));
    }
}
