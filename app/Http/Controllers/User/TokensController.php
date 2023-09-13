<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\User\Menu\SettingsMenu;
use Illuminate\View\View;

class TokensController extends BaseController
{
    use SettingsMenu;

    public function index(): View
    {
        return $this->view('user.tokens');
    }
}
