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
            $this->resource->only(['id', 'text', 'url', 'resources']),
            [
                'created_at' => $this->created_at->toIso8601String(),
                'user' => new UserResource($this->resource->user),
                'name' => $this->resource->type->name
            ]
        );
    }
}
