<?php

namespace Coyote\Transformers;

use Coyote\Notification;
use Coyote\Services\Declination\Declination;
use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
{
    /**
     * @param Notification $notification
     * @return array
     */
    public function transform(Notification $notification)
    {
        $senders = $notification->senders->unique('name');

        $notification->user = $senders->first();
        $count = $senders->count();

        if ($count === 2) {
            $sender = $notification->user->name . ' (oraz ' . $senders->last()->name . ')';
        } elseif ($count > 2) {
            $sender = $notification->user->name . ' (oraz ' . Declination::format($count, ['osoba', 'osoby', 'osób']) . ')';
        } else {
            $sender = $notification->user->name;
        }

        return array_merge($notification->toArray(), ['headline' => str_replace('{sender}', $sender, $notification->headline)]);
    }
}
