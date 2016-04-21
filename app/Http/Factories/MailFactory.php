<?php

namespace Coyote\Http\Factories;

use Illuminate\Contracts\Mail\MailQueue;

trait MailFactory
{
    /**
     * @return MailQueue
     */
    protected function getMailFactory()
    {
        return app(MailQueue::class);
    }
}
