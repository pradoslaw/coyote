<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Job;
use Coyote\Job\Comment;
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
     * @var Job
     */
    public static $job;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            parent::toArray($request), [
                'timestamp'     => $this->created_at->timestamp,
                'created_at'    => format_date($this->created_at),
                'html'          => $this->text,
                'user'          => $this->user(),
                'editable'      => $request->user() ? $this->user_id == $request->user()->id || $request->user()->can('job-update') : false,
                'route'         => [
                    'edit'      => route('job.comment', [$this->job_id, $this->id]),
                    'delete'    => route('job.comment.delete', [$this->job_id, $this->id]),
                    'reply'     => route('job.comment', [$this->job_id])
                ],
                'children'      => CommentResource::collection($this->children),
                'is_author'     => $request->user() ? $this->user_id == CommentResource::$job->user_id : false
            ]
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function user(): array
    {
        if ($this->user_id) {
            return [
                'name' => $this->user->name,
                'profile' => (string) route('profile', [$this->user_id]),
                'photo' => (string) $this->user->photo->url()
            ];
        }

        return [
            'name' => $this->hideEmail($this->email),
            'photo' => cdn('img/avatar.png')
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
