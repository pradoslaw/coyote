<?php

namespace Coyote\Http\Resources;

use Coyote\Post;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Post $post
 */
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
        $only = array_except(
            parent::toArray($request),
            ['order', 'require_tag', 'enable_prune', 'enable_reputation', 'enable_anonymous', 'prune_days', 'prune_last', 'post', 'redirects']
        );

        return array_merge($only, [
            'url' => url(UrlBuilder::forum($this->resource)),

            $this->mergeWhen($this->whenLoaded('post'), function () {
                // set relation to avoid unnecessary db request in UrlBuilder
                $this->post->setRelation('forum', $this->resource);

                return [
                    'post' => [
                        'id'            => $this->post->id,
                        'created_at'    => $this->post->created_at->toIso8601String(),
                        'user_name'     => $this->post->user_name,
                        'url'           => url(UrlBuilder::post($this->post)),
                        'user'          => new UserResource($this->post->user)
                    ],
                    'topic' => [
                        'id'            => $this->post->topic->id,
                        'subject'       => $this->post->topic->subject,
                        'read_at'       => $this->post->topic->read_at
                    ]
                ];
            })
        ]);
    }
}
