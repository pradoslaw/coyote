<?php
namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Post\Comment;
use Coyote\Services\Forum\Tracker;
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
    private bool $obscureDeletedPosts = false;

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

    public function obscureDeletedPosts(): void
    {
        $this->obscureDeletedPosts = true;
    }

    public function toArray(Request $request): array
    {
        if ($this->obscureDeletedPosts) {
            /** @var Post $post */
            $post = $this->resource;
            if ($post->deleted_at) {
                return $this->postResourceToArrayObscured();
            }
        }
        return $this->postResourceToArray($request);
    }

    private function postResourceToArray(Request $request): array
    {
        /** @var Post $post */
        $post = $this->resource;

        $this->applyCommentsRelation();

        $only = $post->only(['id', 'user_name', 'score', 'text', 'edit_count', 'is_voted', 'is_accepted', 'is_subscribed', 'user_id', 'deleter_name', 'delete_reason']);
        $html = $this->text !== null ? $this->html : null;

        $commentsCount = count($post->comments);
        // show all comments if parameter "p" is present. it means that user wants to be redirected
        // to specific post and probably wants to see all comments (or specific comment)
        $comments = $request->get('p') == $this->id ? $post->comments : $post->comments->slice(-5, null, true);

        return array_merge($only, [
            'created_at'     => $this->created_at->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
            'deleted_at'     => $this->deleted_at ? Carbon::parse($this->deleted_at)->toIso8601String() : null,
            'user'           => UserResource::make($this->user),
            'html'           => $html,
            'url'            => UrlBuilder::post($post, true),
            'is_locked'      => $this->topic->is_locked || $this->forum->is_locked,
            $this->mergeWhen($this->tracker !== null, fn() => [
                'is_read' => $this->tracker->getMarkTime() >= $this->created_at,
            ]),
            $this->mergeWhen($this->editor !== null, fn() => [
                'editor' => UserResource::make($this->editor),
            ]),
            'permissions'    => [
                'write'      => $this->gate->allows('write', $this->topic) && $this->gate->allows('write', $this->forum),
                'delete'     => $this->gate->allows('delete', $post) || $this->gate->allows('delete', $this->forum),
                'update'     => $this->gate->allows('update', $post),
                'merge'      => $this->gate->allows('merge', $this->forum),
                'sticky'     => $this->gate->allows('sticky', $this->forum),
                'adm_access' => $this->gate->allows('adm-access'),
                'accept'     => $this->gate->allows('accept', $post),
            ],
            'comments'       => PostCommentResource::collection($comments)->keyBy('id'),
            'comments_count' => $commentsCount,
            'assets'         => AssetsResource::collection($post->assets),
            'metadata'       => encrypt([
                Post::class  => $this->id,
                Topic::class => $this->topic_id,
                Forum::class => $this->forum_id,
            ]),
            'has_review'     => false,
            'review_style'   => 'info',
            'parentPostId'   => $post->tree_parent_post_id,
            'childrenFolded' => false,
            'type'           => 'regular',
        ]);
    }

    private function applyCommentsRelation(): void
    {
        $this->resource->comments->each(function (Comment $comment) {
            $comment->setRelation('forum', $this->forum);
            $comment->setRelation('post', $this->resource);
        });
    }

    private function postResourceToArrayObscured(): array
    {
        return \array_merge(
            $this->resource->only(['id', 'deleter_name', 'delete_reason']),
            [
                'type'           => 'obscured',
                'parentPostId'   => $this->tree_parent_post_id,
                'childrenFolded' => false,
                'assets'         => [],
                'permissions'    => [
                    'write'      => false,
                    'delete'     => false,
                    'update'     => false,
                    'merge'      => false,
                    'sticky'     => false,
                    'adm_access' => false,
                    'accept'     => false,
                ],
                'created_at'     => $this->created_at->toIso8601String(),
                'deleted_at'     => $this->deleted_at ? Carbon::parse($this->deleted_at)->toIso8601String() : null,
            ],
        );
    }
}
