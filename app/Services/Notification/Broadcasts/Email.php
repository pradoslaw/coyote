<?php

namespace Coyote\Services\Notification\Broadcasts;

use Coyote\Mail\NotificationSent;
use Coyote\Services\Notification\Providers\ProviderInterface;
use Illuminate\Contracts\Mail\Mailer;

/**
 * Class Email
 */
class Email extends Broadcast
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param mixed $user
     * @param ProviderInterface $notification
     * @return bool
     */
    public function send($user, ProviderInterface $notification)
    {
        if (!$user['email'] || !$notification->emailTemplate() || !$user['user_email'] || !$user['is_active']
        || !$user['is_confirm'] || $user['is_blocked']) {
            return false;
        }

        $data = $notification->toArray();
        $data['headline'] = $this->parse($data, $data['headline']);

        $mailable = (new NotificationSent($notification->emailTemplate(), $data))->subject($data['headline']);
        $this->mailer->to($user['user_email'])->send($mailable);

        return true;
    }
}
