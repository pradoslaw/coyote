<?php

namespace Coyote\Http\Controllers\User;

use Coyote\User;
use Illuminate\Http\Request;

class SecurityController extends BaseController
{
    use SettingsTrait;

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Bezpieczeństwo', route('user.security'));

        return $this->view('user.security', ['ips' => explode('.', auth()->user()->access_ip)]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $user = auth()->user();

        $user->alert_login = (bool) $request->get('alert_login');
        $user->alert_failure = (bool) $request->get('alert_failure');

        $ips = [];

        foreach ($request->get('ips') as $element) {
            if ($element !== null && pattern('[0-9*]{1,3}')->test($element)) {
                $ips[] = $element;
            }
        }

        if (!in_array(count($ips), [0, 4, 8, 12])) {
            $count = count($ips);

            while (--$count % 4 == 0) {
                if ($count % 4 == 0) {
                    $ips = array_slice($ips, 0, $count);
                }
            }
        }

        $user->access_ip = implode('.', $ips);
        $user->save();

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }
}
