<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Coyote\Services\Media\MediaInterface $photo
 */
class UserResource extends JsonResource
{
    private const OPTIONALS = ['allow_sig', 'allow_count', 'allow_smilies', 'posts', 'sig', 'location', 'visited_at', 'created_at', 'group'];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = array_merge(
            $this->resource->only(['id', 'name', 'deleted_at', 'is_blocked']),
            ['photo' => (string) $this->photo->url() ?: null]
        );

        foreach (self::OPTIONALS as $value) {
            if (isset($this->resource->$value)) {
                $result[$value] = $this->resource->$value;
            }
        }

        return $result;
    }
}
