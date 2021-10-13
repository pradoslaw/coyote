<?php

namespace Coyote\Http\Resources;

use Coyote\Models\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $title
 * @property string $text
 * @property string $excerpt
 * @property string $slug
 * @property \Coyote\User $user
 * @property \Coyote\Tag[] $tags
 * @property Comment[] $commentsWithChildren
 * @property Comment[] $comments
 * @property int $comments_count
 */
class GuideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $parser = resolve('parser.post');

        return array_merge(
            parent::toArray($request),
            [
                'slug'          => $this->slug,
                'html'          => $parser->parse($this->text),
                'excerpt_html'  => $parser->parse($this->excerpt),
                'user'          => new UserResource($this->user),
                'tags'          => TagResource::collection($this->tags),
                'permissions'   => [
                    'update' => true
                ],

                'comments_count'    => $this->comments_count,
                'comments'          => $this->whenLoaded('commentsWithChildren', CommentResource::collection($this->commentsWithChildren))
            ]
        );
    }
}
