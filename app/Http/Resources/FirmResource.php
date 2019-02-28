<?php

namespace Coyote\Http\Resources;

use Coyote\Services\Media\MediaInterface;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property MediaInterface $logo
 */
class FirmResource extends JsonResource
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
            'name'          => $this->name,
            'logo'          => (string) $this->logo->url(),
            'url'           => route('job.firm', $this->slug)
        ];
    }
}
