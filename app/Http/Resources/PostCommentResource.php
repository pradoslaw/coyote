<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property \Coyote\Forum $forum
 * @property \Coyote\Topic $topic
 * @property int $user_id
 * @property int $post_id
 * @property string $text
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
            $this->resource->only(['id', 'text', 'html', 'post_id']),
            [
                'created_at'    => $this->created_at->toIso8601String(),
                'updated_at'    => $this->updated_at->toIso8601String(),
                'user'          => new UserResource($this->user),
                'url'           => UrlBuilder::topic($this->topic) . '?p=' . $this->post_id . '#comment-' . $this->id,

                $this->mergeWhen($request->user(), function () use ($request) {
                    return ['editable' => $request->user()->can('update', [$this->resource, $this->forum])];
                })
            ]
        );
    }
}
