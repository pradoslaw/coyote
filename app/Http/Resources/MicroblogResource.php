<?php

namespace Coyote\Http\Resources;

class MicroblogResource extends Api\MicroblogResource
{
    public function toArray($request)
    {
        return parent::toArray($request) + [
            'is_sponsored' => $this->resource->is_sponsored,
            'metadata' => encrypt(['permission' => 'microblog-delete', 'microblog_id' => $this->resource->id])
        ];
    }
}
