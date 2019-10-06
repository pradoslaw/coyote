<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Rules\PasswordRule;
use Illuminate\Http\Request;

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
            'password' => ['required', app(PasswordRule::class)]
        ]);

        $this->auth->delete();

        $request->session()->flash('success', 'Konto zostało prawidłowo usunięte.');

        return redirect()->to('/');
    }
}
