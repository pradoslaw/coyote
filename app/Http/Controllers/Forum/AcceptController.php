<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Services\Stream\Activities\Accept as Stream_Accept;
use Coyote\Services\Stream\Activities\Reject as Stream_Reject;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;

class AcceptController extends BaseController
{
    /**
     * @param \Coyote\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($post)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby zaakceptować ten post.'], 500);
        }

        // post belongs to this topic:
        $topic = $this->topic->find($post->topic_id, ['id', 'slug', 'subject', 'first_post_id', 'is_locked']);

        if ($topic->is_locked) {
            return response()->json(['error' => 'Wątek jest zablokowany.'], 500);
        }

        $forum = $this->forum->find($post->forum_id);
        if ($forum->is_locked) {
            return response()->json(['error' => 'Forum jest zablokowane.'], 500);
        }

        if ($this->getGateFactory()->denies('update', $forum) && $topic->firstPost()->value('user_id') !== $this->userId) {
            return response()->json(['error' => 'Możesz zaakceptować post tylko we własnym wątku.'], 500);
        }

        $this->transaction(function () use ($topic, $post, $forum) {
            $result = $topic->accept()->where('topic_id', $topic->id)->first();

            // build url to post
            $url = route('forum.topic', [$forum->slug, $topic->id, $topic->slug], false);
            // excerpt of post text. used in reputation and alert
            $excerpt = excerpt($post->text);

            // add or subtract reputation points
            $reputation = app('reputation.post.accept');
            $target = (new Stream_Topic())->map($topic, $forum);

            // user might change his mind and accept different post (or he can uncheck solved post)
            if ($result) {
                $old = $this->post->find($result->post_id, ['user_id', 'text']);

                $reputation->setUrl($url . '?p=' . $result->post_id . '#id' . $result->post_id);
                $reputation->setExcerpt($excerpt);

                // add into activity stream
                stream(Stream_Reject::class, (new Stream_Post(['url' => $reputation->getUrl()]))->map($old), $target);

                // reverse reputation points
                if ($forum->enable_reputation) {
                    $reputation->setIsPositive(false)->setPostId($result->post_id);

                    if ($result->post_id !== $post->id) {
                        $reputation->setExcerpt(excerpt($old->text));

                        if ($old->user_id !== $result->user_id) {
                            $reputation->setUserId($old->user_id)->save();
                        }
                    } elseif ($result->user_id !== $post->user_id) {
                        // reverse reputation points for post author
                        $reputation->setUserId($post->user_id)->save(); // <-- don't change this. ($post->user_id)
                    }
                }

                $result->delete();
            }

            $reputation->setExcerpt($excerpt);
            $url .= '?p=' . $post->id . '#id' . $post->id;

            if (!$result || $post->id !== $result->post_id) {
                $reputation->setUrl($url);

                if ($post->user_id) {
                    // before we add reputation points we need to be sure that user does not accept his own post
                    if ($post->user_id !== $this->userId) {
                        if ($forum->enable_reputation) {
                            // increase reputation points for author
                            $reputation->setIsPositive(true)->setPostId($post->id)->setUserId($post->user_id)->save();
                        }

                        // send notification to the user
                        app('alert.post.accept')
                            ->setPostId($post->id)
                            ->setUsersId($forum->onlyUsersWithAccess([$post->user_id]))
                            ->setSubject(excerpt($topic->subject))
                            ->setExcerpt($excerpt)
                            ->setSenderId($this->userId)
                            ->setSenderName(auth()->user()->name)
                            ->setUrl($url)
                            ->notify();
                    }
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
