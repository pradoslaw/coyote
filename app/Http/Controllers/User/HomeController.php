<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Session;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    use HomeTrait, MediaFactory;

    /**
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function index(User $user)
    {
        $sessions = Session::where('user_id', auth()->user()->id)->get();

        $browsers = [
            'OPR' => 'Opera',
            'Firefox' => 'Firefox',
            'MSIE' => 'MSIE',
            'Trident' => 'MSIE',
            'Opera' => 'Opera',
            'Chrome' => 'Chrome'
        ];

        foreach ($sessions as &$row) {
            $browser = 'unknown';

            foreach ($browsers as $item => $name) {
                if (stripos($row['browser'], $item) !== false) {
                    $browser = $name;
                    break;
                }
            }

            $row['browser'] = $browser;
        }

        return $this->view('user.home', [
            'rank'                  => $user->rank(auth()->user()->id),
            'total_users'           => $user->countUsersWithReputation(),
            'ip'                    => request()->ip(),
            'sessions'              => $sessions
        ]);
    }

    /**
     * Upload zdjecia na serwer
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, User $user)
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
     * @param User $user
     */
    public function delete(User $user)
    {
        $user->update(['photo' => null], auth()->user()->id);
    }
}
