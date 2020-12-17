<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Coyote\Services\Media\MediaInterface;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property MediaInterface $logo
 */
class TagResource extends JsonResource
{
    public function toArray($request)
    {
        return array_merge(
            array_except(parent::toArray($request), ['created_at', 'category_id', 'last_used_at']),
            [
                'logo'      => (string) $this->logo->url(),
            ]
        );
    }
}
