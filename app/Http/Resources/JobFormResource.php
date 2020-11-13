<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Coyote\Job\Location[] $locations
 * @property \Coyote\Job\Tag[] $tags
 * @property \Coyote\Job\Feature[] $features
 * @property \Coyote\Firm $firm
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
            'tags'      => TagResource::collection($this->tags),
            'features'  => FeatureResource::collection($this->features),

            'firm'      => new FirmFormResource($this->firm)
        ]);
    }
}
