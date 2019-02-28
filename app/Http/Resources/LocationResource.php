<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $city
 */
class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'city'      => $this->city,
            'url'       => route('job.city', [$this->city])
        ];
    }
}
