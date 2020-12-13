<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Carbon $created_at
 */
class FlagResource extends JsonResource
{
    public function toArray($request)
    {
        return array_merge(
            $this->resource->only(['id', 'name', 'text', 'user_id', 'user_name', 'url']),
            [
                'created_at' => $this->created_at->toIso8601String(),
                'resource_id' => $this->resource->pivot->resource_id
            ]
        );
    }
}
