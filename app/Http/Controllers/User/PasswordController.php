<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasswordController extends BaseController
{
    use SettingsTrait;
    
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->breadcrumb->push('Zmiana hasła', route('user.password'));

        return parent::view('user.password');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'password'                  => 'required|confirmed|min:3',
            'password_old'              => 'required|password'
        ]);

        $user = User::find(auth()->user()->id);
        $user->password = bcrypt($request->get('password'));
        $user->save();

        Auth::login($user);

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }
}
