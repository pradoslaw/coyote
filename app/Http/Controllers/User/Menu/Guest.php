<?php
namespace Coyote\Http\Controllers\User\Menu;

use Coyote\Domain\User\User;

class Guest extends User
{
    public function __construct()
    {
        parent::__construct(false, null, null, null);
    }
}
