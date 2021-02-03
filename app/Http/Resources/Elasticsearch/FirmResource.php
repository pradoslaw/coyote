<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Illuminate\Http\Resources\Json\JsonResource;

class FirmResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name'          => $this->resource->name,
            'slug'          => $this->resource->slug,
            'logo'          => (string) $this->resource->logo->url()
        ];
    }
}
