<?php
namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Post\Comment;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
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
    private Gate $gate;
    private ?Tracker $tracker = null;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->gate = app(Gate::class);
    }

    public function setTracker(Tracker $tracker): self
    {
        $this->tracker = $tracker;
        return $this;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $this->applyCommentsRelation();

        $only = $this->resource->only(['id', 'user_name', 'score', 'text', 'edit_count', 'is_voted', 'is_accepted', 'is_subscribed', 'user_id', 'deleter_name', 'delete_reason']);
        $html = $this->text !== null ? $this->html : null;

        $commentsCount = count($this->resource->comments);
        // show all comments if parameter "p" is present. it means that user wants to be redirected
        // to specific post and probably wants to see all comments (or specific comment)
        $comments = $request->get('p') == $this->id ? $this->resource->comments : $this->resource->comments->slice(-5, null, true);

        return array_merge($only, [
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'deleted_at' => $this->deleted_at ? Carbon::parse($this->deleted_at)->toIso8601String() : null,
            'user'       => UserResource::make($this->user),
            'html'       => $html,
            'url'        => UrlBuilder::post($this->resource, true),
            'is_locked'  => $this->topic->is_locked || $this->forum->is_locked,

            $this->mergeWhen($this->tracker !== null, function () {
                return ['is_read' => $this->tracker->getMarkTime() >= $this->created_at];
            }),

            // only for moderators
            $this->mergeWhen($this->gate->allows('delete', $this->forum), [
                'ip'      => $this->ip,
                'browser' => $this->browser,
            ]),

            $this->mergeWhen($this->editor !== null, function () {
                return ['editor' => UserResource::make($this->editor)];
            }),

            'permissions' => [
                'write'      => $this->gate->allows('write', $this->topic) && $this->gate->allows('write', $this->forum),
                'delete'     => $this->gate->allows('delete', $this->resource) || $this->gate->allows('delete', $this->forum),
                'update'     => $this->gate->allows('update', $this->resource),
                'merge'      => $this->gate->allows('merge', $this->forum),
                'sticky'     => $this->gate->allows('sticky', $this->forum),
                'adm_access' => $this->gate->allows('adm-access'),
                'accept'     => $this->gate->allows('accept', $this->resource),
            ],

            'comments'       => PostCommentResource::collection($comments)->keyBy('id'),
            'comments_count' => $commentsCount,
            'assets'         => AssetsResource::collection($this->resource->assets),
            'metadata'       => encrypt([
                Post::class  => $this->id,
                Topic::class => $this->topic_id,
                Forum::class => $this->forum_id,
            ]),
            'has_review'     => $this->hasReview($this->id),
        ]);
    }

    private function applyCommentsRelation(): void
    {
        $this->resource->comments->each(function (Comment $comment) {
            $comment->setRelation('forum', $this->forum);
            $comment->setRelation('post', $this->resource);
        });
    }

    private function hasReview(?int $id): bool
    {
        if ($id === null) {
            return false;
        }
        if (!auth()->check()) {
            return false;
        }
        $guest = new Guest(auth()->user()->guest_id);
        $postsToReview = $guest->getSetting('postsToReview', []);
        $postsReviewed = $guest->getSetting('postsReviewed', []);
        if (\in_array($id, $postsToReview)) {
            return !\array_key_exists($id, $postsReviewed);
        }
        return false;
    }
}
