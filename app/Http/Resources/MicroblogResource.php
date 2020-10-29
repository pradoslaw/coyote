<?php

namespace Coyote\Http\Resources;

class MicroblogResource extends Api\MicroblogResource
{
    public function toArray($request)
    {
        return parent::toArray($request) + ['metadata' => encrypt(['permission' => 'delete', 'microblog_id' => $this->resource->id])];
    }
}
