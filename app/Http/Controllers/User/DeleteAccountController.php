<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\UserDeleted;
use Coyote\Rules\PasswordRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeleteAccountController extends BaseController
{
    use HomeTrait;

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return $this->view('user.delete');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'password' => [
                'bail',
                Rule::requiredIf(function () {
                    return $this->auth->password !== null;
                }),
                app(PasswordRule::class)
            ]
        ]);

        $this->transaction(function () {
            $this->auth->delete();

            event(new UserDeleted($this->auth));
        });

        $request->session()->flash('success', 'Konto zostało prawidłowo usunięte.');

        return redirect()->to('/');
    }
}
