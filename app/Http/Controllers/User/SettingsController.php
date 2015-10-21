<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class SettingsController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Ustawienia', route('user.settings'));

        return parent::view('user.settings');
    }

    public function save()
    {
    }
}
