<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Coyote\Job\Location[] $locations
 */
class JobFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge($this->resource->toArray(), [
            'locations' => LocationResource::collection($this->locations),
            'is_gross' => (int) $this->resource->is_gross // is_gross is dropdown list, that's why we cast to int
        ]);
    }
}
