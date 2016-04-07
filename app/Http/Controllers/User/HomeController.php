<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Session;
use Illuminate\Http\Request;
use Coyote\Thumbnail;

class HomeController extends BaseController
{
    use HomeTrait;

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

        return parent::view('user.home', [
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

        if ($request->file('photo')->isValid()) {
            $fileName = uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
            $path = public_path('storage/photo/');

            $request->file('photo')->move($path, $fileName);

            $thumbnail = new Thumbnail\Thumbnail(new Thumbnail\Objects\Photo());
            $thumbnail->make($path . $fileName);

            $user->update(['photo' => $fileName], auth()->user()->id);

            return response()->json([
                'url' => url('storage/photo/' . $fileName)
            ]);
        }
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
