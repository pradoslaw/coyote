<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Notifications\Post\AcceptedNotification;
use Coyote\Services\Stream\Activities\Accept as Stream_Accept;
use Coyote\Services\Stream\Activities\Reject as Stream_Reject;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\UrlBuilder\UrlBuilder;

class AcceptController extends BaseController
{
    /**
     * @param \Coyote\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($post)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby zaakceptować ten post.'], 401);
        }

        // post belongs to this topic:
        $topic = $post->topic;

        if ($this->auth->cannot('write', $topic)) {
            return response()->json(['error' => 'Wątek jest zablokowany.'], 403);
        }

        $forum = $topic->forum;
        if ($this->auth->cannot('write', $forum)) {
            return response()->json(['error' => 'Forum jest zablokowane.'], 403);
        }

        if ($this->auth->cannot('update', $forum) && $topic->firstPost()->value('user_id') !== $this->userId) {
            return response()->json(['error' => 'Możesz zaakceptować post tylko we własnym wątku.'], 403);
        }

        $this->transaction(function () use ($topic, $post, $forum) {
            // currently accepted post (if any)
            $accepted = $topic->accept;

            // build url to post
            $url = UrlBuilder::topic($topic);

            // excerpt of post text. used in reputation and alert
            $excerpt = excerpt($post->html);

            // add or subtract reputation points
            $reputation = app('reputation.post.accept');
            $target = (new Stream_Topic())->map($topic);

            // user might change his mind and accept different post (or he can uncheck solved post)
            if ($accepted) {
                $reputation->setUrl($url . '?p=' . $accepted->post_id . '#id' . $accepted->post_id);
                $reputation->setExcerpt($excerpt);

                // add into activity stream
                stream(Stream_Reject::class, (new Stream_Post(['url' => $reputation->getUrl()]))->map($accepted->post), $target);

                // reverse reputation points
                if ($forum->enable_reputation) {
                    $reputation->setPositive(false)->setPostId($accepted->post_id);

                    // user has chosen different post
                    if ($accepted->post_id !== $post->id) {
                        $reputation->setExcerpt(excerpt($accepted->post->html));

                        if ($accepted->post->user_id !== null && $accepted->post->user_id !== $accepted->user_id) {
                            $reputation->setUserId($accepted->post->user_id)->save();
                        }
                    } elseif ($post->user_id !== null && $accepted->user_id !== $post->user_id) {
                        // reverse reputation points for post author
                        $reputation->setUserId($post->user_id)->save(); // <-- don't change this. ($post->user_id)
                    }
                }

                $accepted->delete();
            }

            $reputation->setExcerpt($excerpt);
            $url .= '?p=' . $post->id . '#id' . $post->id;

            if (!$accepted || $post->id !== $accepted->post_id) {
                $reputation->setUrl($url);

                // before we add reputation points we need to be sure that user does not accept his own post
                if ($post->user_id !== null && $post->user_id !== $this->userId) {
                    if ($forum->enable_reputation) {
                        // increase reputation points for author
                        $reputation->setPositive(true)->setPostId($post->id)->setUserId($post->user_id)->save();
                    }

                    $post->user->notify(new AcceptedNotification($this->auth, $post));
                }

                $topic->accept()->create([
                    'post_id'   => $post->id,
                    'user_id'   => $this->userId, // don't change this. we need to know who accepted this post
                    'ip'        => request()->ip()
                ]);

                // add into activity stream
                stream(Stream_Accept::class, (new Stream_Post(['url' => $url]))->map($post), $target);
            }
        });
    }
}
