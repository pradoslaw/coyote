<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property \Carbon\Carbon $visited_at
 * @property \Carbon\Carbon $created_at
 */
class UserResource extends JsonResource
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
            $this->resource->only('id', 'name', 'photo'),
            ['visited_at' => $date->toIso8601String(), 'decay_date'  => $date->toIso8601String(), 'url' => route('profile', [$this->id])]
        );
    }
}
