<?php
namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Services\Declination;
use Coyote\Services\Media\Factory;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property Carbon $read_at
 * @property bool $is_clicked
 * @property User $user
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $senders = $this->resource->senders->unique('name');
        $user = $senders->first();

        $only = $this->resource->only(['subject', 'excerpt', 'id', 'url']);

        return \array_merge($only, [
            'is_read'    => $this->is_clicked && $this->read_at,
            'headline'   => $this->getHeadline($user, $senders),
            'created_at' => $this->resource->created_at->toIso8601String(),
            'photo'      => $this->getMediaUrl($user ? $user->photo : null),
            'user_id'    => $senders->first()->user_id,
        ]);
    }

    private function getMediaUrl(?string $filename): string
    {
        if ($filename) {
            return app(Factory::class)->make('photo', ['file_name' => $filename])->url();
        }
        return '';
    }

    /**
     * @param mixed $user
     * @param Collection $senders
     * @return string
     */
    private function getHeadline($user, $senders): string
    {
        $count = $senders->count();
        if ($count === 0) {
            return ''; // no senders? return empty notification
        }
        return str_replace(
            '{sender}',
            $this->sender($count, $user, $senders),
            $this->resource->headline);
    }

    private function sender(int $count, mixed $user, Collection $senders): mixed
    {
        if ($count === 2) {
            return $user->name . ' (oraz ' . $senders->last()->name . ')';
        }
        if ($count > 2) {
            return $user->name . ' (oraz ' . Declination::format($count - 1, ['osoba', 'osoby', 'osób']) . ')';
        }
        return $user->name;
    }
}
