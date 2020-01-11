<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = array_except(parent::toArray($request), ['order', 'require_tag', 'enable_prune', 'enable_reputation', 'enable_anonymous', 'prune_days', 'prune_last']);

        return array_merge($only, [
//            'post' => new PostResource($this->whenLoaded('post')),
//            'topic' => new TopicResource($this->post->topic),
        ]);
    }
}
