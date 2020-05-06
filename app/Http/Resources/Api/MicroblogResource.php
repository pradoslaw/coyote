<?php

namespace Coyote\Http\Resources\Api;

use Carbon\Carbon;
use Coyote\Http\Resources\UserResource;
use Coyote\Microblog;
use Coyote\Services\Media\MediaInterface;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $html
 * @property User $user
 * @property Microblog[] $comments
 * @property int $parent_id
 * @property MediaInterface[] $media
 */
class MicroblogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'votes', 'text', 'html', 'parent_id']);

        return array_merge(
            $only,
            [
                'created_at'    => $this->created_at->toIso8601String(),
                'updated_at'    => $this->created_at->toIso8601String(),
                'html'          => $this->html,
                'comments'      => $this->when(!$this->parent_id, function () {
                    return $this->resource->relationLoaded('comments') ? MicroblogResource::collection($this->comments) : [];
                }),
                'user'          => UserResource::make($this->user),
                'media'         => $this->media(),
                'editable'      => $this->when($request->user(), function () use ($request) {
                    return $request->user()->can('update', $this->resource);
                }),
                $this->mergeWhen(array_has($this->resource, ['is_voted', 'is_subscribed', 'comments_count']), function () {
                    return $this->resource->only(['is_voted', 'is_subscribed', 'comments_count']);
                })
            ]
        );
    }

    protected function media(): array
    {
        if (!$this->resource->getOriginal('media')) {
            return [];
        }

        $result = [];

        foreach ($this->media as $media) {
            $result[] = ['thumbnail' => $media->url()->thumbnail('microblog'), 'url' => (string) $media->url(), 'name' => $media->getFilename()];
        }

        return $result;
    }
}
