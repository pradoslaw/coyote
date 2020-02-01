<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;

class RedirectToPost
{
    /**
     * @var PostRepository
     */
    protected $post;

    /**
     * @param PostRepository $post
     */
    public function __construct(PostRepository $post)
    {
        $this->post = $post;
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
        /* @var \Coyote\Forum $forum */
        $forum = $request->route('forum');
        /* @var \Coyote\Topic $topic */
        $topic = $request->route('topic');

        // associate topic with forum
        $topic->forum()->associate($forum);

        $tracker = Tracker::make($topic);
        $markTime = $tracker->getMarkTime();

        $request->attributes->set('mark_time', $markTime);

        if ($request->get('view') !== 'unread') {
            return $next($request);
        }

        $url = UrlBuilder::topic($topic);

        if ($markTime < $topic->last_post_created_at) {
            $postId = $this->post->getFirstUnreadPostId($topic->id, $markTime);

            if ($postId && $postId !== $topic->first_post_id) {
                return redirect()->to($url . '?p=' . $postId . '#id' . $postId);
            }
        } else {
            return redirect()->to($url . '?p=' . $topic->last_post_id . '#id' . $topic->last_post_id);
        }

        return $next($request);
    }
}
