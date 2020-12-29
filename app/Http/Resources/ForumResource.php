<?php

namespace Coyote\Http\Resources;

use Coyote\Post;
use Coyote\Services\Guest;
use Coyote\Services\UrlBuilder;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Post $post
 * @property \Carbon\Carbon $read_at
 * @property int $order
 * @property string $url
 */
class ForumResource extends JsonResource
{
    /**
     * @var Guest
     */
    protected $guest;

    /**
     * @param Guest|null $guest
     * @return $this
     */
    public function setGuest(?Guest $guest)
    {
        $this->guest = $guest;

        return $this;
    }

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
            ['order', 'require_tag', 'enable_prune', 'enable_reputation', 'enable_anonymous', 'prune_days', 'prune_last', 'post', 'last_post_id']
        );

        return array_merge($only, [
            'url' => url(UrlBuilder::forum($this->resource)),
            'is_read' => $this->isRead(),
            'order' => $this->custom_order ?? $this->order,
            'is_hidden' => $this->is_hidden ?? false,
            'is_redirected' => $this->url !== null,

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
                        'title'         => $this->post->topic->title,
                        'is_read'       => $this->post->topic->isRead(),
                        'url'           => url(UrlBuilder::topic($this->post->topic->getModel()))
                    ]
                ];
            })
        ]);
    }

    private function isRead(): bool
    {
        if (!$this->whenLoaded('post')) {
            return true;
        }

        if (!$this->read_at) {
            return $this->guest !== null ? $this->guest->getDefaultSessionTime() > $this->post->created_at : true;
        }

        return $this->read_at >= $this->post->created_at;
    }
}
