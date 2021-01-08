<?php

namespace Coyote\Http\Controllers\User;

class TokensController extends BaseController
{
    use SettingsTrait;

    public function index()
    {
        return $this->view('user.tokens');
    }
}
