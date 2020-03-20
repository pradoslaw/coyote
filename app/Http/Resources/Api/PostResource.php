<?php

namespace Coyote\Http\Resources\Api;

use Carbon\Carbon;
use Coyote\Http\Resources\UserResource;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property string $user_name
 * @property string $text
 * @property string $html
 * @property User $user
 * @property int $score
 * @property int $edit_count
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'user_name', 'score', 'edit_count', 'forum_id', 'topic_id']);

        return array_merge($only, [
            'created_at'    => $this->created_at->toIso8601String(),
            'user'          => UserResource::make($this->user),
            'html'          => $this->text !== null ? $this->html : null,
            'excerpt'       => $this->text !== null ? excerpt($this->html) : null,
            'url'           => url(UrlBuilder::post($this->resource)),

            'comments'      => PostCommentResource::collection($this->whenLoaded('comments'))
        ]);
    }
}
