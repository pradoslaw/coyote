<?php
namespace Tests\Unit\OAuth\Fixture;

use Coyote\User;
use Tests\Unit\BaseFixture;

trait Models
{
    use BaseFixture\Server\Laravel\Transactional;

    function newUser(string $username): void
    {
        $user = new User;
        $user->name = $username;
        $user->email = 'irrelevant';
        $user->save();
    }

    function newUserConfirmedEmail(string $email): int
    {
        $user = new User;
        $user->name = 'irrelevant';
        $user->email = $email;
        $user->is_confirm = true;
        $user->save();
        return $user->id;
    }

    function newUserDeleted(string $username): void
    {
        $user = new User;
        $user->name = $username;
        $user->email = 'irrelevant';
        $user->deleted_at = 1;
        $user->save();
    }
}
