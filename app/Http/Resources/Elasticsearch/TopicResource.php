<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;

/**
 * @property int $id
 * @property int $replies
 * @property int $score
 * @property string $subject
 * @property Carbon $created_at
 * @property Carbon $last_post_created_at
 * @property User $user
 * @property Forum $forum
 * @property Post $firstPost
 * @property Post $lastPost
 * @property Post[] $posts
 * @property int $topic_last_post_id
 * @method \Illuminate\Database\Eloquent\Relations\HasMany subscribers()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany users()
 * @method \Illuminate\Database\Eloquent\Relations\HasOne accept()
 */
class TopicResource extends ElasticsearchResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'score', 'replies']);

        return array_merge(
            $only,
            [
                'subject'               => htmlspecialchars($this->subject),
                'last_post_created_at'  => $this->last_post_created_at->toIso8601String(),
                'decay_date'            => $this->last_post_created_at->toIso8601String(),
                'url'                   => UrlBuilder::topic($this->resource->getModel()),
                'user_id'               => $this->firstPost->user_id,
                'forum'         => [
                    'id'        => $this->forum->id,
                    'name'      => $this->forum->name,
                    'slug'      => $this->forum->slug,
                    'url'       => UrlBuilder::forum($this->forum)
                ],
                'suggest'       => $this->getSuggest(),
                'participants'  => $this->users()->pluck('user_id'),
                'subscribers'   => $this->subscribers()->pluck('user_id')
            ]
        );
    }

    protected function getDefaultSuggestTitle(): ?string
    {
        return $this->subject;
    }

    /**
     * @return int
     */
    protected function weight(): int
    {
        return round(
            min(1000, $this->replies * 10)
                + ($this->score * 20)
                    + ($this->accept()->exists() ? 500 : 0)
                        + (($this->last_post_created_at->timestamp - self::BASE_TIMESTAMP) / 600000)
        );
    }

    /**
     * @return array
     */
    protected function categories(): array
    {
        $result = ['forum:' . $this->forum->id];

        if ($this->firstPost->user_id) {
            $result[] = 'user:' . $this->firstPost->user_id;
        }

        $result = array_merge($result, $this->subscribers()->pluck('user_id')->map(function ($userId) {
                return 'subscriber:' . $userId;
            })
            ->toArray()
        );

        $result = array_merge($result, $this->users()->pluck('user_id')->map(function ($userId) {
                return 'participant:' . $userId;
            })
            ->toArray()
        );

        return $result;
    }
}
