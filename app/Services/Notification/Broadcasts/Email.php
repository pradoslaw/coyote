<?php

namespace Coyote\Services\Notification\Broadcasts;

use Coyote\Services\Notification\Providers\ProviderInterface;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;

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

        $email = $user['user_email'];

        $this->mailer->queue($notification->emailTemplate(), $data, function (Message $message) use ($email, $data) {
            $message->subject($data['headline']);
            $message->to($email);
            $message->from('no-reply@4programmers.net', config('app.name'));
        });

        return true;
    }
}
