<?php

namespace Coyote\Repositories\Contracts;

use Illuminate\Mail\Mailable;

interface MailRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Mailable $mail
     * @param int $withinDays
     * @return bool
     */
    public function isDuplicated(Mailable $mail, $withinDays = 2);
}
