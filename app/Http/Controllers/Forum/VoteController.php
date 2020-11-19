<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Notifications\Post\VotedNotification;
use Coyote\Post;
use Coyote\Services\Stream\Activities\Vote as Stream_Vote;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\UrlBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;

class VoteController extends BaseController
{
    /**
     * @param \Coyote\Post $post
     * @return int
     * @throws AuthenticationException|AuthorizationException
     */
    public function index($post)
    {
        if (auth()->guest()) {
            throw new AuthenticationException('Musisz być zalogowany, aby oddać ten głos.');
        }

        if (!config('app.debug') && auth()->user()->id === $post->user_id) {
            throw new AuthorizationException('Nie możesz głosować na wpisy swojego autorstwa.');
        }

        $topic = $post->topic;
        if ($this->auth->cannot('write', $topic)) {
            throw new AuthorizationException('Wątek jest zablokowany.');
        }

        $forum = $topic->forum;
        if ($this->auth->cannot('write', $forum)) {
            throw new AuthorizationException('Forum jest zablokowane.');
        }

        $this->transaction(function () use ($post, $topic, $forum) {
            $vote = $post->votes()->forUser($this->userId)->first();

            // build url to post
            $url = UrlBuilder::post($post);
            // excerpt of post text. used in reputation and alert
            $excerpt = excerpt($post->html);

            if ($vote) {
                $vote->delete();
                $post->score--;
            } else {
                $post->votes()->create([
                    'user_id' => $this->userId, 'forum_id' => $forum->id, 'ip' => $this->request->ip()
                ]);
                $post->score++;

                if ($post->user) {
                    // send notification to the user
                    $post->user->notify(new VotedNotification($this->auth, $post));
                }
            }

            // increase/decrease reputation points according to the forum settings
            if ($post->user_id && $forum->enable_reputation) {
                // add or subtract reputation points
                app('reputation.post.vote')
                    ->setUserId($post->user_id)
                    ->setPositive($vote === null)
                    ->setUrl($url)
                    ->setPostId($post->id)
                    ->setExcerpt($excerpt)
                    ->save();
            }

            // add into activity stream
            stream(Stream_Vote::class, (new Stream_Post(['url' => $url]))->map($post), (new Stream_Topic())->map($topic));
        });

        return $post->score;
    }

    public function voters(Post $post)
    {
        $post->load('votes.user:id,name');

        return $post->votes->pluck('user.name');
    }
}
