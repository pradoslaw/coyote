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
 * @property string $html
 * @property User $user
 * @property Forum $forum
 * @property Post $firstPost
 * @property Post $lastPost
 * @property Microblog[] $comments
 * @property Tag[] $tags
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
        $only = $this->resource->only(['id', 'title', 'score', 'views', 'replies', 'is_sticky', 'is_locked']);
        $posts = collect();

        $posts->push($this->firstPost);
        $posts->push($this->lastPost);

        return array_merge(
            $only,
            [
                'locked_at'     => $this->locked_at ? $this->locked_at->toIso8601String() : null,
                'url'           => UrlBuilder::topic($this->resource),
                'forum'         => [
                    'id'        => $this->forum->id,
                    'name'      => $this->forum->name,
                    'slug'      => $this->forum->slug
                ],
                'posts'         => PostResource::collection($posts),
                'tags'          => TagResource::collection($this->tags)
            ]
        );
    }
}
