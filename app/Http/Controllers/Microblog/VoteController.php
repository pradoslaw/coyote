<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Vote as Stream_Vote;
use Coyote\Services\Stream\Objects\Microblog as Stream_Microblog;

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
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function post($microblog, Request $request)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby oddać ten głos.'], 500);
        }

        $vote = $microblog->voters()->forUser($this->userId)->first();

        if (!config('app.debug') && $this->userId === $microblog->user_id) {
            return response()->json(['error' => 'Nie możesz głosować na wpisy swojego autorstwa.'], 500);
        }

        \DB::beginTransaction();

        try {
            if ($vote) {
                $vote->delete();

                $microblog->votes--;
            } else {
                $microblog->voters()->create(['user_id' => $this->userId, 'ip' => $request->getClientIp()]);
                $microblog->votes++;
            }

            $microblog->score = $microblog->getScore();

            // reputacje przypisujemy tylko za ocene wpisu a nie komentarza!!
            if (!$microblog->parent_id) {
                $url = route('microblog.view', [$microblog->id], false) . '#entry-' . $microblog->id;
                // need to parse text before adding excerpt to the db 'cause we don't want to save markdown but raw text
                $microblog->text = app('parser.microblog')->parse($microblog->text);

                app('reputation.microblog.vote')->map($microblog)->setUrl($url)->setIsPositive(!$vote)->save();
            } else {
                $url = route('microblog.view', [$microblog->parent_id], false) . '#comment-' . $microblog->id;
            }

            $microblog->save();

            // put this to activity stream
            stream(Stream_Vote::class, (new Stream_Microblog())->map($microblog));

            if (!$vote) {
                app('alert.microblog.vote')
                    ->setMicroblogId($microblog->id)
                    ->addUserId($microblog->user_id)
                    ->setSubject(excerpt($microblog->text))
                    ->setSenderId($this->userId)
                    ->setSenderName(auth()->user()->name)
                    ->setUrl($url)
                    ->notify();
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            throw $e;
        }

        return response()->json(['count' => $microblog->voters()->count()]);
    }

    /**
     * @param \Coyote\Microblog $microblog
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function voters($microblog)
    {
        return response(
            $microblog->voters()
                ->join('users', 'users.id', '=', 'user_id')
                ->get(['users.name'])
                ->lists('name')
                ->implode("\n")
        );
    }
}
