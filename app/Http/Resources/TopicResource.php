<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Services\Forum\Tracker;
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
 * @property \Carbon\Carbon $read_at
 */
class TopicResource extends JsonResource
{
    /**
     * @var string|null
     */
    private $guestId;

    /**
     * @param string|null $guestId
     * @return $this
     */
    public function setGuestId(?string $guestId)
    {
        $this->guestId = $guestId;

        return $this;
    }

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
                'read_at'               => $this->read_at ? $this->read_at->toIso8601String() : null,
                'is_read'               => $this->isRead(),
                'forum'         => [
                    'id'        => $this->forum->id,
                    'name'      => $this->forum->name,
                    'slug'      => $this->forum->slug
                ],
                'tags'                  => TagResource::collection($this->whenLoaded('tags'))
            ]
        );
    }

    /**
     * @return bool
     */
    private function isRead(): bool
    {
        return Tracker::make($this->resource)->isRead($this->guestId);
    }
}
