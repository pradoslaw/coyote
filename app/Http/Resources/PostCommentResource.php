<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Job\Comment;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $job_id
 * @property Carbon $created_at
 * @property User $user
 * @property int $user_id
 * @property string $text
 * @property Comment[] $children
 * @property string $email
 */
class PostCommentResource extends JsonResource
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
            $this->resource->toArray(['id', 'text']),
            [
                'created_at'    => $this->created_at->toIso8601String(),
                'updated_at'    => $this->created_at->toIso8601String(),
                'user'          => new UserResource($this->whenLoaded('user'))
            ]
        );
    }
}
