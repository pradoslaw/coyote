<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Http\Factories\GateFactory;
use Coyote\Post\Comment;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $user_id
 * @property int $topic_id
 * @property int $forum_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string $user_name
 * @property string $text
 * @property string $html
 * @property string $delete_reason
 * @property User $user
 * @property User|null $editor
 * @property User|null $deleter
 * @property int $score
 * @property int $edit_count
 * @property Topic $topic
 * @property Forum $forum
 * @property string $ip
 * @property string $host
 * @property string $browser
 */
class PostResource extends JsonResource
{
    use GateFactory;

    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * @var \Coyote\Flag[]
     */
    protected $flags;

    /**
     * @var \Illuminate\Contracts\Auth\Access\Gate
     */
    protected $gate;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->gate = $this->getGateFactory();
    }

    /**
     * @param Tracker $tracker
     * @return $this
     */
    public function setTracker(Tracker $tracker)
    {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * @param \Coyote\Flag[] $flags
     * @return $this
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }


    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->applyCommentsRelation();

        $only = $this->resource->only(['id', 'user_name', 'score', 'text', 'edit_count', 'is_voted', 'is_accepted', 'is_subscribed', 'user_id', 'deleter_name', 'delete_reason']);
        $html = $this->text !== null ? $this->html : null;

        $commentsCount = count($this->resource->comments);
        // show all comments if parameter "p" is present. it means that user wants to be redirected
        // to specific post and probably wants to see all comments (or specific comment)
        $comments = $request->get('p') == $this->id ? $this->resource->comments : $this->resource->comments->slice(-5, null, true);

        return array_merge($only, [
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'deleted_at'    => $this->deleted_at ? Carbon::parse($this->deleted_at)->toIso8601String() : null,
            'user'          => UserResource::make($this->user),
            'html'          => $html,
            'url'           => UrlBuilder::post($this->resource, true),
            'is_read'       => $this->tracker->getMarkTime() >= $this->created_at,
            'is_locked'     => $this->topic->is_locked || $this->forum->is_locked,

            // only for moderators
            $this->mergeWhen($this->gate->allows('delete', $this->forum), [
                'ip'         => $this->ip,
                'browser'    => $this->browser
            ]),

            $this->mergeWhen($this->editor !== null, function () {
                return ['editor' => UserResource::make($this->editor)];
            }),

            $this->mergeWhen($this->flags !== null, function () {
                return ['flags' => JsonResource::collection($this->flags)];
            }),

            'permissions' => [
                'write'             => $this->gate->allows('write', $this->topic) && $this->gate->allows('write', $this->forum),
                'delete'            => $this->gate->allows('delete', $this->resource) || $this->gate->allows('delete', $this->forum),
                'update'            => $this->gate->allows('update', $this->resource),
                'merge'             => $this->gate->allows('merge', $this->forum),
                'sticky'            => $this->gate->allows('sticky', $this->forum),
                'adm_access'        => $this->gate->allows('adm-access'),
                'accept'            => $this->gate->allows('accept', $this->resource)
            ],

            'comments'      => PostCommentResource::collection($comments)->keyBy('id'),
            'comments_count'=> $commentsCount,
            'attachments'   => PostAttachmentResource::collection($this->resource->attachments),
            'metadata'      => encrypt([
                'post_id'           => $this->id,
                'topic_id'          => $this->topic_id,
                'forum_id'          => $this->forum_id,
                'permission'        => 'delete'
            ])
        ]);
    }

    private function applyCommentsRelation()
    {
        $this->resource->comments->each(function (Comment $comment) {
            $comment->setRelation('forum', $this->forum);
        });
    }
}
