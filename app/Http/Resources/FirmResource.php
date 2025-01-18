<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Services\Media\File;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property File $logo
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class FirmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = array_except(
            $this->resource->toArray(),
            ['slug', 'user_id', 'headline', 'employees', 'founded', 'country_id', 'vat_id', 'deleted_at', 'is_private', 'benefits', 'gallery'],
        );

        return array_merge($data, [
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'logo'       => (string)$this->logo->url(),
            'hasLogo'    => $this->logo->getFilename() && $this->logo->size() > 0,
            'url'        => route('job.firm', $this->slug),
        ]);
    }
}
