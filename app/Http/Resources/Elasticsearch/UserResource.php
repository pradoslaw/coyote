<?php
namespace Coyote\Http\Resources\Elasticsearch;

use Carbon\Carbon;
use Coyote\Services\Media\File;
use Illuminate\Http\Request;

/**
 * @property int $id
 * @property string $name
 * @property Carbon $visited_at
 * @property Carbon $created_at
 * @property File $photo
 * @property int $reputation
 */
class UserResource extends ElasticsearchResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $date = $this->visited_at ?: $this->created_at;

        return array_merge(
            $this->resource->only('id', 'name'),
            [
                'created_at' => $this->created_at->toIso8601String(),
                'visited_at' => $date->toIso8601String(),
                'deleted_at' => $date->toIso8601String(),
                'decay_date' => $date->toIso8601String(),
                'url'        => route('profile', [$this->id], false),
                'photo'      => ((string)$this->photo->url()) ?? null,
                'suggest'    => $this->getSuggest(),
                'group'      => $this->resource->group_name,
            ],
        );
    }

    protected function weight(): int
    {
        $date = $this->visited_at ?: $this->created_at;
        return round($this->reputation + (($date->timestamp - self::BASE_TIMESTAMP) / 600000));
    }

    /**
     * @return string[]
     */
    protected function input(): array
    {
        return [$this->name];
    }
}
