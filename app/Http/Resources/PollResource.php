<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Coyote\Poll\Item[] $items
 * @property int $length
 * @property \Carbon\Carbon $created_at
 */
class PollResource extends JsonResource
{
    public function toArray($request)
    {
        return array_merge(
            $this->resource->only(['id', 'title', 'max_items', 'length']),
            [
                'items'         => JsonResource::collection($this->items),
                'expired_at'    => $this->resource->expiredAt(),
                'expired'       => $this->resource->expired(),

                'votes'         => $this->when(
                    $request->user(),
                    function () use ($request) {
                        return (array) $this->resource->userVoteIds($request->user()->id);
                    },
                    []
                )
            ]
        );
    }
}
