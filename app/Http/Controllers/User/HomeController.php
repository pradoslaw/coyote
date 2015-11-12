<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Reputation;
use Coyote\Session;

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
}
