<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Coyote\Actkey;
use Coyote\Group;
use Coyote\Http\Requests\UserSettingsRequest;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->breadcrumb->push('Moje konto', route('user.home'));
        $this->breadcrumb->push('Ustawienia', route('user.settings'));

        $groupList = [null => '-- wybierz --'] + Group\User::groupList(auth()->user()->id)->toArray();

        $actEmail = Actkey::where('user_id', auth()->user()->id)->pluck('email');

        return parent::view('user.settings', [
            'formatList'        => User::dateFormatList(),
            'yearList'          => User::birthYearList(),
            'groupList'         => $groupList,
            'actEmail'          => $actEmail
        ]);
    }

    /**
     * @param UserSettingsRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(UserSettingsRequest $request)
    {
        $user = auth()->user();

        if ($user->email !== $request->get('email')) {
            $email = $request->get('email');

            // kasujemy poprzednie rekordu zwiazane z tym userem
            Actkey::where('user_id', $user->id)->delete();
            // przed zmiana e-maila trzeba wyslac link potwierdzajacy
            $actkey = Actkey::create([
                'actkey'   => str_random(),
                'user_id'  => $user->id,
                'email'    => $email
            ]);

            // taki format linku zachowany jest ze wzgledu na wsteczna kompatybilnosc.
            // z czasem mozemy zmienic ten format aby wskazywal na /User/Confirm/Email/<id>/<actkey>
            $url = route('user.email') . '?id=' . $user->id . '&actkey=' . $actkey->actkey;

            Mail::queue('emails.email', ['url' => $url], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Prosimy o potwierdzenie nowego adresu e-mail');
            });

            if ($user->is_confirm) {
                $request['email'] = $user->email;
            }
        }
        User::find($user->id)->fill($request->all())->save();

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }
}
