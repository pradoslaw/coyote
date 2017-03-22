<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Mail;
use Coyote\Repositories\Contracts\MailRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailable;

class MailRepository extends Repository implements MailRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Mail::class;
    }

    /**
     * @inheritdoc
     */
    public function isDuplicated(Mailable $mail, $withinDays = 2)
    {
        $subject = $mail->subject;
        $email = array_first($mail->to)['address'];

        if (empty($email)) {
            throw new \InvalidArgumentException('E-mail address can not be empty.');
        }

        return $this
            ->model
            ->where('email', $email)
            ->when($subject, function (Builder $builder) use ($subject) {
                return $builder->where('subject', $subject);
            })
            ->where('created_at', '>=', Carbon::parse("-$withinDays days"))
            ->exists();
    }
}
