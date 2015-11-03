<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Coyote\Http\Requests\UserSettingsRequest;

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

    /**
     * @param UserSettingsRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(UserSettingsRequest $request)
    {
        User::find(auth()->user()->id)->fill($request->all())->save();

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }
}
