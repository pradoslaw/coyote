<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MailingRepositoryInterface as MailingRepository;
use Ramsey\Uuid\Uuid;

class MailingController extends Controller
{
    /**
     * @param string $uuid
     * @param MailingRepository $mailing
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unsubscribe($uuid, MailingRepository $mailing)
    {
        if (!Uuid::isValid($uuid)) {
            return redirect()
                ->to('/')
                ->with(
                    'error',
                    sprintf('Ups. Link wygląda na nieprawidłowy. Napisz do nas na %s, a usuniemy Twój adres e-mail z listy mailingowej.', config('mail.from.address'))
                );
        }

        /** @var \Coyote\Mailing $result */
        $result = $mailing->findOrFail($uuid);
        $result->delete();

        return redirect()
            ->to('/')
            ->with('success', sprintf('Adres %s został usunięty z listy mailingowej.', $result->email));
    }
}
