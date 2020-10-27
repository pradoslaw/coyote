<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Services\Media\MediaInterface;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property MediaInterface $file
 */
class PostAttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            $this->resource->toArray(['id', 'name', 'size', 'mime']),
            [
                'created_at' => $this->created_at->toIso8601String(),
                'url'        => $this->getUrl($this->file)
            ]
        );
    }

    protected function getUrl(MediaInterface $media): string
    {
        if ($media->isImage()) {
            return (string) $this->file->url();
        }

        // file can be located in hidden forum. we must hide real file url
        return route('forum.download', [$this->resource->id]);
    }
}
