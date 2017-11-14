<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\MailingRepositoryInterface as MailingRepository;

class MailingController extends Controller
{
    /**
     * @param string $uuid
     * @param MailingRepository $mailing
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unsubscribe($uuid, MailingRepository $mailing)
    {
        /** @var \Coyote\Mailing $result */
        $result = $mailing->findOrFail($uuid);
        $result->delete();

        return redirect()
            ->to('/')
            ->with('success', sprintf('Adres %s został usunięty z listy mailingowej.', $result->email));
    }
}
