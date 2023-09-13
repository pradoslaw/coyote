<?php

namespace Coyote\Http\Controllers\User;

use Illuminate\View\View;

class TokensController extends BaseController
{
    use SettingsTrait;

    public function index(): View
    {
        return $this->view('user.tokens');
    }
}
