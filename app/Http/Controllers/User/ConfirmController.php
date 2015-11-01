<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Actkey;
use Coyote\User;
use Coyote\Http\Controllers\Controller;

class ConfirmController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->breadcrumb->push('Potwierdź adres e-mail', route('user.confirm'));

        return parent::view('user.confirm');
    }

    /**
     * Potwierdzenie adresu e-mail poprzez link aktywacyjny znajdujacy sie w mailu
     *
     * @return \Illuminate\Http\Response
     */
    public function email()
    {
        $actkey = Actkey::where('user_id', request('id'))->where('actkey', request('actkey'))->firstOrFail();

        $user = User::find(request('id'));
        $user->is_confirm = 1;
        $user->save();

        $actkey->delete();

        return redirect(route('home'))->with('success', 'Adres e-mail został pozytywnie potwierdzony');
    }
}
