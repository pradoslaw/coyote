<?php

namespace Coyote\Http\Resources;

use Coyote\Services\Media\MediaInterface;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $real_name
 * @property MediaInterface $logo
 */
class TagResource extends JsonResource
{
    /**
     * @var \Closure
     */
    public static $url;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $callback = self::$url instanceof \Closure ? self::$url : function ($name) {
            return route('job.tag', [urlencode($name)]);
        };

        return array_merge($this->resource->only(['id', 'name', 'real_name']), [
            'logo'      => (string) $this->logo->url(),
            'url'       => $callback($this->name)
        ]);
    }
}
