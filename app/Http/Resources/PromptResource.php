<?php

namespace Coyote\Http\Resources;

use Coyote\Services\Media\File;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $group
 * @property File $photo
 */
class PromptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'group' => $this->group,
            'photo' => (string)$this->photo->url(),
        ];
    }
}
