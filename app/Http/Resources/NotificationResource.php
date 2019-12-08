<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Coyote\Services\Declination\Declination;

/**
 * @property string $read_at
 * @property bool $is_clicked
 */
class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = array_only($this->resource->toArray(), ['subject', 'excerpt', 'id', 'url', 'guid']);

        return array_merge($only, [
            'is_unread'      => $this->read_at && $this->read_at > $request->session()->get('created_at') || ! $this->is_clicked,

            'headline'      => $this->getHeadline(),
            'created_at'    => format_date($this->resource->created_at)
        ]);
    }

    /**
     * @return string
     */
    private function getHeadline(): string
    {
        $senders = $this->resource->senders->unique('name');

        $this->resource->user = $senders->first();
        $count = $senders->count();

        if ($count === 0) {
            return ''; // no senders? return empty notification
        } elseif ($count === 2) {
            $sender = $this->resource->user->name . ' (oraz ' . $senders->last()->name . ')';
        } elseif ($count > 2) {
            $sender = $this->resource->user->name . ' (oraz ' . Declination::format($count, ['osoba', 'osoby', 'osób']) . ')';
        } else {
            $sender = $this->resource->user->name;
        }

        return str_replace('{sender}', $sender, $this->resource->headline);
    }
}
