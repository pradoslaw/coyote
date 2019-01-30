<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Coyote\Services\Declination\Declination;

class Notification extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $senders = $this->resource->senders->unique('name');

        $this->resource->user = $senders->first();
        $count = $senders->count();

        if ($count === 0) {
            return []; // no senders? return empty notification
        } elseif ($count === 2) {
            $sender = $this->resource->user->name . ' (oraz ' . $senders->last()->name . ')';
        } elseif ($count > 2) {
            $sender = $this->resource->user->name . ' (oraz ' . Declination::format($count, ['osoba', 'osoby', 'osób']) . ')';
        } else {
            $sender = $this->resource->user->name;
        }

        return array_merge(parent::toArray($request), ['headline' => str_replace('{sender}', $sender, $this->resource->headline)]);
    }
}
