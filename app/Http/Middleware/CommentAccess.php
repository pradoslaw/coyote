<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Post\Comment;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\Post\CommentRepositoryInterface as CommentRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;

class CommentAccess
{
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
     * @param Gate $gate
     * @param ForumRepository $forum
     * @param TopicRepository $topic
     * @param PostRepository $post
     * @param CommentRepository $comment
     */
    public function __construct(
        Gate $gate,
        ForumRepository $forum,
        TopicRepository $topic,
        PostRepository $post,
        CommentRepository $comment
    ) {
        $this->gate = $gate;
        $this->forum = $forum;
        $this->topic = $topic;
        $this->post = $post;
        $this->comment = $comment;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
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
     * @param Comment $comment
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    private function checkAbility(Comment $comment)
    {
        /** @var \Coyote\Post $post */
        $post = $this->post->findOrFail($comment->post_id, ['id', 'topic_id', 'forum_id']);
        $topic = $post->topic;
        $forum = $topic->forum;

        // Maybe user does not have an access to this category?
        if ($this->gate->denies('access', $forum)) {
            return response('Unauthorized.', 401);
        }

        // Only moderators can post comment if topic (or forum) was locked
        // todo: move this code to PostCommentPolicy
        if ($this->gate->denies('write', $forum) || $this->gate->denies('write', $topic)) {
            return response('Unauthorized.', 401);
        }

        $this->request->attributes->add([
            'post'      => $post,
            'topic'     => $topic,
            'forum'     => $forum,
            'comment'   => $comment
        ]);

        return true;
    }
}
