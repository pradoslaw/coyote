<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Comment;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $job_id
 * @property Carbon $created_at
 * @property User $user
 * @property int $user_id
 * @property string $text
 * @property Comment[] $children
 * @property string $email
 */
class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            array_only(parent::toArray($request), ['id', 'text', 'email', 'html', 'parent_id']),
            [
                'created_at'    => $this->created_at->toIso8601String(),
                'children'      => $this->whenLoaded('children', fn () => CommentResource::collection($this->children)->keyBy('id')),
                'is_owner'      => $this->resource->resource->user_id === $this->user_id,

                'url'           => UrlBuilder::url($this->resource->resource) . '#comment-' . $this->id,
                'metadata'      => encrypt([Comment::class => $this->id]),

                'user'          => new UserResource($this->user ?: (new User)->forceFill($this->anonymous())),

                'permissions'   => [
                    'update'    => $request->user()?->can('update', $this->resource),
                    'delete'    => $request->user()?->can('delete', $this->resource)
                ]
            ]
        );
    }

    /**
     * @return array
     */
    private function anonymous(): array
    {
        return [
            'name' => $this->hideEmail($this->email),
            'photo' => null
        ];
    }

    /**
     * @param string $email
     * @return string
     */
    private function hideEmail(string $email): string
    {
        list($name, $domain) = explode('@', $email);

        $domain = explode('.', $domain);

        return substr($name, 0, 1) . '...@' . substr($domain[0], 0, 1) . '...' . last($domain);
    }
}
