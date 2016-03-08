<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Actkey;
use Coyote\User;
use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;

class ConfirmController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => 'getEmail']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getIndex(Request $request)
    {
        if ($request->user()->is_confirm) {
            return redirect()->route('user.home')->with('success', 'Adres e-mail jest już potwierdzony.');
        }
        $this->breadcrumb->push('Potwierdź adres e-mail', url('Confirm'));

        return $this->view('auth.confirm');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postIndex(Request $request)
    {
        if ($request->user()->is_confirm) {
            return redirect()->route('user.home')->with('success', 'Adres e-mail jest już potwierdzony.');
        }

        $this->validate($request, [
            'email' => 'required|email|max:255|unique:users,email,NULL,id,is_confirm,1',
        ]);

        // perhaps user decided to change his email, so we need to save new one in database
        if ($request->email !== $request->user()->email) {
            $request->user()->fill(['email' => $request->email])->save();
        }

        $url = Actkey::createLink($request->user()->id);
        $email = $request->email;

        Mail::queue('emails.email', ['url' => $url], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Prosimy o potwierdzenie nowego adresu e-mail');
        });

        return back()->with('success', 'Na podany adres e-mail został wysłany link aktywacyjny.');
    }

    /**
     * Potwierdzenie adresu e-mail poprzez link aktywacyjny znajdujacy sie w mailu
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getEmail(Request $request)
    {
        $actkey = Actkey::where('user_id', $request->id)->where('actkey', $request->actkey)->firstOrFail();

        $user = User::find(request('id'));
        $user->is_confirm = 1;

        if ($actkey->email) {
            $user->email = $actkey->email;
        }

        $user->save();
        $actkey->delete();

        return redirect()->route('home')->with('success', 'Adres e-mail został pozytywnie potwierdzony.');
    }
}
