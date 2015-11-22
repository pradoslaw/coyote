<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Reputation;
use Coyote\Session;
use Coyote\User;
use Illuminate\Http\Request;
use Image;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Moje konto', route('user.home'));

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
            'rank'                  => Reputation::getUserRank(auth()->user()->id),
            'total_users'           => Reputation::getTotalUsers(),
            'ip'                    => request()->ip(),
            'sessions'              => $sessions
        ]);
    }

    /**
     * Upload zdjecia na serwer
     *
     * @param Request $request
     * @param UserRepositoryInterface $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, UserRepositoryInterface $user)
    {
        $this->validate($request, [
            'photo'             => 'required|image'
        ]);

        if ($request->file('photo')->isValid()) {
            $fileName = uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
            $path = public_path('storage/photo/');

            $request->file('photo')->move($path, $fileName);

            if (auth()->user()->photo) {
                @unlink(public_path('storage/photo/' . auth()->user()->photo));
            }

            $thumbnail = Image::open($path . $fileName)->thumbnail(
                new \Imagine\Image\Box(120, 120),
                \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND
            );

            $thumbnail->save($path . $fileName);
            $user->update(['photo' => $fileName], auth()->user()->id);

            return response()->json([
                'url' => url('storage/photo/' . $fileName)
            ]);
        }
    }

    /**
     * Usuniecie zdjecia z serwera
     *
     * @param UserRepositoryInterface $user
     */
    public function delete(UserRepositoryInterface $user)
    {
        @unlink(public_path('storage/photo/' . auth()->user()->photo));
        $user->update(['photo' => null], auth()->user()->id);
    }
}
