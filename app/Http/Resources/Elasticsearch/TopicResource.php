<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

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
class TopicResource extends JsonResource
{
    const BASE_TIMESTAMP = 946684800;

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

    /**
     * @return array
     */
    private function getSuggest(): array
    {
        $result = [];
        $weight = $this->weight();

        foreach ($this->input() as $index => $input) {
            $result[] = [
                'input' => $input,
                'weight' => max(0, $weight - ($index * 100)), // each input has lower weight
                'contexts'  => [
                    'category'     => $this->categories()
                ]
            ];
        }

        return $result;
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
    private function input(): array
    {
        $title = htmlspecialchars(trim($this->subject));
        $words = preg_split('/\s+/', $title);

        if (count($words) === 1) {
            return [$title];
        }

        $result = [];

        for ($i = 0; $i < 2; $i++) {
            $result[] = implode(' ', array_slice($words, $i));
        }

        return $result;
    }

    /**
     * @return array
     */
    private function categories(): array
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
