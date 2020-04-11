<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return array_merge($this->resource->only('name', 'photo'), ['visited_at' => $this->resource->created_at->toIso8601String()]);
    }
}
