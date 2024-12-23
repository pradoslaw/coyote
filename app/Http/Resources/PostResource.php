<?php
namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    private readonly Gate $gate;
    private readonly Post $post;

    private ?Tracker $tracker = null;
    private bool $obscureDeletedPosts = false;

    public function __construct(Post $resource)
    {
        parent::__construct($resource);
        $this->gate = app(Gate::class);
        $this->post = $resource;
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
            if ($this->post->deleted_at) {
                return $this->postResourceToArrayObscured();
            }
        }
        $this->applyCommentsRelations();
        return $this->postResourceToArray($request);
    }

    private function postResourceToArray(Request $request): array
    {
        // show all comments if parameter "p" is present. it means that user wants to be redirected
        // to specific post and probably wants to see all comments (or specific comment)
        $comments = $request->get('p') == $this->post->id ? $this->post->comments : $this->post->comments->slice(-5);

        return [
            ...$this->post->only(['id', 'user_name', 'score', 'text', 'edit_count', 'is_voted', 'is_accepted', 'is_subscribed', 'user_id', 'deleter_name', 'delete_reason']),
            'created_at'           => $this->post->created_at->toIso8601String(),
            'updated_at'           => $this->post->updated_at?->toIso8601String(),
            'deleted_at'           => $this->post->deleted_at ? Carbon::parse($this->post->deleted_at)->toIso8601String() : null,
            'user'                 => UserResource::make($this->post->user),
            'html'                 => $this->post->text === null ? null : $this->post->html,
            'url'                  => UrlBuilder::post($this->post, true),
            'is_locked'            => $this->post->topic->is_locked || $this->post->forum->is_locked,
            $this->mergeWhen($this->tracker !== null, fn() => [
                'is_read' => $this->post->created_at <= $this->tracker->getMarkTime(),
            ]),
            $this->mergeWhen($this->post->editor !== null, fn() => [
                'editor' => UserResource::make($this->post->editor),
            ]),
            'permissions'          => [
                'write'  => $this->gate->allows('write', $this->post->topic) && $this->gate->allows('write', $this->post->forum),
                'delete' => $this->gate->allows('deleteAsUser', $this->post),
                'update' => $this->gate->allows('updateAsUser', $this->post),
                'accept' => $this->gate->allows('acceptAsUser', $this->post),
            ],
            'moderatorPermissions' => [
                'delete'    => $this->gate->allows('deleteAsModerator', $this->post) || $this->gate->allows('delete', $this->post->forum),
                'update'    => $this->gate->allows('updateAsModerator', $this->post),
                'accept'    => $this->gate->allows('acceptAsModerator', $this->post),
                'merge'     => $this->gate->allows('merge', $this->post->forum),
                'sticky'    => $this->gate->allows('sticky', $this->post->forum),
                'admAccess' => $this->gate->allows('adm-access'),
            ],
            'comments'             => PostCommentResource::collection($comments)->keyBy('id'),
            'comments_count'       => \count($this->post->comments),
            'assets'               => AssetsResource::collection($this->post->assets),
            'metadata'             => encrypt([
                Post::class  => $this->post->id,
                Topic::class => $this->post->topic_id,
                Forum::class => $this->post->forum_id,
            ]),
            'has_review'           => false,
            'review_style'         => 'info',
            'parentPostId'         => $this->post->tree_parent_post_id,
            'childrenFolded'       => false,
            'type'                 => 'regular',
        ];
    }

    private function applyCommentsRelations(): void
    {
        foreach ($this->post->comments as $comment) {
            $comment->setRelation('forum', $this->post->forum);
            $comment->setRelation('post', $this->post);
        }
    }

    private function postResourceToArrayObscured(): array
    {
        return [
            ...$this->post->only(['id', 'deleter_name', 'delete_reason']),
            'type'                 => 'obscured',
            'parentPostId'         => $this->post->tree_parent_post_id,
            'childrenFolded'       => false,
            'assets'               => [],
            'permissions'          => [
                'write'  => false,
                'delete' => false,
                'update' => false,
                'merge'  => false,
                'accept' => false,
            ],
            'moderatorPermissions' => [
                'sticky'    => false,
                'admAccess' => false,
            ],
            'created_at'           => $this->post->created_at->toIso8601String(),
            'deleted_at'           => $this->post->deleted_at ? Carbon::parse($this->post->deleted_at)->toIso8601String() : null,
        ];
    }
}
