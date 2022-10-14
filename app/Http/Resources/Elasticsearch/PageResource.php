<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Coyote\Topic;

/**
 * @property Topic $content
 * @property string $content_type
 */
class PageResource extends ElasticsearchResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            $this->resource->only(['id', 'title', 'path']),
            [
                $this->mergeWhen($this->content_type === Topic::class && $this->content?->forum, fn () => [
                    'forum' => ['is_prohibited' => $this->content->forum->is_prohibited]
                ])
            ]
        );
    }
}
