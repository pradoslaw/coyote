<?php

namespace Coyote\Http\Resources\Api;

use Carbon\Carbon;
use Coyote\Http\Resources\UserResource;
use Coyote\Microblog;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $html
 * @property User $user
 * @property Microblog[] $comments
 */
class MicroblogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'votes']);

        return array_merge(
            $only,
            [
                'created_at'    => $this->created_at->toIso8601String(),
                'updated_at'    => $this->created_at->toIso8601String(),
                'html'          => $this->html,
                'comments'      => $this->comments ? MicroblogResource::collection($this->comments) : [],
                'user'          => UserResource::make($this->user)
            ]
        );
    }
}
