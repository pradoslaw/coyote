<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;
use Coyote\Alert\Setting;

class AlertsController extends Controller
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Alert
     */
    private $alert;

    /**
     * @param User $user
     * @param Alert $alert
     */
    public function __construct(User $user, Alert $alert)
    {
        parent::__construct();

        $this->user = $user;
        $this->alert = $alert;
    }

    /**
     * @param Session $session
     * @return $this
     */
    public function index(Session $session)
    {
        $this->breadcrumb->push('Powiadomienia', route('user.alerts'));

        $alerts = $this->alert->paginate(auth()->user()->id);
        $session = $session->findBy('user_id', auth()->user()->id, ['created_at']);

        $markId = [];
        foreach ($alerts as $alert) {
            if (!$alert->read_at) {
                $markId[] = $alert->id;
            }
        }

        if ($markId) {
            $this->alert->markAsRead($markId);
        }

        return parent::view('user.alerts')->with(compact('alerts', 'session'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function settings()
    {
        $settings = Setting::select(['alert_settings.*', 'alert_types.name'])
                ->join('alert_types', 'alert_types.id', '=', 'type_id')
                ->where('user_id', auth()->user()->id)
                ->get();

        return parent::view('user.alerts.settings', compact('settings'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $settings = ['profile' => $request->get('profile'), 'email' => $request->get('email')];

        foreach (array_keys($settings) as $mode) {
            while (list($setting, $value) = each($settings[$mode])) {
                Setting::where('id', $setting)->where('user_id', auth()->user()->id)->update([$mode => $value]);
            }
        }
        return back()->with('success', 'Zmiany zostały zapisane');
    }
}
