<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Coyote\Services\Media\MediaInterface $photo
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
        $only = $this->resource->only(['id', 'name', 'deleted_at', 'is_blocked']);

        return array_merge($only, ['photo' => (string) $this->photo->url()]);
    }
}
