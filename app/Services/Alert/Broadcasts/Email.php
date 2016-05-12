<?php

namespace Coyote\Services\Alert\Broadcasts;

use Coyote\Services\Alert\Providers\ProviderInterface;
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
     * @param ProviderInterface $alert
     * @return bool
     */
    public function send($user, ProviderInterface $alert)
    {
        if (!$user['email'] || !$alert->emailTemplate() || !$user['user_email'] || !$user['is_active']
        || !$user['is_confirm'] || $user['is_blocked']) {
            return false;
        }
        
        $data = $alert->toArray();
        $data['headline'] = $this->parse($data, $data['headline']);
        
        $email = $user['user_email'];

        $this->mailer->send($alert->emailTemplate(), $data, function ($message) use ($email, $data) {
            $message->subject($data['headline']);
            $message->to($email);
            $message->from('no-reply@4programmers.net', $data['sender_name']);
        });
        
        return true;
    }
}
