<?php

namespace Coyote\Http\Resources;

use Coyote\Comment;
use Coyote\Services\UrlBuilder;
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
 * @property \Coyote\Guide\Vote[]|\Illuminate\Support\Collection $voters[]
 * @property \Coyote\Models\Subscription[]|\Illuminate\Support\Collection $subscribers
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
        $user = $request->user();

        return array_merge(
            $this->resource->only(['id', 'title', 'created_at', 'votes', 'views']),
            [
                'slug'          => $this->slug,
                'url'           => UrlBuilder::guide($this->resource),

                'user'          => new UserResource($this->user),
                'tags'          => TagResource::collection($this->tags),
                'permissions'   => [
                    'update'    => $request->user()?->can('update', $this->resource)
                ],

                'comments_count'=> $this->comments_count,
                'subscribers'   => $this->subscribers()->count(),

                $this->mergeWhen($this->text || $this->excerpt, function () {
                    $parser = resolve('parser.post');

                    return [
                        'html'          => $parser->parse((string) $this->text),
                        'excerpt_html'  => $parser->parse((string) $this->excerpt)
                    ];
                }),

                'is_voted'          => $this->when($user, fn () => $this->voters->contains('user_id', $user->id), false),
                'is_subscribed'     => $this->when($user, fn () => $this->subscribers->contains('user_id', $user->id), false),

                'comments'          => $this->whenLoaded('commentsWithChildren', fn () => (new CommentCollection($this->commentsWithChildren))->setOwner($this->resource))
            ]
        );
    }
}
