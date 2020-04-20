<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Coyote\Services\UrlBuilder\UrlBuilder;

/**
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $html
 * @property string $title
 * @property string $excerpt
 * @property int $views
 * @method \Illuminate\Database\Eloquent\Relations\HasMany subscribers()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany authors()
 */
class WikiResource extends ElasticsearchResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $body = array_except($this->resource->toArray(), ['is_locked', 'templates', 'views', 'template', 'wiki_id', 'parent_id']);

        return array_merge($body, [
            'url'           => UrlBuilder::wiki($this->resource),
            'title'         => htmlspecialchars($this->title),
            'text'          => strip_tags($this->html),
            'excerpt'       => htmlspecialchars($this->excerpt),
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
            'decay_date'    => $this->updated_at->toIso8601String(),
            'suggest'       => $this->getSuggest(),
            'participants'  => $this->authors()->pluck('user_id'),
            'subscribers'   => $this->subscribers()->pluck('user_id')
        ]);
    }

    protected function getDefaultSuggestTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    protected function weight(): int
    {
        return round(min(5000, $this->views) + (($this->created_at->timestamp - self::BASE_TIMESTAMP) / 600000));
    }

    /**
     * @return array
     */
    protected function categories(): array
    {
        $user = $this->authors()->first();

        if (empty($user)) {
            return [];
        }
        $result = ['user:' . $user->id];

        $result = array_merge($result, $this->subscribers()->pluck('user_id')->map(function ($userId) {
            return 'subscriber:' . $userId;
        })
            ->toArray()
        );

        $result = array_merge($result, $this->authors()->pluck('user_id')->map(function ($userId) {
            return 'participant:' . $userId;
        })
            ->toArray()
        );

        return $result;
    }
}
