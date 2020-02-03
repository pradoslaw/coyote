<?php

namespace Coyote\Http\Controllers\User;

use Carbon\Carbon;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class HomeController extends BaseController
{
    use HomeTrait, MediaFactory;

    /**
     * @param UserRepository $user
     * @param SessionRepository $session
     * @return \Illuminate\View\View
     */
    public function index(UserRepository $user, SessionRepository $session)
    {
//        $sessions = $session->all()->where('user_id', $this->userId);
//
//        foreach ($sessions as &$row) {
//            $agent = new Agent();
//            $agent->setUserAgent($row['browser']);
//
//            $row['agent'] = $agent;
//            $row['updated_at'] = Carbon::createFromTimestamp($row['updated_at']);
//        }

        return $this->view('user.home', [
            'rank'                  => $user->rank($this->userId),
            'total_users'           => $user->countUsersWithReputation(),
            'ip'                    => request()->ip(),
//            'sessions'              => $sessions
        ]);
    }

    /**
     * Upload zdjecia na serwer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $this->validate($request, [
            'photo'             => 'required|mimes:jpeg,jpg,png,gif'
        ]);

        $media = $this->auth->photo->upload($request->file('photo'));
        $this->auth->save();

        return response()->json([
            'url' => (string) $media->url()
        ]);
    }

    /**
     * Usuniecie zdjecia z serwera
     */
    public function delete()
    {
        $this->auth->photo = null;
        $this->auth->save();
    }
}
