<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Mailing;
use Coyote\Repositories\Contracts\MailingRepositoryInterface;

class MailingRepository extends Repository implements MailingRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Mailing::class;
    }
}
