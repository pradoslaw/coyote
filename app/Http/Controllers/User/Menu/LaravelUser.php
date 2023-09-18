<?php
namespace Coyote\Http\Controllers\User\Menu;

use Coyote\Domain\User\User;

class LaravelUser extends User
{
    public function __construct(\Coyote\User $user)
    {
        parent::__construct(true,
          $user->id,
          $user->pm,
          $user->pm_unread);
    }
}
