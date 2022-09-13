<?php

namespace Coyote\Http\Resources\Elasticsearch;

class PageResource extends ElasticsearchResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->only(['id', 'title', 'path']);
    }
}
