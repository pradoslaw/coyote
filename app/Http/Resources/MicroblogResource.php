<?php

namespace Coyote\Http\Resources;

class MicroblogResource extends Api\MicroblogResource
{
    public function toArray($request)
    {
        $result = parent::toArray($request);

        unset($result['media']);

        return array_merge($result, [
            'assets'        => $this->whenLoaded('assets', fn () => AssetsResource::collection($this->assets), []),
            'is_sponsored'  => $this->resource->is_sponsored,
            'metadata'      => encrypt(['permission' => 'microblog-delete', 'microblog_id' => $this->resource->id])
        ]);
    }
}
