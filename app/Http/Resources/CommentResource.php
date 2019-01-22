<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
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
            parent::toArray($request), [
                'timestamp'     => $this->created_at->timestamp,
                'created_at'    => format_date($this->created_at),
                'html'          => $this->text,
                'user' => [
                    'name'      => $this->user->name,
                    'profile'   => (string) route('profile', [$this->user_id]),
                    'photo'     => (string) $this->user->photo->url()
                ],
                'editable'      => $request->user() ? $this->user_id == $request->user()->id || $request->user()->can('job_edit') : false,
                'route'         => [
                    'edit'      => route('job.comment', [$this->job_id, $this->id]),
                    'delete'    => route('job.comment.delete', [$this->job_id, $this->id]),
                    'reply'     => route('job.comment', [$this->job_id])
                ],
                'children'      => CommentResource::collection($this->children)
            ]
        );
    }
}
