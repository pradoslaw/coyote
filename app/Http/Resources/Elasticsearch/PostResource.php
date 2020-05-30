<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Carbon\Carbon;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $created_at
 * @property string $text
 * @property string $html
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
        return [
            'created_at'    => $this->created_at->toIso8601String(),
            'text'          => $this->text !== null ? strip_tags($this->html) : null,
            'url'           => UrlBuilder::post($this->resource),
            'id'            => $this->id,
            'user_id'       => $this->user_id,
        ];
    }
}
