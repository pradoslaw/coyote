<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Coyote\Services\UrlBuilder\UrlBuilder;

/**
 * @property int $id
 * @property int $user_id
 * @property int $parent_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $html
 * @property \Coyote\Microblog $comments
 */
class MicroblogResource extends ElasticsearchResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'url'           => $this->parent_id ? UrlBuilder::microblogComment($this->resource) : UrlBuilder::microblog($this->resource),
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
            'decay_date'    => $this->created_at->toIso8601String(),
            'text'          => strip_tags($this->html),
            'user_id'       => $this->user_id,
            'children'      => MicroblogResource::collection($this->comments)
        ];
    }
}
