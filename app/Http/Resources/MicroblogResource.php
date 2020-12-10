<?php

namespace Coyote\Http\Resources;

class MicroblogResource extends Api\MicroblogResource
{
    public function toArray($request)
    {
        $result = parent::toArray($request);

        $assets = $result['media'];
        unset($result['media']);

        return array_merge($result, [
            'assets'        => $assets,
            'is_sponsored'  => $this->resource->is_sponsored,
            'metadata'      => encrypt(['permission' => 'microblog-delete', 'microblog_id' => $this->resource->id])
        ]);
    }
}
