<?php

namespace Coyote\Http\Resources;

use Coyote\Models\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $title
 * @property string $text
 * @property string $excerpt
 * @property \Coyote\User $user
 * @property \Coyote\Tag[] $tags
 * @property Comment[] $commentsWithChildren
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
                'slug'          => str_slug($this->title),
                'html'          => $parser->parse($this->text),
                'excerpt_html'  => $parser->parse($this->excerpt),
                'user'          => new UserResource($this->user),
                'tags'          => TagResource::collection($this->tags),
                'permissions'   => [
                    'update' => true
                ],
                'is_editing'    => false,

                'comments'      => $this->whenLoaded('commentsWithChildren', CommentResource::collection($this->commentsWithChildren))
//                'comments'      => $this->whenLoaded('commentsWithChildren', CommentCollection::collection($this->commentsWithChildren))
            ]
        );
    }
}
