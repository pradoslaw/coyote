<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Carbon\Carbon;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property string $text
 * @property string $html
 * @property User $user
 * @property int $score
 * @property int $edit_count
 * @property \Coyote\Topic $topic
 * @property \Coyote\Forum $forum
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        $only = $this->resource->only(['id', 'user_name', 'score', 'ip', 'forum_id', 'topic_id', 'host', 'browser', 'user_id']);
//
//        if (empty($only['ip'])) {
//            unset($only['ip']);
//        }

        return [
            'created_at'    => $this->created_at->toIso8601String(),
            'text'          => $this->text !== null ? strip_tags($this->html) : null,
            'url'           => UrlBuilder::post($this->resource),
            'ip'            => $this->ip,
            'user_id'       => $this->user_id,

//            'topic'         => array_merge($this->topic->only('subject', 'slug', 'forum_id', 'id', 'first_post_id'), ['subject' => htmlspecialchars($this->topic->subject)]),
//
//            'forum'         => [
//                'id'        => $this->forum->id,
//                'name'      => $this->forum->name,
//                'slug'      => $this->forum->slug,
//                'url'       => UrlBuilder::forum($this->forum)
//            ],
//


//            $this->mergeWhen($this->topic->first_post_id === $this->id, function () {
//                return ['tags' => $this->topic->tags()->pluck('name')];
//            })
        ];
    }
}
