<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Carbon $created_at
 */
class PostAttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            $this->resource->toArray(['id', 'name', 'file', 'size', 'mime']),
            ['created_at' => $this->created_at->toIso8601String()]
        );
    }
}
