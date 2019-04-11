<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MicroblogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'user_id', 'votes', 'created_at', 'updated_at']);

        return array_merge(
            $only,
            [
                'html' => $this->resource->html,
                'children' => MicroblogResource::collection($this->resource->children)
            ]
        );
    }
}
