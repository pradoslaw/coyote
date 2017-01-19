<?php

namespace Coyote\Transformers;

use Coyote\Alert;
use Coyote\Services\Declination\Declination;
use League\Fractal\TransformerAbstract;

class AlertTransformer extends TransformerAbstract
{
    /**
     * @param Alert $alert
     * @return array
     */
    public function transform(Alert $alert)
    {
        $alert->senders = $alert->senders->unique('name');

        $alert->user = $alert->senders->first();
        $count = $alert->senders->count();

        if ($count === 2 && $alert->user->name !== $alert->senders[1]->name) {
            $sender = $alert->user->name . ' (oraz ' . $alert->senders[1]->name . ')';
        } elseif ($count > 2) {
            $sender = $alert->user->name . ' (oraz ' . Declination::format($count, ['osoba', 'osoby', 'osób']) . ')';
        } else {
            $sender = $alert->user->name;
        }

        return array_merge($alert->toArray(), ['headline' => str_replace('{sender}', $sender, $alert->headline)]);
    }
}
