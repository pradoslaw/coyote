<?php

namespace Coyote\Http\Resources;

use Coyote\Services\Assets\Url;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetsResource extends JsonResource
{
    public function toArray($request)
    {
        $filter = $this->guessThumbnailFilter($this->resource->content_type);
        $url = Url::make($this->resource);

        return array_merge(
            array_except($this->resource->toArray(), ['content_id', 'content_type']),
            [
                'url' => (string) $url,
                'thumbnail' => $this->when(
                    // thumbnail only for images
                    $this->resource->isImage() && class_exists($filter, true),
                    fn () => $url->thumbnail(new $filter)
                )
            ]
        );
    }

    private function guessThumbnailFilter(string $content): string
    {
        return "\\Coyote\\Services\\Media\\Filters\\" . ucfirst(class_basename($content));
    }
}
