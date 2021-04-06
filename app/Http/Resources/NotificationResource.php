<?php

namespace Coyote\Http\Resources;

use Coyote\Services\Media\Factory;
use Illuminate\Http\Resources\Json\JsonResource;
use Coyote\Services\Declination;

/**
 * @property \Carbon\Carbon $read_at
 * @property bool $is_clicked
 * @property \Coyote\User $user
 */
class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $senders = $this->resource->senders->unique('name');
        $user = $senders->first();

        $only = $this->resource->only(['subject', 'excerpt', 'id', 'url']);

        return array_merge($only, [
            'is_read'       => $this->is_clicked && $this->read_at,

            'headline'      => $this->getHeadline($user, $senders),
            'created_at'    => $this->resource->created_at->toIso8601String(),
            'photo'         => $this->getMediaUrl($user ? $user->photo : null),
            'user_id'       => $senders->first()->user_id
        ]);
    }

    /**
     * @param string|null $filename
     * @return string
     */
    private function getMediaUrl(?string $filename): string
    {
        return $filename ? app(Factory::class)->make('photo', ['file_name' => $filename])->url() : '';
    }

    /**
     * @param mixed $user
     * @param \Illuminate\Support\Collection $senders
     * @return string
     */
    private function getHeadline($user, $senders): string
    {
        $count = $senders->count();

        if ($count === 0) {
            return ''; // no senders? return empty notification
        } elseif ($count === 2) {
            $sender = $user->name . ' (oraz ' . $senders->last()->name . ')';
        } elseif ($count > 2) {
            $sender = $user->name . ' (oraz ' . Declination::format($count - 1, ['osoba', 'osoby', 'osób']) . ')';
        } else {
            $sender = $user->name;
        }

        return str_replace('{sender}', $sender, $this->resource->headline);
    }
}
