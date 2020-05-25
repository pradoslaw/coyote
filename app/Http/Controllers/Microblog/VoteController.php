<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Notifications\Microblog\VotedNotification;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Vote as Stream_Vote;
use Coyote\Services\Stream\Objects\Microblog as Stream_Microblog;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;

/**
 * Ocena glosow na dany wpis na mikro (lub wyswietlanie loginow ktorzy oddali ow glos)
 *
 * Class VoteController
 * @package Coyote\Http\Controllers\Microblog
 */
class VoteController extends Controller
{
    /**
     * @param \Coyote\Microblog $microblog
     * @param Request $request
     * @return int
     * @throws \Exception
     */
    public function post($microblog, Request $request)
    {
        if (auth()->guest()) {
            throw new AuthenticationException('Musisz być zalogowany, aby oddać ten głos.');
        }

        /** @var \Coyote\Microblog\Vote $vote */
        $vote = $microblog->voters()->forUser($this->userId)->first();

        if (!config('app.debug') && $this->userId === $microblog->user_id) {
            throw new AuthorizationException('Nie możesz głosować na wpisy swojego autorstwa.');
        }

        $vote = $this->transaction(function () use ($vote, $microblog, $request) {
            if ($vote) {
                $vote->delete();

                $microblog->votes--;
            } else {
                $vote = $microblog->voters()->create(['user_id' => $this->userId, 'ip' => $request->ip()]);
                $microblog->votes++;
            }

            $microblog->score = $microblog->getScore();
            $target = null;

            // reputacje przypisujemy tylko za ocene wpisu a nie komentarza!!
            if (!$microblog->parent_id) {
                $url = UrlBuilder::microblog($microblog);
                $object = (new Stream_Microblog())->map($microblog);

                app('reputation.microblog.vote')->map($microblog)->setUrl($url)->setPositive($vote->wasRecentlyCreated)->save();
            } else {
                $object = (new Stream_Comment())->map($microblog);
                $target = (new Stream_Microblog())->map($microblog->parent);
            }

            $microblog->save();

            // put this to activity stream
            stream(Stream_Vote::class, $object, $target);

            return $vote;
        });

        if ($vote->wasRecentlyCreated && $microblog->user) {
            $microblog->user->notify(new VotedNotification($vote));
        }

        return $microblog->voters()->count();
    }
}
