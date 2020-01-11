<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Tag;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Carbon $locked_at
 * @property Carbon $created_at
 * @property Carbon $last_post_created_at
 * @property string $html
 * @property User $user
 * @property Forum $forum
 * @property Post $firstPost
 * @property Post $lastPost
 * @property Microblog[] $comments
 * @property Tag[] $tags
 * @property \Coyote\Topic\Track[] $tracks
 */
class TopicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'subject', 'score', 'views', 'replies', 'is_sticky', 'is_locked', 'first_post_id', 'last_post_id']);

        return array_merge(
            $only,
            [
                'locked_at'             => $this->locked_at ? $this->locked_at->toIso8601String() : null,
                'created_at'            => $this->created_at->toIso8601String(),
                'last_post_created_at'  => $this->last_post_created_at->toIso8601String(),
                'url'                   => url(UrlBuilder::topic($this->resource)),
                'forum'         => [
                    'id'        => $this->forum->id,
                    'name'      => $this->forum->name,
                    'slug'      => $this->forum->slug
                ],
                'tags'                  => TagResource::collection($this->whenLoaded('tags')),
//@todo dodac date przeczytania watku
//                'read_at'               => $this->when($this->resource->relationLoaded('tracks'), function () {
//                    return max($this->tracks->first()->marked_at ?? 0, $this->forum->tracks->first()->marked_at ?? 0);
//                })
            ]
        );
    }
}
