<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Alert;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as AlertRepository;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Illuminate\Http\Request;
use Carbon;

class AlertsController extends BaseController
{
    use SettingsTrait, HomeTrait {
        SettingsTrait::getSideMenu as settingsSideMenu;
        HomeTrait::getSideMenu as homeSideMenu;
    }

    /**
     * @var AlertRepository
     */
    private $alert;

    /**
     * @param AlertRepository $alert
     */
    public function __construct(AlertRepository $alert)
    {
        parent::__construct();

        $this->alert = $alert;
    }

    /**
     * @return mixed
     */
    public function getSideMenu()
    {
        if ($this->getRouter()->currentRouteName() == 'user.alerts') {
            return $this->homeSideMenu();
        } else {
            return $this->settingsSideMenu();
        }
    }

    /**
     * @param SessionRepository $session
     * @return \Illuminate\View\View
     */
    public function index(SessionRepository $session)
    {
        $this->breadcrumb->push('Powiadomienia', route('user.alerts'));

        $alerts = $this->alert->paginate($this->userId);
        $session = $session->findBy('user_id', $this->userId, ['created_at']);

        // mark as read
        $this->mark($alerts);

        return $this->view('user.alerts.home')->with(compact('alerts', 'session'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $this->breadcrumb->push('Ustawienia powiadomień', route('user.alerts.settings'));
        $settings = $this->alert->getUserSettings($this->userId);

        return $this->view('user.alerts.settings', compact('settings'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->alert->setUserSettings($this->userId, $request->input('settings'));

        return back()->with('success', 'Zmiany zostały zapisane');
    }

    /**
     * @param SessionRepository $session
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax(SessionRepository $session, Request $request)
    {
        $unread = $this->auth->alerts_unread;

        $alerts = $this->alert->takeForUser($this->userId, max(10, $unread), $request->query('offset', 0));
        $unread -= $this->mark($alerts);

        $view = view('user.alerts.ajax', [
            'alerts'    => $alerts,
            'session'   => $session->findBy('user_id', $this->userId, ['created_at']),
        ])->render();

        return response()->json([
            'html'      => $view,
            'unread'    => $unread
        ]);
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $this->alert->delete($id);
    }

    /**
     * Marks all alerts as read
     */
    public function markAsRead()
    {
        if ($this->auth->alerts_unread) {
            $this->alert->where('user_id', $this->userId)->whereNull('read_at')->update([
                'read_at' => Carbon\Carbon::now()
            ]);
        }

        $this->alert->where('user_id', $this->userId)->update(['is_marked' => true]);
    }

    /**
     * @param string $guid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function url(string $guid)
    {
        /** @var \Coyote\Alert $alert */
        $alert = $this->alert->findBy('guid', $guid, ['id', 'url', 'read_at', 'is_marked']);
        abort_if($alert === null, 404);

        if (!$alert->read_at) {
            $alert->is_marked = true;
            $alert->read_at = Carbon\Carbon::now();

            $alert->save();
        }

        return redirect()->to($alert->url);
    }

    /**
     * Mark alerts as read and returns number of marked alerts
     *
     * @param \Illuminate\Support\Collection $alerts
     * @return int
     */
    private function mark($alerts)
    {
        $ids = $alerts
            ->reject(function (Alert $alert) {
                return $alert->read_at !== null;
            })
            ->pluck('id')
            ->all();

        if (!empty($ids)) {
            $this->alert->markAsRead($ids);
        }

        return count($ids);
    }
}
