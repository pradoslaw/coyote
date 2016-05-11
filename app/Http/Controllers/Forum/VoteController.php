<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Services\Stream\Activities\Vote as Stream_Vote;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;

class VoteController extends BaseController
{
    /**
     * @param \Coyote\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($post)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby oddać ten głos.'], 500);
        }

        if (!config('app.debug') && auth()->user()->id === $post->user_id) {
            return response()->json(['error' => 'Nie możesz głosować na wpisy swojego autorstwa.'], 500);
        }

        $forum = $this->forum->find($post->forum_id);
        if ($forum->is_locked) {
            return response()->json(['error' => 'Forum jest zablokowane.'], 500);
        }

        $topic = $this->topic->find($post->topic_id, ['id', 'slug', 'subject', 'is_locked']);
        if ($topic->is_locked) {
            return response()->json(['error' => 'Wątek jest zablokowany.'], 500);
        }

        \DB::transaction(function () use ($post, $topic, $forum) {
            $result = $post->votes()->forUser($this->userId)->first();

            // build url to post
            $url = route('forum.topic', [$forum->slug, $topic->id, $topic->slug], false) . '?p=' . $post->id . '#id' . $post->id;
            // excerpt of post text. used in reputation and alert
            $excerpt = excerpt($post->text);

            if ($result) {
                $result->delete();
                $post->score--;
            } else {
                $post->votes()->create([
                    'user_id' => $this->userId, 'forum_id' => $forum->id, 'ip' => request()->ip()
                ]);
                $post->score++;

                // send notification to the user
                app()->make('Alert\Post\Vote')
                    ->setPostId($post->id)
                    ->setUsersId($forum->onlyUsersWithAccess([$post->user_id]))
                    ->setSubject(excerpt($topic->subject))
                    ->setExcerpt($excerpt)
                    ->setSenderId($this->userId)
                    ->setSenderName(auth()->user()->name)
                    ->setUrl($url)
                    ->notify();
            }

            // increase/decrease reputation points according to the forum settings
            if ($post->user_id && $forum->enable_reputation) {
                // add or subtract reputation points
                app()->make('Reputation\Post\Vote')
                    ->setUserId($post->user_id)
                    ->setIsPositive(!count($result))
                    ->setUrl($url)
                    ->setPostId($post->id)
                    ->setExcerpt($excerpt)
                    ->save();
            }

            // add into activity stream
            stream(Stream_Vote::class, (new Stream_Post(['url' => $url]))->map($post), (new Stream_Topic())->map($topic, $forum));
        });

        return response()->json(['count' => $post->score]);
    }
}
