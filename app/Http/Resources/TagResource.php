<?php

namespace Coyote\Http\Resources;

use Coyote\Services\Media\MediaInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use Closure;

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
    public static Closure $urlResolver;

    public function __construct($resource)
    {
        parent::__construct($resource);

        if (empty(static::$urlResolver)) {
            static::$urlResolver = fn ($name) => route('job.tag', [urlencode($name)]);
        }
    }

    public static function urlResolver(Closure $resolver)
    {
        static::$urlResolver = $resolver;
    }

    public static function resolveUrl(string $name): string
    {
        return call_user_func(static::$urlResolver, $name);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge($this->resource->only(['id', 'name', 'real_name', 'topics', 'jobs', 'microblogs']), [
            'logo'      => (string) $this->logo->url(),
            'url'       => static::resolveUrl($this->name),

            'priority'  => $this->whenLoaded('pivot', function () {
                return $this->pivot->priority ?: $this->pivot->rate;
            })
        ]);
    }
}
