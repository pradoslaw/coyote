<?php

namespace Coyote\Http\Resources;

use Coyote\Microblog;

class MicroblogResource extends Api\MicroblogResource
{
    public function toArray($request)
    {
        $result = parent::toArray($request);

        $assets = $result['media'];
        unset($result['media']);

        return array_merge($result, [
            'assets'        => $assets,
            'tags'          => $this->whenLoaded('tags', TagResource::collection($this->resource->tags)),
            'is_sponsored'  => $this->resource->is_sponsored,
            'metadata'      => encrypt([Microblog::class => $this->resource->id])
        ]);
    }
}
