<?php

namespace Coyote\Http\Controllers\User;

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
        $sessions = $session->where('user_id', $this->userId)->get();

        foreach ($sessions as &$row) {
            $agent = new Agent();
            $agent->setUserAgent($row['browser']);

            $row['agent'] = $agent;
        }

        return $this->view('user.home', [
            'rank'                  => $user->rank($this->userId),
            'total_users'           => $user->countUsersWithReputation(),
            'ip'                    => request()->ip(),
            'sessions'              => $sessions
        ]);
    }

    /**
     * Upload zdjecia na serwer
     *
     * @param Request $request
     * @param UserRepository $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, UserRepository $user)
    {
        $this->validate($request, [
            'photo'             => 'required|image'
        ]);

        $media = $this->getMediaFactory('user_photo')->upload($request->file('photo'));
        $user->update(['photo' => $media->getFilename()], $this->userId);

        return response()->json([
            'url' => $media->url()
        ]);
    }

    /**
     * Usuniecie zdjecia z serwera
     *
     * @param UserRepository $user
     */
    public function delete(UserRepository $user)
    {
        $user->update(['photo' => null], $this->userId);
    }
}
