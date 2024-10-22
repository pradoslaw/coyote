<?php
namespace Coyote\Http\Controllers\User;

use Coyote\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordController extends BaseController
{
    public function index(): View
    {
        $this->breadcrumb->push('Zmiana hasła', route('user.password'));

        return $this->view('user.password');
    }

    public function save(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'password'     => 'required|confirmed|min:3',
            'password_old' => 'required|password',
        ]);
        $user = User::find(auth()->user()->id);
        $user->password = bcrypt($request->get('password'));
        $user->save();
        auth()->login($user);
        return back()->with('success', 'Zmiany zostały zapisane.');
    }
}
