<?php

namespace Coyote\Http\Resources\Elasticsearch;

/**
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $visited_at
 * @property \Carbon\Carbon $created_at
 * @property \Coyote\Services\Media\MediaInterface $photo
 * @property int $reputation
 * @property int $group_id
 * @property string $group_name
 */
class UserResource extends ElasticsearchResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $date = $this->visited_at ?: $this->created_at;

        return array_merge(
            $this->resource->only('id', 'name'),
            [
                'created_at'    => $this->created_at->toIso8601String(),
                'visited_at'    => $date->toIso8601String(),
                'deleted_at'    => $date->toIso8601String(),
                'decay_date'    => $date->toIso8601String(),
                'url'           => route('profile', [$this->id], false),
                'photo'         => ((string) $this->photo->url()) ?? null,
                'suggest'       => $this->getSuggest(),

                $this->mergeWhen($this->group_id, function () {
                    return ['group' => $this->group_name];
                })
            ]
        );
    }

    /**
     * @return int
     */
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
