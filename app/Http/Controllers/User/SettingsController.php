<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Moje konto', route('user.home'));
        $this->breadcrumb->push('Ustawienia', route('user.settings'));

        return parent::view('user.settings', ['formatList' => User::dateFormatList(), 'yearList' => User::birthYearList()]);
    }

    public function save(Request $request)
    {
        $this->validate($request, User::$rules);

        echo 'OK';
    }
}
