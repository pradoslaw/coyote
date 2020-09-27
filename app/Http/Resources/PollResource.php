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
            array_except(parent::toArray($request), ['created_at', 'updated_at']),
            [
                'items'         => JsonResource::collection($this->items),
                'expired_at'    => $this->resource->expiredAt(),
                'expired'       => $this->resource->expired(),

                $this->mergeWhen($request->user(), function () use ($request) {
                    return ['votes' => $this->resource->userVoteIds($request->user()->id)];
                })
            ]
        );
    }
}
