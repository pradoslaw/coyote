<?php

namespace Coyote\Http\Resources\Elasticsearch;

/**
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $visited_at
 * @property \Carbon\Carbon $created_at
 * @property \Coyote\Services\Media\MediaInterface $photo
 * @property int $reputation
 */
class UserResource extends ElasticsearchResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $date = $this->visited_at ?: $this->created_at;

        return array_merge(
            $this->resource->only('id', 'name'),
            [
                'visited_at'    => $date->toIso8601String(),
                'decay_date'    => $date->toIso8601String(),
                'url'           => route('profile', [$this->id], false),
                'photo'         => ((string) $this->photo->url()) ?? null,
                'suggest'       => $this->getSuggest()
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
