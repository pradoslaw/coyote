<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Guide;
use Coyote\Http\Controllers\Controller;
use Coyote\Reputation;
use Coyote\Services\UrlBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Vote as Stream_Vote;
use Coyote\Services\Stream\Objects\Guide as Stream_Guide;

class VoteController extends Controller
{
    public function vote(Guide $guide, Request $request)
    {
        /** @var \Coyote\Guide\Vote|null $vote */
        $vote = $guide->voters()->forUser($this->userId)->first();

        if (!config('app.debug')) {
            if ($this->userId === $guide->user_id) {
                throw new AuthorizationException('Nie możesz głosować na wpisy swojego autorstwa.');
            }
            if ($this->auth->reputation < Reputation::VOTE) {
                throw new AuthorizationException('Nie możesz jeszcze oddawać głosów na wpisy innych.');
            }
        }

        $this->transaction(function () use ($vote, $guide, $request) {
            if ($vote) {
                $vote->delete();

                $guide->votes--;
            } else {
                $vote = $guide->voters()->create(['user_id' => $this->userId, 'ip' => $request->ip()]);
                $guide->votes++;
            }

            $target = null;


//            $url = UrlBuilder::guide($guide);
            $object = (new Stream_Guide())->map($guide);

//                app('reputation.microblog.vote')->map($microblog)->setUrl($url)->setPositive($vote->wasRecentlyCreated)->save();


            $guide->save();

            // put this to activity stream
            stream(Stream_Vote::class, $object, $target);

            return $vote;
        });
    }
}
