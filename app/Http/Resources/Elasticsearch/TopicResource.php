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
                'forum'         => [
                    'id'        => $this->forum->id,
                    'name'      => $this->forum->name,
                    'slug'      => $this->forum->slug,
                    'url'       => UrlBuilder::forum($this->forum)
                ],
                'suggest'       => $this->getSuggest()
            ]
        );
    }

    /**
     * @return array
     */
    private function getSuggest(): array
    {
        $result = [];

        foreach ($this->input() as $index => $input) {
            $weight = $this->weight();

            $result[] = [
                'input' => $input,
                'weight' => $weight - ($index * 10), // each input has lower weight
                'contexts'  => [
                    'category'     => $this->categories()
                ]
            ];
        }
    }

    /**
     * @return int
     */
    protected function weight(): int
    {
        return round(
            ($this->replies * 10)
                + ($this->score * 20)
                    + (100 * (int) ($this->accept() !== null))
                        + (($this->last_post_created_at->timestamp - self::BASE_TIMESTAMP) / 600000)
        );
    }

    private function input(): array
    {
        $title = htmlspecialchars($this->subject);
        $result = [$title];

        $index = mb_strpos($title, ' ');

        $result[] = trim(mb_substr($title, $index));

        return $result;
    }

    private function categories(): array
    {
        $result = ['forum:' . $this->forum->id];

        if ($this->firstPost->user_id) {
            $result[] = 'user:' . $this->firstPost->user_id;
        }

        foreach ($this->subscribers()->pluck('user_id') as $subscriber) {
            $result[] = 'subscriber:' . $subscriber;
        }

        foreach ($this->users()->pluck('user_id') as $userId) {
            $result[] = 'users:' . $userId;
        }

        return $result;
    }
}
