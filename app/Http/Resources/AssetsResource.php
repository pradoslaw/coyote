<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetsResource extends JsonResource
{
    private static ?string $filter = null;

    public static function makeThumbnail(string $filter)
    {
        self::$filter = $filter;
    }

    public function toArray($request)
    {
        return array_merge(
            parent::toArray($request),
            [
                'url' => (string) $this->resource->url,
                'thumbnail' => $this->when(
                    // thumbnail only for images
                    self::$filter !== null && $this->resource->isImage(),
                    fn () => $this->resource->url->thumbnail(self::$filter)
                )
            ]
        );
    }
}
