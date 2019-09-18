<?php

namespace Coyote\Http\Resources;

use Coyote\Country;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $city
 * @property string $street
 * @property string $street_number
 * @property Country $country
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
            'url'           => route('job.city', [$this->city]),
            'city'          => $this->city,
            'street'        => $this->street,
            'street_number' => $this->street_number,

            $this->mergeWhen($this->resource->relationLoaded('country'), [
                'country'   => $this->country->name
            ])
        ];
    }
}
