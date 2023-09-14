<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\UserDeleted;
use Coyote\Http\Controllers\User\Menu\AccountMenu;
use Coyote\Rules\PasswordCheck;
use Coyote\Services\Stream\Activities\Delete;
use Coyote\Services\Stream\Objects\Person;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeleteAccountController extends BaseController
{
    use AuthenticatesUsers {
        logout as traitLogout;
    }

    use AccountMenu;

    public function index(): View
    {
        return $this->view('user.delete');
    }

    public function delete(Request $request, Guard $guard): RedirectResponse
    {
        $this->validate($request, [
          'password' => [
            'bail',
            Rule::requiredIf(fn () => $this->auth->password !== null),
            app(PasswordCheck::class)
          ]
        ]);

        $this->auth->timestamps = false;
        $this->auth->forceFill([
          'ip'         => $request->ip(),
          'browser'    => $request->browser(),
          'visits'     => $this->auth->visits + 1,
          'visited_at' => now(),
          'is_online'  => false
        ]);

        $this->transaction(function () use ($request, $guard) {
            $this->auth->save();

            // save information before logging out
            stream(Delete::class, new Person($this->auth));

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $guard->logout();
            $this->auth->delete();

            event(new UserDeleted($this->auth));
        });

        $request->session()->flash('success', 'Konto zostało prawidłowo usunięte.');

        return redirect()->to('/');
    }
}
