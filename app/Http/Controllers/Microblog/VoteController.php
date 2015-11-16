<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Microblog;
use Coyote\Microblog\Vote;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Illuminate\Http\Request;
use Log;

/**
 * Ocena glosow na dany wpis na mikro (lub wyswietlanie loginow ktorzy oddali ow glos)
 *
 * Class VoteController
 * @package Coyote\Http\Controllers\Microblog
 */
class VoteController extends Controller
{
    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function post($id, Request $request)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby oddać ten głos.']);
        }

        $microblog = Microblog::findOrFail($id);
        $vote = Vote::where('microblog_id', $id)->where('user_id', auth()->user()->id)->first();

        if (!config('app.debug') && auth()->user()->id === $microblog->user_id) {
            return response()->json(['error' => 'Nie możesz głosować na wpisy swojego autorstwa'], 500);
        }

        \DB::beginTransaction();

        try {
            if ($vote) {
                $vote->delete();

                $microblog->votes--;
            } else {
                Vote::create(['microblog_id' => $id, 'user_id' => auth()->user()->id, 'ip' => $request->getClientIp()]);

                $microblog->votes++;
            }

            // magia dzieje sie tutaj :) ustalanie "mocy" danego wpisu. na tej podstawie wyswietlane
            // sa wpisy na stronie glownej. liczba glosow swiadczy o ich popularnosci
            $microblog->score = $microblog->votes ? (int) (log($microblog->votes + $microblog->bonus, 2)
                + $microblog->created_at->getTimestamp() / 45000) : null;

            $microblog->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            throw $e;
        }

        return response()->json(['count' => Vote::where('microblog_id', $id)->count()]);
    }

    /**
     * @param $id
     * @param MicroblogRepository $microblog
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function voters($id, MicroblogRepository $microblog)
    {
        return response(implode("\n", $microblog->getVoters($id)));
    }
}
