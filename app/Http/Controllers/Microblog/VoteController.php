<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Microblog;
use Coyote\Microblog\Vote;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Illuminate\Http\Request;
use Coyote\Reputation\Microblog\Vote as Reputation_Vote;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Alert\Providers\Microblog\Vote as Alert_Vote;

/**
 * Ocena glosow na dany wpis na mikro (lub wyswietlanie loginow ktorzy oddali ow glos)
 *
 * Class VoteController
 * @package Coyote\Http\Controllers\Microblog
 */
class VoteController extends Controller
{
    /**
     * @var Reputation
     */
    private $reputation;

    /**
     * @var Alert
     */
    private $alert;

    /**
     * @param Reputation $reputation
     */
    public function __construct(Reputation $reputation, Alert $alert)
    {
        $this->reputation = $reputation;
        $this->alert = $alert;
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function post($id, Request $request)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby oddać ten głos.'], 500);
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

            $microblog->score = Microblog::getScore(
                $microblog->votes,
                $microblog->bonus,
                $microblog->created_at->getTimestamp()
            );

            // reputacje przypisujemy tylko za ocene wpisu a nie komentarza!!
            if (!$microblog->parent_id) {
                $url = route('microblog.view', [$microblog->id], false) . '#entry-' . $microblog->id;

                (new Reputation_Vote($this->reputation))->map($microblog)->setUrl($url)->setIsPositive(!$vote)->save();
            } else {
                $url = route('microblog.view', [$microblog->parent_id], false) . '#comment-' . $microblog->id;
            }

            $microblog->save();

            if (!$vote) {
                (new Alert_Vote($this->alert))
                    ->setMicroblogId($microblog->id)
                    ->addUserId($microblog->user_id)
                    ->setSubject(excerpt($microblog->text))
                    ->setSenderId(auth()->user()->id)
                    ->setSenderName(auth()->user()->name)
                    ->setUrl($url)
                    ->notify();
            }

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
