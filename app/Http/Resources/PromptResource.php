<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $name
 * @property string $group
 * @property \Coyote\Services\Media\MediaInterface $photo
 */
class PromptResource extends JsonResource
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
            'name' => $this->name,
            'group' => $this->group,
            'photo' => (string) $this->photo->url()
        ];
    }
}
