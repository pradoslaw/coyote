<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\Post\CommentRepositoryInterface as CommentRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;

class CommentAccess
{
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @var ForumRepository
     */
    protected $forum;

    /**
     * @var TopicRepository
     */
    protected $topic;

    /**
     * @var PostRepository
     */
    protected $post;

    /**
     * @var CommentRepository
     */
    protected $comment;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @param Guard $auth
     * @param Gate $gate
     * @param ForumRepository $forum
     * @param TopicRepository $topic
     * @param PostRepository $post
     * @param CommentRepository $comment
     */
    public function __construct(
        Guard $auth,
        Gate $gate,
        ForumRepository $forum,
        TopicRepository $topic,
        PostRepository $post,
        CommentRepository $comment
    ) {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->forum = $forum;
        $this->topic = $topic;
        $this->post = $post;
        $this->comment = $comment;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;
        $commentId = $request->route('id');

        if (empty($commentId)) {
            $comment = $this->comment->makeModel();
            $comment->post_id = (int) $request->input('post_id');
        } else {
            $comment = $this->comment->findOrFail($commentId);
        }

        if (($response = $this->checkAbility($comment)) !== true) {
            return $response;
        }

        return $next($request);
    }

    /**
     * @param $comment \Coyote\Post\Comment
     * @return array
     */
    private function checkAbility($comment)
    {
        $post = $this->post->findOrFail($comment->post_id, ['id', 'topic_id', 'forum_id']);
        $forum = $this->forum->findOrFail($post->forum_id);

        // Maybe user does not have an access to this category?
        if (!$forum->userCanAccess($this->auth->id())) {
            return response('Unauthorized.', 401);
        }

        $topic = $this->topic->findOrFail($post->topic_id, ['id', 'forum_id', 'path', 'subject', 'is_locked']);

        // Only moderators can delete this post if topic (or forum) was locked
        if ($this->gate->denies('delete', $forum)) {
            if ($topic->is_locked || $forum->is_locked || $post->deleted_at) {
                return response('Unauthorized.', 401);
            }
        }

        $this->request->attributes->add([
            'post' => $post,
            'topic' => $topic,
            'forum' => $forum,
            'comment' => $comment
        ]);

        return true;
    }
}
