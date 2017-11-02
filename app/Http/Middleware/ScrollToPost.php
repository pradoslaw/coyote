<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Forum;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Services\Forum\Personalizer;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Topic;
use Illuminate\Http\Request;

class ScrollToPost
{
    /**
     * @var PostRepository
     */
    protected $post;

    /**
     * @var Personalizer
     */
    protected $personalizer;

    /**
     * @param PostRepository $post
     * @param Personalizer $personalizer
     */
    public function __construct(PostRepository $post, Personalizer $personalizer)
    {
        $this->post = $post;
        $this->personalizer = $personalizer;
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

        $guestId = $request->session()->get('guest_id');

        $markTime = [
            Topic::class => $topic->markTime($guestId),
            Forum::class => $forum->markTime($guestId)
        ];

        if (empty($markTime[Forum::class])) {
            // try to establish user's first visit datetime
            $markTime[Forum::class] = $this->personalizer->getDefaultDateTime();
        }

        $request->attributes->set('mark_time', $markTime);

        if ($request->get('view') !== 'unread') {
            return $next($request);
        }

        // associate topic with forum
        $topic->forum()->associate($forum);
        $url = UrlBuilder::topic($topic);

        if ($markTime[Topic::class] < $topic->last_post_created_at
            && $markTime[Forum::class] < $topic->last_post_created_at) {
            $max = max($markTime[Topic::class], $markTime[Forum::class]);

            if ($max) {
                $postId = $this->post->getFirstUnreadPostId($topic->id, $max);

                if ($postId && $postId !== $topic->first_post_id) {
                    return redirect()->to($url . '?p=' . $postId . '#id' . $postId);
                }
            }
        } else {
            return redirect()->to($url . '?p=' . $topic->last_post_id . '#id' . $topic->last_post_id);
        }

        return $next($request);
    }
}
