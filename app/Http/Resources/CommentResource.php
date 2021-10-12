<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Job;
use Coyote\Job\Comment;
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
            array_only(parent::toArray($request), ['id', 'text', 'email', 'html']),
            [
                'created_at'    => $this->created_at->toIso8601String(),
//                'editable'      => $request->user() ? $this->user_id == $request->user()->id || $request->user()->can('job-update') : false,
                'children'      => CommentResource::collection($this->children)->keyBy('id'),
//                'is_author'     => $request->user() ? $this->user_id == $this->job->user_id : false,
//                'url'           => UrlBuilder::jobComment($this->job, $this->id),
//                'metadata'      => encrypt([Comment::class => $this->id, Job::class => $this->job_id]),

                'user'          => new UserResource($this->user ?: (new User)->forceFill($this->anonymous()))
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
